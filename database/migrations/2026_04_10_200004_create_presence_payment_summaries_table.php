<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presence_payment_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presence_import_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('base_price', 10, 2);
            $table->unsignedInteger('count_full')->default(0);
            $table->unsignedInteger('count_three_quarter')->default(0);
            $table->unsignedInteger('count_half')->default(0);
            $table->unsignedInteger('count_quarter')->default(0);
            $table->unsignedInteger('count_zero')->default(0);
            $table->unsignedInteger('total_students')->default(0);
            $table->decimal('total_payment', 10, 2)->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presence_payment_summaries');
    }
};
