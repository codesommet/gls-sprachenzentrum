<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // A1, A2, B1, B2
            $table->string('title');
            $table->text('description')->nullable();

            $table->unsignedInteger('time_limit_seconds')->nullable(); // optional
            $table->unsignedInteger('questions_per_attempt')->default(10);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('level');
        });
    }

    public function down(): void {
        Schema::dropIfExists('quizzes');
    }
};