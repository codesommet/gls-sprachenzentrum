<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('media_type')->default('none')->after('question_text'); // none|image|audio|both
            $table->string('media_caption')->nullable()->after('media_type');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'media_caption']);
        });
    }
};
