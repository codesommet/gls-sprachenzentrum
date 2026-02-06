<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
    if (!Schema::hasColumn('quiz_questions', 'question_media_type')) {
        $table->string('question_media_type', 20)->default('none')->after('question_text'); // none|audio|image
    }
    if (!Schema::hasColumn('quiz_questions', 'options_type')) {
        $table->string('options_type', 20)->default('text')->after('question_media_type'); // text|image
    }
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            //
        });
    }
};
