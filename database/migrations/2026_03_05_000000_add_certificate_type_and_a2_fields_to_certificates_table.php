<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Certificate type: 'b2' (default for existing), 'a2'
            $table->string('certificate_type', 10)->default('b2')->after('certificate_number');

            // A2 speaking score (Sprechen) — not needed for B2
            $table->integer('speaking_score')->nullable()->after('writing_score');
            $table->integer('speaking_max')->nullable()->after('writing_max');

            // Make B2-only fields nullable (grammar, presentation, discussion, problemsolving)
            $table->integer('grammar_score')->nullable()->change();
            $table->integer('grammar_max')->nullable()->change();

            $table->integer('presentation_score')->nullable()->change();
            $table->integer('presentation_max')->nullable()->change();

            $table->integer('discussion_score')->nullable()->change();
            $table->integer('discussion_max')->nullable()->change();

            $table->integer('problemsolving_score')->nullable()->change();
            $table->integer('problemsolving_max')->nullable()->change();

            // Written/oral totals nullable for A2 (A2 uses a single total)
            $table->integer('written_total')->nullable()->change();
            $table->integer('written_max')->nullable()->change();
            $table->integer('oral_total')->nullable()->change();
            $table->integer('oral_max')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['certificate_type', 'speaking_score', 'speaking_max']);

            $table->integer('grammar_score')->nullable(false)->change();
            $table->integer('grammar_max')->nullable(false)->change();
            $table->integer('presentation_score')->nullable(false)->change();
            $table->integer('presentation_max')->nullable(false)->change();
            $table->integer('discussion_score')->nullable(false)->change();
            $table->integer('discussion_max')->nullable(false)->change();
            $table->integer('problemsolving_score')->nullable(false)->change();
            $table->integer('problemsolving_max')->nullable(false)->change();
            $table->integer('written_total')->nullable(false)->change();
            $table->integer('written_max')->nullable(false)->change();
            $table->integer('oral_total')->nullable(false)->change();
            $table->integer('oral_max')->nullable(false)->change();
        });
    }
};
