<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_level_followups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')
                ->constrained('groups')
                ->cascadeOnDelete();

            $table->enum('level', ['A1', 'A2', 'B1', 'B2']);

            // Segment dates (derived from group date_debut/date_fin)
            $table->date('level_start_date');
            $table->date('level_end_date');
            $table->date('due_date'); // reminder trigger (at level_start_date)

            // Workflow
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->timestamp('done_at')->nullable();
            $table->text('done_notes')->nullable();

            $table->timestamps();

            $table->unique(['group_id', 'level']);
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_level_followups');
    }
};

