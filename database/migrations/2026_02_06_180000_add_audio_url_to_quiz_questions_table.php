<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add audio_url column to quiz_questions table.
     * 
     * This column stores external audio URLs (from Cloudinary, S3, etc.)
     * instead of uploading audio files via Spatie Media Library.
     * 
     * Rules:
     * - Can only be filled when question_media_type='audio'
     * - If options_type='image', audio_url must be null (question media hidden)
     * - If empty on update, set to null in DB
     * 
     * For backward compatibility:
     * - question_audio collection still exists in Spatie
     * - But new edits should use audio_url only
     */
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_questions', 'audio_url')) {
                $table->string('audio_url', 2048)
                    ->nullable()
                    ->after('media_caption')
                    ->comment('External audio URL (Cloudinary/S3) - replaces file upload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'audio_url')) {
                $table->dropColumn('audio_url');
            }
        });
    }
};
