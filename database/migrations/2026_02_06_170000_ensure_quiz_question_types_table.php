<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure quiz_questions table has the required columns:
     * - question_media_type: Controls question media (none, image, audio)
     * - options_type: Controls option rendering (text, image)
     * 
     * These columns are INDEPENDENT from each other.
     * This migration is idempotent - safe to run multiple times.
     */
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            // Add question_media_type if not exists (controls question media only)
            if (!Schema::hasColumn('quiz_questions', 'question_media_type')) {
                $table->string('question_media_type', 20)->default('none')
                    ->after('question_text')
                    ->comment('none|audio|image - Controls question media requirement');
            }

            // Add options_type if not exists (controls option rendering only)
            if (!Schema::hasColumn('quiz_questions', 'options_type')) {
                $table->string('options_type', 20)->default('text')
                    ->after('question_media_type')
                    ->comment('text|image - Controls how options are rendered and stored');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'question_media_type')) {
                $table->dropColumn('question_media_type');
            }
            if (Schema::hasColumn('quiz_questions', 'options_type')) {
                $table->dropColumn('options_type');
            }
        });
    }
};
