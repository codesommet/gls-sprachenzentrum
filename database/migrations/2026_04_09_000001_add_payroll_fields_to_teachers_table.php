<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds payment_per_student field to teachers table.
 * Each teacher can have a default rate per student (e.g., 300 or 500 DH).
 * This rate can be overridden per import in group_imports.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->decimal('payment_per_student', 10, 2)
                ->nullable()
                ->after('bio')
                ->comment('Default payment per student for this teacher (DH)');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('payment_per_student');
        });
    }
};
