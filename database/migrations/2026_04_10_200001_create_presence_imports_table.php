<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presence_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->date('month');
            $table->date('date_start');
            $table->date('date_end');
            $table->unsignedInteger('total_days')->default(0);
            $table->decimal('payment_per_student', 10, 2)->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->text('notes')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['group_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presence_imports');
    }
};
