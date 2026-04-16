<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores ALL extra fee columns from Excel (inscription A1/A2, inscription B2, etc.)
 * as a JSON array: [{header, amount, color}, ...]
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('group_import_students', function (Blueprint $table) {
            $table->json('fee_columns')->nullable()->after('registration_fee')
                ->comment('All non-month fee columns: [{header, amount, color}]');
        });
    }

    public function down(): void
    {
        Schema::table('group_import_students', function (Blueprint $table) {
            $table->dropColumn('fee_columns');
        });
    }
};
