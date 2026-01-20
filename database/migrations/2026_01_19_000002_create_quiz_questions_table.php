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