<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encaissements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('encaissement_import_id')->nullable()->constrained()->nullOnDelete()->comment('Lien vers l import source, null si saisie manuelle');
            $table->string('reference', 50)->nullable()->comment('Matricule (ancien CRM) ou Ref P-xxx (nouveau CRM)');
            $table->enum('source_system', ['old_crm', 'new_crm', 'manual'])->default('manual');
            $table->string('student_name')->comment('Nom complet de l etudiant');
            $table->string('payer_name')->nullable()->comment('Nom du payeur si different de l etudiant');
            $table->decimal('amount', 10, 2)->comment('Montant en DH');
            $table->enum('payment_method', ['especes', 'tpe', 'virement', 'cheque'])->default('especes');
            $table->enum('fee_type', ['inscription_a1', 'inscription_b2', 'mensualite', 'examen_osd', 'autre'])->default('mensualite');
            $table->date('fee_month')->nullable()->comment('Mois concerne par le paiement (premier jour du mois)');
            $table->string('fee_description')->nullable()->comment('Texte brut original du type de frais');
            $table->string('group_name')->nullable()->comment('Nom du groupe/classe');
            $table->string('school_year', 20)->nullable()->comment('Annee scolaire ex: 2025/2026');
            $table->date('collected_at')->comment('Date effective de l encaissement');
            $table->string('operator_name')->nullable()->comment('Nom du caissier/operateur');
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete()->comment('Lien employe si identifie');
            $table->unsignedInteger('guichet_number')->nullable()->comment('N guichet ancien CRM uniquement');
            $table->unsignedInteger('order_number')->nullable()->comment('N ordre ou N ligne sequentiel');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('site_id', 'enc_site_idx');
            $table->index('collected_at', 'enc_date_idx');
            $table->index('payment_method', 'enc_method_idx');
            $table->index('fee_type', 'enc_fee_type_idx');
            $table->index('operator_name', 'enc_operator_idx');
            $table->index('source_system', 'enc_source_idx');
            $table->index(['site_id', 'collected_at'], 'enc_site_date_idx');
            $table->index(['site_id', 'fee_month'], 'enc_site_month_idx');

            // Deduplication index: same ref + same amount + same date = likely duplicate
            $table->index(['reference', 'amount', 'collected_at'], 'enc_dedup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encaissements');
    }
};
