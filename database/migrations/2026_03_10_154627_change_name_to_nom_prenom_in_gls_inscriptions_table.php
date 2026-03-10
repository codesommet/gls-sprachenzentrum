<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gls_inscriptions', function (Blueprint $table) {
            // Rename 'name' to 'nom'
            $table->renameColumn('name', 'nom');
        });

        Schema::table('gls_inscriptions', function (Blueprint $table) {
            // Add 'prenom' column after 'nom'
            $table->string('prenom')->default('')->after('nom');
        });

        // Split existing 'nom' values into nom and prenom
        DB::statement("
            UPDATE gls_inscriptions 
            SET prenom = SUBSTRING_INDEX(nom, ' ', -1),
                nom = SUBSTRING_INDEX(nom, ' ', 1)
            WHERE nom LIKE '% %'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Merge nom and prenom back into name
        DB::statement("
            UPDATE gls_inscriptions 
            SET nom = CONCAT(nom, ' ', prenom)
            WHERE prenom != ''
        ");

        Schema::table('gls_inscriptions', function (Blueprint $table) {
            $table->dropColumn('prenom');
        });

        Schema::table('gls_inscriptions', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
        });
    }
};
