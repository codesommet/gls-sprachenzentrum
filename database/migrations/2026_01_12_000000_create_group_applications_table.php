<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('group_applications', function (Blueprint $table) {
            $table->id();

            // Relation
            $table->foreignId('group_id')
                ->constrained('groups')
                ->cascadeOnDelete();

            // Applicant info
            $table->string('full_name');
            $table->string('whatsapp_number');
            $table->string('email');
            $table->date('birthday')->nullable();
            $table->text('note')->nullable();

            // Status workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            // Google Sheet tracking
            $table->string('google_sheet_name')->nullable();
            $table->unsignedInteger('google_sheet_row')->nullable();
            $table->timestamp('google_sheet_synced_at')->nullable();
            $table->timestamp('google_sheet_confirmed_synced_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('group_id');
            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_applications');
    }
};
