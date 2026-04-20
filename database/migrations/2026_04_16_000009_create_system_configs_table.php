<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default prime config
        \DB::table('system_configs')->insert([
            [
                'key' => 'prime.period_months',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Duree par defaut des primes en mois (1, 3, 6, 12)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prime.threshold_rate',
                'value' => '70',
                'type' => 'integer',
                'description' => 'Taux de recouvrement minimum pour etre eligible aux primes (%)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prime.amount_per_point',
                'value' => '200',
                'type' => 'integer',
                'description' => 'Montant en DH par point de pourcentage au-dessus du seuil',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prime.eligible_roles',
                'value' => 'Réception,Commercial,Coordination',
                'type' => 'string',
                'description' => 'Roles eligibles aux primes (separes par virgule)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};
