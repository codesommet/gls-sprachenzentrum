<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Computed lifecycle status per student per month per import.
 * Recalculated on each import. Stores the classification result
 * (initial, new, active, lost, returned, cancelled, transferred, inactive).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_lifecycle_entries', function (Blueprint $table) {
            $table->id();

            // Which import version computed this
            $table->foreignId('group_import_id')
                ->constrained('group_imports')
                ->cascadeOnDelete();

            // Which student
            $table->foreignId('group_import_student_id')
                ->constrained('group_import_students')
                ->cascadeOnDelete();

            // Month
            $table->date('month');

            // Lifecycle classification
            $table->enum('status', [
                'initial',      // First paid month == group start month
                'new',          // First paid month after group start month
                'active',       // Paying, not first time, not returned
                'lost',         // Was paying, now stopped
                'returned',     // Came back after a gap
                'cancelled',    // Student cancelled / annulé
                'transferred',  // Student transferred to another group
                'inactive',     // No payment, no prior payment history
            ]);

            $table->timestamps();

            // One entry per student per month per import
            $table->unique(
                ['group_import_id', 'group_import_student_id', 'month'],
                'lifecycle_student_month_unique'
            );

            $table->index('group_import_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_lifecycle_entries');
    }
};
