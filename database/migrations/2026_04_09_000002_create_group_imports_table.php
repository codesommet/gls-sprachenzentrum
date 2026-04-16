<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Import snapshots / versions for CRM Excel files.
 * Each row represents one import of a CRM file for a specific group.
 * Supports versioning: multiple imports per group over time.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_imports', function (Blueprint $table) {
            $table->id();

            // Link to existing group
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();

            // Version number (auto-incremented per group: 1, 2, 3...)
            $table->unsignedInteger('version')->default(1);

            // The month the group started (used for lifecycle analysis)
            $table->date('start_month')->comment('YYYY-MM-01 format, group start month for lifecycle calc');

            // Payment rate override (nullable = use teacher default)
            $table->decimal('payment_per_student', 10, 2)
                ->nullable()
                ->comment('Override teacher rate for this import period (DH)');

            // File info
            $table->string('file_name');
            $table->string('file_path')->nullable();

            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->unique(['group_id', 'version']);
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_imports');
    }
};
