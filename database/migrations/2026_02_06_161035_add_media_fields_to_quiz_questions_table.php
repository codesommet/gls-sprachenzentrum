<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_questions', 'media_type')) {
                $table->string('media_type', 20)->default('none')->after('question_text'); // none|audio|image
            }

            if (!Schema::hasColumn('quiz_questions', 'media_path')) {
                $table->string('media_path')->nullable()->after('media_type'); // storage path (audio/image)
            }

            if (!Schema::hasColumn('quiz_questions', 'media_caption')) {
                $table->string('media_caption')->nullable()->after('media_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_questions', 'media_caption')) $table->dropColumn('media_caption');
            if (Schema::hasColumn('quiz_questions', 'media_path')) $table->dropColumn('media_path');
            if (Schema::hasColumn('quiz_questions', 'media_type')) $table->dropColumn('media_type');
        });
    }
};
