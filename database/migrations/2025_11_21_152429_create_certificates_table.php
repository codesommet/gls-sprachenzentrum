<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            /**
             * Personal Information
             */
            $table->string('last_name');
            $table->string('first_name');
            $table->date('birth_date');
            $table->string('birth_place')->nullable();

            /**
             * Exam Meta
             */
            $table->string('exam_level')->default('Deutsch B2');
            $table->date('exam_date');
            $table->date('issue_date');
            $table->string('certificate_number')->unique();
            $table->string('public_token', 64)->unique()->nullable();
            $table->string('certificate_type', 10)->default('b2');

            /**
             * Schriftliche Prüfung (Written Exam)
             */
            $table->integer('reading_score');           // Leseverstehen
            $table->integer('grammar_score')->nullable(); // Sprachbausteine (B2 only)
            $table->integer('listening_score');          // Hörverstehen
            $table->integer('writing_score');            // Schriftlicher Ausdruck
            $table->integer('speaking_score')->nullable(); // Sprechen (A2 only)

            $table->integer('written_total')->nullable();

            /**
             * Mündliche Prüfung (Oral Exam) — B2 only
             */
            $table->integer('presentation_score')->nullable();  // Präsentation
            $table->integer('discussion_score')->nullable();     // Diskussion
            $table->integer('problemsolving_score')->nullable(); // Problemlösung

            $table->integer('oral_total')->nullable();

            /**
             * Final Result
             */
            $table->string('final_result'); // Befriedigend / Gut / Sehr gut etc.
            $table->text('ergebnis_note')->nullable();

            /**
             * MAX VALUES (Written)
             */
            $table->integer('reading_max')->default(75);
            $table->integer('grammar_max')->nullable()->default(30);
            $table->integer('listening_max')->default(75);
            $table->integer('writing_max')->default(45);
            $table->integer('speaking_max')->nullable();

            /**
             * MAX VALUES (Oral)
             */
            $table->integer('presentation_max')->nullable()->default(25);
            $table->integer('discussion_max')->nullable()->default(25);
            $table->integer('problemsolving_max')->nullable()->default(25);

            /**
             * TOTALS MAX
             */
            $table->integer('written_max')->nullable()->default(225);
            $table->integer('oral_max')->nullable()->default(75);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
