<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Store Excel cell background colors extracted during import.
 * row_color = dominant color of the student row (from student name cell)
 * background_color = individual cell color per payment month
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('group_import_students', function (Blueprint $table) {
            $table->string('row_color', 7)->nullable()->after('status')
                ->comment('Hex color from Excel row, e.g. #FF0000');
        });

        Schema::table('group_import_student_payments', function (Blueprint $table) {
            $table->string('background_color', 7)->nullable()->after('raw_value')
                ->comment('Hex color from Excel cell, e.g. #92D050');
        });
    }

    public function down(): void
    {
        Schema::table('group_import_students', function (Blueprint $table) {
            $table->dropColumn('row_color');
        });

        Schema::table('group_import_student_payments', function (Blueprint $table) {
            $table->dropColumn('background_color');
        });
    }
};
