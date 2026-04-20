<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des imports d'impayes
        Schema::create('impaye_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->enum('file_type', ['excel', 'pdf'])->default('excel');
            $table->string('month', 7)->nullable()->comment('YYYY-MM du mois concerne');
            $table->date('snapshot_date')->nullable()->comment('Date d arrete du snapshot (toutes echeances jusqu a cette date)');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('new_rows')->default(0)->comment('Nouveaux impayes detectes');
            $table->unsignedInteger('resolved_rows')->default(0)->comment('Impayes resolus (ayant paye depuis le dernier import)');
            $table->unsignedInteger('kept_rows')->default(0)->comment('Impayes encore actifs (presents dans les deux imports)');
            $table->foreignId('previous_import_id')->nullable()
                  ->constrained('impaye_imports')->nullOnDelete()
                  ->comment('Import precedent pour le meme mois et centre');
            $table->unsignedInteger('error_rows')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Montant total des impayes');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('errors_log')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Table des impayes
        Schema::create('impayes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('impaye_import_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('order_number')->nullable();
            $table->string('reference', 50)->nullable()->comment('Ref SL125, PR1, etc.');
            $table->string('dedup_key', 255)->nullable()
                  ->comment('Cle de deduplication: site_id + student_name + fee_description + amount_due');
            $table->string('student_name');
            $table->string('phone')->nullable();
            $table->string('group_name')->nullable()->comment('Ex: Herr Nizar 10H');
            $table->string('fee_description')->nullable()->comment('Ex: Frais d\'Avril, Frais d\'inscription B2');
            $table->decimal('amount_due', 10, 2)->comment('Montant restant a payer');
            $table->string('month', 7)->nullable()->comment('YYYY-MM du mois concerne');
            $table->enum('status', ['pending', 'recovered', 'cancelled'])->default('pending');
            $table->boolean('auto_resolved')->default(false)
                  ->comment('Marque automatiquement recouvré lors d un nouvel import');
            $table->date('recovered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'month'], 'impaye_site_month_idx');
            $table->index('status', 'impaye_status_idx');
            $table->index(['site_id', 'dedup_key'], 'impaye_dedup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impayes');
        Schema::dropIfExists('impaye_imports');
    }
};
