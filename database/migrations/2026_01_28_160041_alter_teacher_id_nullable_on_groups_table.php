<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // si tu as une FK, le dropForeign peut être nécessaire
            // le nom peut varier, mais souvent: groups_teacher_id_foreign
            try {
                $table->dropForeign(['teacher_id']);
            } catch (\Throwable $e) {
                // ignore si pas de FK ou nom différent
            }

            $table->unsignedBigInteger('teacher_id')->nullable()->change();

            // recréer la FK (optionnel mais recommandé)
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            try {
                $table->dropForeign(['teacher_id']);
            } catch (\Throwable $e) {}

            $table->unsignedBigInteger('teacher_id')->nullable(false)->change();

            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnDelete(); // ou restrict selon ton choix initial
        });
    }
};
