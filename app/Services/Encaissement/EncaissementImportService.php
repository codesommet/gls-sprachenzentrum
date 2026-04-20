<?php

namespace App\Services\Encaissement;

use App\Models\Encaissement;
use App\Models\EncaissementImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

/**
 * Orchestrates the full import pipeline:
 *  1. Store the uploaded file
 *  2. Create an EncaissementImport record
 *  3. Dispatch to the correct parser (old/new × excel/pdf)
 *  4. Deduplicate
 *  5. Bulk insert encaissements
 *  6. Update import stats
 */
class EncaissementImportService
{
    public function __construct(
        private OldCrmExcelParser $oldExcelParser,
        private NewCrmExcelParser $newExcelParser,
        private OldCrmPdfParser $oldPdfParser,
        private NewCrmPdfParser $newPdfParser,
    ) {}

    /**
     * Preview import: parse file but don't persist.
     *
     * @return array{rows: array, errors: array, meta: array}
     */
    public function preview(
        UploadedFile $file,
        int $siteId,
        string $sourceSystem,
        ?string $schoolYear = null
    ): array {
        $tempPath = $file->getRealPath();
        $fileType = $this->detectFileType($file);

        return $this->parseFile($tempPath, $siteId, $sourceSystem, $fileType, $schoolYear);
    }

    /**
     * Execute full import: parse, deduplicate, persist.
     */
    public function import(
        UploadedFile $file,
        int $siteId,
        string $sourceSystem,
        ?string $schoolYear = null,
        ?int $userId = null,
        ?string $notes = null,
        ?string $month = null
    ): EncaissementImport {
        $fileType = $this->detectFileType($file);

        // Store file
        $storedPath = $file->store('encaissement-imports', 'local');

        // Create import record
        $import = EncaissementImport::create([
            'site_id' => $siteId,
            'source_system' => $sourceSystem,
            'file_type' => $fileType,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'school_year' => $schoolYear,
            'month' => $month,
            'status' => EncaissementImport::STATUS_PROCESSING,
            'imported_by' => $userId,
            'notes' => $notes,
        ]);

        try {
            // Parse
            $fullPath = storage_path('app/' . $storedPath);
            $result = $this->parseFile($fullPath, $siteId, $sourceSystem, $fileType, $schoolYear);

            // Deduplicate + insert
            $stats = $this->persistRows($result['rows'], $import);

            // Detect period
            $dates = collect($result['rows'])->pluck('collected_at')->filter()->sort();

            // Update import record
            $import->update([
                'status' => EncaissementImport::STATUS_COMPLETED,
                'total_rows' => $result['meta']['parsed_rows'] ?? count($result['rows']),
                'success_rows' => $stats['inserted'],
                'error_rows' => count($result['errors']),
                'duplicate_rows' => $stats['duplicates'],
                'total_amount' => $stats['total_amount'],
                'period_start' => $dates->first(),
                'period_end' => $dates->last(),
                'errors_log' => !empty($result['errors']) ? $result['errors'] : null,
            ]);
        } catch (\Throwable $e) {
            $import->update([
                'status' => EncaissementImport::STATUS_FAILED,
                'errors_log' => [['message' => $e->getMessage(), 'trace' => mb_substr($e->getTraceAsString(), 0, 500)]],
            ]);
            throw $e;
        }

        return $import->fresh();
    }

    // ── Private ───────────────────────────────────────────────

    private function detectFileType(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return 'excel';
        }
        if ($ext === 'pdf') {
            return 'pdf';
        }
        // Fallback by mime
        $mime = $file->getMimeType();
        if (str_contains($mime, 'pdf')) return 'pdf';
        return 'excel';
    }

    private function parseFile(string $filePath, int $siteId, string $sourceSystem, string $fileType, ?string $schoolYear): array
    {
        if ($sourceSystem === 'old_crm' && $fileType === 'excel') {
            return $this->oldExcelParser->parse($filePath, $siteId);
        }
        if ($sourceSystem === 'old_crm' && $fileType === 'pdf') {
            return $this->oldPdfParser->parse($filePath, $siteId);
        }
        if ($sourceSystem === 'new_crm' && $fileType === 'excel') {
            return $this->newExcelParser->parse($filePath, $siteId, $schoolYear);
        }
        if ($sourceSystem === 'new_crm' && $fileType === 'pdf') {
            return $this->newPdfParser->parse($filePath, $siteId, $schoolYear);
        }

        throw new \InvalidArgumentException("Unsupported combination: {$sourceSystem} + {$fileType}");
    }

    /**
     * Persist parsed rows with deduplication.
     *
     * @return array{inserted: int, duplicates: int, total_amount: float}
     */
    private function persistRows(array $rows, EncaissementImport $import): array
    {
        $inserted = 0;
        $duplicates = 0;
        $totalAmount = 0;

        DB::transaction(function () use ($rows, $import, &$inserted, &$duplicates, &$totalAmount) {
            $batch = [];

            foreach ($rows as $row) {
                // Deduplication check: same reference + amount + date + student
                if ($this->isDuplicate($row, $import)) {
                    $duplicates++;
                    continue;
                }

                $batch[] = array_merge($row, [
                    'encaissement_import_id' => $import->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $totalAmount += (float) $row['amount'];
                $inserted++;

                // Batch insert every 100 rows for performance
                if (count($batch) >= 100) {
                    Encaissement::insert($batch);
                    $batch = [];
                }
            }

            // Insert remaining
            if (!empty($batch)) {
                Encaissement::insert($batch);
            }
        });

        return [
            'inserted' => $inserted,
            'duplicates' => $duplicates,
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Check if a row is a duplicate (already exists in DB from a different import).
     */
    private function isDuplicate(array $row, EncaissementImport $import): bool
    {
        $query = Encaissement::where('site_id', $row['site_id'])
            ->where('amount', $row['amount'])
            ->where('collected_at', $row['collected_at'])
            ->where('student_name', $row['student_name']);

        if (!empty($row['reference'])) {
            $query->where('reference', $row['reference']);
        }

        // Don't flag rows from the SAME import as duplicates (re-import scenario handled by import-level check)
        $query->where(function ($q) use ($import) {
            $q->whereNull('encaissement_import_id')
              ->orWhere('encaissement_import_id', '!=', $import->id);
        });

        return $query->exists();
    }
}
