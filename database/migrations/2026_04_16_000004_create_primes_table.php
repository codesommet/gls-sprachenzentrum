<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('primes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->comment('Montant de la prime en DH');
            $table->date('month')->comment('Periode de la prime (premier jour du mois)');
            $table->date('period_start')->nullable()->comment('Debut periode couverte');
            $table->date('period_end')->nullable()->comment('Fin periode couverte');
            $table->unsignedTinyInteger('period_months')->default(1)->comment('Duree periode en mois (1, 3, 6, 12)');
            $table->enum('type', ['performance', 'collection', 'assiduite', 'autre'])->default('performance');
            $table->text('reason')->nullable()->comment('Justification de la prime');
            $table->string('calculation_rule')->nullable()->comment('Regle appliquee: collection_rate_80, etc.');
            $table->decimal('collection_rate', 5, 2)->nullable()->comment('Taux de recouvrement au moment du calcul');
            $table->decimal('total_encaisse', 12, 2)->nullable()->comment('Total encaisse par le centre ce mois-ci');
            $table->decimal('total_impaye', 12, 2)->nullable()->comment('Total des impayes');
            $table->boolean('auto_generated')->default(false)->comment('Prime generee automatiquement');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'month'], 'prime_employee_month_idx');
            $table->index(['site_id', 'month'], 'prime_site_month_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('primes');
    }
};
