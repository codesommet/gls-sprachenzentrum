<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('gls_inscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('adresse');

            $table->string('niveau');
            $table->string('type_cours')->nullable();
            $table->integer('group_id')->nullable();
            $table->string('horaire_prefere')->nullable();
            $table->date('date_start')->nullable();
            $table->string('centre');

            // Tracking field: which form was used (modal or page)
            $table->enum('form_source', ['modal', 'page', 'unknown'])->default('unknown');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gls_inscriptions');
    }
};
