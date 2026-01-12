<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('phone');
            $table->string('email');
            $table->timestamps(); // created_at = date de soumission auto
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
