<?php

namespace App\Services\Payroll;

use App\Models\Group;
use App\Models\GroupImport;
use App\Models\GroupImportStudent;
use App\Models\GroupImportStudentPayment;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the full CRM Excel import flow.
 *
 * Responsibilities:
 * - Store uploaded file
 * - Create versioned import snapshot
 * - Persist parsed students + monthly payments
 * - Trigger lifecycle analysis
 */
class CrmGroupImportService
{
    public function __construct(
        protected CrmExcelParserService $parser,
        protected StudentLifecycleAnalysisService $lifecycleService,
    ) {}

    /**
     * Import a CRM Excel file for a group.
     *
     * @param  Group         $group             Target group
     * @param  UploadedFile  $file              Uploaded Excel file
     * @param  Carbon        $startMonth        Group start month for lifecycle analysis
     * @param  float|null    $paymentPerStudent Override teacher payment rate
     * @param  string|null   $notes             Import notes
     * @param  int|null      $importedBy        User ID who performed the import
     * @return GroupImport   The created import record
     */
    public function import(
        Group $group,
        UploadedFile $file,
        Carbon $startMonth,
        ?float $paymentPerStudent = null,
        ?string $notes = null,
        ?int $importedBy = null,
    ): GroupImport {
        return DB::transaction(function () use ($group, $file, $startMonth, $paymentPerStudent, $notes, $importedBy) {

            // 1. Store the uploaded file
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('payroll/imports', 'local');

            // 2. Determine next version number for this group
            $nextVersion = ($group->imports()->max('version') ?? 0) + 1;

            // 3. Create the import record
            $import = GroupImport::create([
                'group_id'            => $group->id,
                'version'             => $nextVersion,
                'start_month'         => $startMonth->copy()->startOfMonth(),
                'payment_per_student' => $paymentPerStudent,
                'file_name'           => $fileName,
                'file_path'           => $filePath,
                'notes'               => $notes,
                'imported_by'         => $importedBy,
            ]);

            // 4. Parse the Excel file
            $parsed = $this->parser->parse($file, $startMonth);

            // 5. Persist each student and their monthly payments
            foreach ($parsed['students'] as $studentData) {
                $student = GroupImportStudent::create([
                    'group_import_id'  => $import->id,
                    'row_number'       => $studentData['row_number'],
                    'student_name'     => $studentData['student_name'],
                    'registration_fee' => $studentData['registration_fee'],
                    'fee_columns'      => $studentData['fee_columns'] ?? null,
                    'status'           => $studentData['auto_status'] ?? 'active',
                    'row_color'        => $studentData['row_color'] ?? null,
                    'raw_data'         => $studentData['raw_data'],
                ]);

                foreach ($studentData['payments'] as $payment) {
                    GroupImportStudentPayment::create([
                        'group_import_student_id' => $student->id,
                        'month'                   => $payment['month'],
                        'amount'                  => $payment['amount'],
                        'raw_value'               => $payment['raw_value'],
                        'background_color'        => $payment['background_color'] ?? null,
                    ]);
                }
            }

            // 6. Compute lifecycle entries for all students in this import
            $this->lifecycleService->analyzeImport($import);

            return $import;
        });
    }

    /**
     * Update a student's status (e.g., mark as cancelled or transferred).
     * Recalculates lifecycle after status change.
     */
    public function updateStudentStatus(GroupImportStudent $student, string $status): void
    {
        DB::transaction(function () use ($student, $status) {
            $student->update(['status' => $status]);

            // Recalculate lifecycle for the entire import
            $this->lifecycleService->analyzeImport($student->groupImport);
        });
    }
}
