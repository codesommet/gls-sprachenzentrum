 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();

            // Option type & media
            $table->string('option_type', 20)->default('text');  // text|image
            $table->string('option_media_path')->nullable();     // image path
            $table->string('option_caption')->nullable();        // label under image

            $table->string('option_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['question_id', 'is_correct']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('quiz_options');
    }
};
