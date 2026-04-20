<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->nullable()->comment('Ref DP55, DP61, etc.');
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('autre')->comment('Categorie de charge');
            $table->string('label')->comment('Description libre de la charge');
            $table->decimal('amount', 10, 2)->comment('Montant en DH');
            $table->date('month')->comment('Mois concerne (premier jour du mois)');
            $table->date('expense_date')->nullable()->comment('Date effective de la depense');
            $table->string('payment_method', 30)->nullable()->comment('Methode de paiement');
            $table->string('operator_name')->nullable()->comment('Operateur');
            $table->unsignedInteger('order_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('expense_import_id')->nullable()->comment('Lien vers import source');
            $table->timestamps();

            $table->index(['site_id', 'month'], 'expense_site_month_idx');
            $table->index('type', 'expense_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_expenses');
    }
};
