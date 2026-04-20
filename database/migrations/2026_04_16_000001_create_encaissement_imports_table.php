<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encaissement_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->enum('source_system', ['old_crm', 'new_crm'])->comment('Nawat (2023 - Oct. 2025) ou Wimschool (Nov. 2025+)');
            $table->enum('file_type', ['excel', 'pdf'])->comment('Type de fichier importe');
            $table->string('file_name')->comment('Nom du fichier original');
            $table->string('file_path')->comment('Chemin stockage sur disque');
            $table->date('period_start')->nullable()->comment('Debut de la periode couverte');
            $table->date('period_end')->nullable()->comment('Fin de la periode couverte');
            $table->string('school_year', 20)->nullable()->comment('Annee scolaire ex: 2025/2026');
            $table->string('month', 7)->nullable()->comment('Mois de l import au format YYYY-MM');
            $table->unsignedInteger('total_rows')->default(0)->comment('Nombre total de lignes importees');
            $table->unsignedInteger('success_rows')->default(0)->comment('Lignes importees avec succes');
            $table->unsignedInteger('error_rows')->default(0)->comment('Lignes en erreur');
            $table->unsignedInteger('duplicate_rows')->default(0)->comment('Lignes detectees comme doublons');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Montant total importe en DH');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('errors_log')->nullable()->comment('Journal des erreurs ligne par ligne');
            $table->text('notes')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['site_id', 'source_system']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encaissement_imports');
    }
};
