<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();

            $table->string('full_name');
            $table->string('whatsapp_number');
            $table->date('birthday')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();

            $table->index('group_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_applications');
    }
};
