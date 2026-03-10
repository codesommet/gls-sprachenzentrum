<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('google_sheet_name')->nullable()->after('email');
            $table->unsignedInteger('google_sheet_row')->nullable()->after('google_sheet_name');
            $table->timestamp('google_sheet_synced_at')->nullable()->after('google_sheet_row');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['google_sheet_name', 'google_sheet_row', 'google_sheet_synced_at']);
        });
    }
};
