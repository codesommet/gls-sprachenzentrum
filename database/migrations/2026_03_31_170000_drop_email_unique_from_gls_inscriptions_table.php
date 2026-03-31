<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gls_inscriptions', function (Blueprint $table) {
            $table->dropUnique('gls_inscriptions_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('gls_inscriptions', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
