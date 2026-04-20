<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impayes', function (Blueprint $table) {
            // Dedup key: same student + same fee + same month + same site = same impaye across imports
            $table->string('dedup_key', 255)->nullable()->after('reference')
                  ->comment('Cle de deduplication: site_id + student_name + fee_description + amount_due');
            $table->boolean('auto_resolved')->default(false)->after('status')
                  ->comment('Marque automatiquement recouvré lors d un nouvel import');

            $table->index(['site_id', 'dedup_key'], 'impaye_dedup_idx');
        });

        Schema::table('impaye_imports', function (Blueprint $table) {
            $table->unsignedInteger('new_rows')->default(0)->after('success_rows')
                  ->comment('Nouveaux impayes detectes');
            $table->unsignedInteger('resolved_rows')->default(0)->after('new_rows')
                  ->comment('Impayes resolus (ayant paye depuis le dernier import)');
            $table->unsignedInteger('kept_rows')->default(0)->after('resolved_rows')
                  ->comment('Impayes encore actifs (presents dans les deux imports)');
            $table->foreignId('previous_import_id')->nullable()->after('kept_rows')
                  ->constrained('impaye_imports')->nullOnDelete()
                  ->comment('Import precedent pour le meme mois et centre');
        });
    }

    public function down(): void
    {
        Schema::table('impaye_imports', function (Blueprint $table) {
            $table->dropForeign(['previous_import_id']);
            $table->dropColumn(['new_rows', 'resolved_rows', 'kept_rows', 'previous_import_id']);
        });

        Schema::table('impayes', function (Blueprint $table) {
            $table->dropIndex('impaye_dedup_idx');
            $table->dropColumn(['dedup_key', 'auto_resolved']);
        });
    }
};
