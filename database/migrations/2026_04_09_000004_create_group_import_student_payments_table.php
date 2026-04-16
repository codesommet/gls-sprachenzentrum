<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Monthly payment values per student per import.
 * Each row = one month's payment for one student in one import version.
 * Stores both parsed amount and raw cell value for auditability.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_import_student_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_import_student_id')
                ->constrained('group_import_students')
                ->cascadeOnDelete();

            // Month as YYYY-MM-01
            $table->date('month');

            // Parsed numeric amount
            $table->decimal('amount', 10, 2)->default(0);

            // Original cell value (e.g., "1300.00 DH", "", "0.00 DH")
            $table->string('raw_value')->nullable();

            $table->timestamps();

            // One payment per student per month per import
            $table->unique(
                ['group_import_student_id', 'month'],
                'gis_payments_student_month_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_import_student_payments');
    }
};
