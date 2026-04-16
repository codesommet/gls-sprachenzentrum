<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores each student row from a CRM Excel import.
 * One record per student per import version.
 * Preserves raw data for auditability.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_import_students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_import_id')
                ->constrained('group_imports')
                ->cascadeOnDelete();

            // Original row number in Excel
            $table->unsignedInteger('row_number')->nullable();

            // Student identification
            $table->string('student_name');

            // Registration fee column (e.g., "Frais d'inscription A1/A2")
            $table->decimal('registration_fee', 10, 2)->nullable();

            // Student status: from row color or manual assignment
            // active = normal, cancelled = red row, transferred = gray row
            $table->enum('status', ['active', 'cancelled', 'transferred', 'unknown'])
                ->default('active');

            // Preserve the entire original row as JSON for auditing
            $table->json('raw_data')->nullable();

            $table->timestamps();

            // For fast lookups and cross-import matching
            $table->index(['group_import_id', 'student_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_import_students');
    }
};
