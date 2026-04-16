<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presence_import_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presence_import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('student_name');
            $table->unsignedInteger('total_present')->default(0);
            $table->unsignedInteger('total_absent')->default(0);
            $table->unsignedTinyInteger('active_quarters')->default(0);
            $table->enum('category', ['full', 'three_quarter', 'half', 'quarter', 'zero'])->default('zero');
            $table->enum('category_override', ['full', 'three_quarter', 'half', 'quarter', 'zero'])->nullable();
            $table->decimal('weighted_amount', 10, 2)->default(0);
            $table->enum('status', ['active', 'cancelled', 'transferred', 'unknown'])->default('active');
            $table->string('row_color', 7)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presence_import_students');
    }
};
