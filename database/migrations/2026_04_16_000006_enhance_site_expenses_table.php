<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_expenses', function (Blueprint $table) {
            // Add new columns for import support
            $table->string('reference', 50)->nullable()->after('id')->comment('Ref DP55, DP61, etc.');
            $table->date('expense_date')->nullable()->after('month')->comment('Date effective de la depense');
            $table->string('payment_method', 30)->nullable()->after('expense_date')->comment('Methode de paiement');
            $table->string('operator_name')->nullable()->after('payment_method')->comment('Operateur');
            $table->unsignedInteger('order_number')->nullable()->after('operator_name');
            $table->foreignId('expense_import_id')->nullable()->after('notes')
                  ->comment('Lien vers import source');

            // Add new expense types to support the PDF format
            // We'll change the enum to a string to be more flexible
        });

        // Change type from enum to string for flexibility
        // MySQL doesn't support ALTER ENUM easily, so we use raw SQL
        \DB::statement("ALTER TABLE site_expenses MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'autre'");
    }

    public function down(): void
    {
        Schema::table('site_expenses', function (Blueprint $table) {
            $table->dropColumn(['reference', 'expense_date', 'payment_method', 'operator_name', 'order_number', 'expense_import_id']);
        });
    }
};
