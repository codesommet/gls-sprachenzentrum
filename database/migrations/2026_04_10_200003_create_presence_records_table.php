<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presence_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presence_import_student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'no_data'])->default('no_data');
            $table->string('raw_value', 20)->nullable();
            $table->timestamps();

            $table->unique(['presence_import_student_id', 'date'], 'presence_student_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presence_records');
    }
};
