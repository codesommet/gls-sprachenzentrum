<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('studienkollegs', function (Blueprint $table) {
            $table->id();

            /* =========================
             * CORE
             * ========================= */
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('university')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('Germany');

            /* =========================
             * MEDIA
             * ========================= */
            $table->string('hero_image')->nullable();
            $table->string('card_image')->nullable();
            $table->string('university_logo')->nullable();
            $table->string('video_url')->nullable();

            /* =========================
             * FLAGS
             * ========================= */
            $table->boolean('featured')->default(false);
            $table->boolean('public')->default(true);
            $table->boolean('uni_assist')->default(false);
            $table->boolean('entrance_exam')->default(false);

            /* =========================
             * ACADEMIC
             * ========================= */
            $table->unsignedTinyInteger('duration_semesters')->default(2);
            $table->string('tuition')->default('Free');
            $table->string('language_of_instruction')->default('German');

            /* =========================
             * APPLICATION
             * ========================= */
            $table->string('application_method')->nullable();
            $table->string('application_portal_note')->nullable();
            $table->string('application_url')->nullable();

            /* =========================
             * REQUIREMENTS & CERTIFICATION
             * ========================= */
            $table->json('requirements')->nullable();
            $table->boolean('certification_required')->default(false);
            $table->boolean('translation_required')->default(false);
            $table->string('translation_note')->nullable();

            /* =========================
             * EXAM
             * ========================= */
            $table->string('exam_subjects')->nullable();
            $table->string('exam_link')->nullable();
            $table->string('exam_url')->nullable();

            /* =========================
             * JSON DATA
             * ========================= */
            $table->json('courses')->nullable();
            $table->json('languages')->nullable();
            $table->json('documents')->nullable();
            $table->json('deadlines')->nullable();

            /* =========================
             * CONTACT & LOCATION
             * ========================= */
            $table->string('official_website')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('address')->nullable();
            $table->text('map_embed')->nullable();

            /* =========================
             * SEO
             * ========================= */
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studienkollegs');
    }
};
