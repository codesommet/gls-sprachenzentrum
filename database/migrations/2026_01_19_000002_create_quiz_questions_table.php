<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();

            $table->text('question_text');

            // Media fields
            $table->string('media_type', 20)->default('none');       // none|audio|image
            $table->string('media_path')->nullable();                // storage path
            $table->string('media_caption')->nullable();
            $table->string('question_media_type', 20)->default('none'); // none|audio|image
            $table->string('options_type', 20)->default('text');        // text|image
            $table->string('audio_url', 2048)->nullable();             // external audio URL

            $table->unsignedTinyInteger('difficulty')->default(1); // 1..5
            $table->unsignedTinyInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['quiz_id', 'difficulty', 'is_active']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('quiz_questions');
    }
};
