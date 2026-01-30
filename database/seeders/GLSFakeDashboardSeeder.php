<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GLSFakeDashboardSeeder extends Seeder
{
    public function run(): void
    {
        // ⚠️ DEV ONLY : vider pour éviter doublons
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('blog_posts')->truncate();
        DB::table('blog_categories')->truncate();
        DB::table('certificates')->truncate();
        DB::table('gls_inscriptions')->truncate();
        DB::table('consultations')->truncate();
        DB::table('newsletter_subscribers')->truncate();
        DB::table('group_applications')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // 1) Blog categories
        $catIds = [];
        for ($i = 1; $i <= 4; $i++) {
            $nameFr = "Catégorie $i";
            $catIds[] = DB::table('blog_categories')->insertGetId([
                'name_fr' => $nameFr,
                'name_en' => "Category $i",
                'name_ar' => null,
                'name_de' => null,
                'slug' => Str::slug($nameFr) . '-' . Str::random(6),
                'is_active' => true,
                'position' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Helpers pour générer des dates réparties sur 12 mois
        $randomDateInMonth = function (Carbon $monthStart) {
            return $monthStart->copy()->addDays(rand(0, 25))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
        };

        // 2) Blog posts (12 mois, published + draft)
        for ($m = 0; $m < 12; $m++) {
            $monthStart = $now->copy()->subMonths($m)->startOfMonth();

            $count = rand(3, 12);
            for ($i = 1; $i <= $count; $i++) {
                $titleFr = "Article " . ($m + 1) . "-" . $i;
                $created = $randomDateInMonth($monthStart);

                DB::table('blog_posts')->insert([
                    'category_id' => $catIds[array_rand($catIds)],
                    'title_fr' => $titleFr,
                    'title_en' => "Post " . ($m + 1) . "-" . $i,
                    'slug' => Str::slug($titleFr) . '-' . Str::random(6),
                    'content_fr' => "Contenu FR fake pour $titleFr",
                    'content_en' => "Fake EN content for $titleFr",
                    'reading_time' => rand(2, 8),
                    'featured' => (bool) rand(0, 1),
                    'views' => rand(0, 500),
                    'status' => (rand(1, 10) <= 8) ? 'published' : 'draft',
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }

        // 3) Certificates (12 mois)
        for ($m = 0; $m < 12; $m++) {
            $monthStart = $now->copy()->subMonths($m)->startOfMonth();
            $count = rand(2, 10);

            for ($i = 1; $i <= $count; $i++) {
                $created = $randomDateInMonth($monthStart);
                $examDate = $created->copy()->subDays(rand(1, 20))->toDateString();
                $issueDate = $created->copy()->toDateString();

                DB::table('certificates')->insert([
                    'last_name' => 'Nom' . rand(10, 99),
                    'first_name' => 'Prénom' . rand(10, 99),
                    'birth_date' => $now->copy()->subYears(rand(18, 35))->subDays(rand(0, 365))->toDateString(),
                    'birth_place' => 'Maroc',
                    'exam_level' => 'Deutsch B2',
                    'exam_date' => $examDate,
                    'issue_date' => $issueDate,
                    'certificate_number' => 'GLS-' . strtoupper(Str::random(10)),

                    'reading_score' => rand(30, 75),
                    'grammar_score' => rand(10, 30),
                    'listening_score' => rand(30, 75),
                    'writing_score' => rand(15, 45),
                    'written_total' => 0, // on calcule après
                    'presentation_score' => rand(10, 25),
                    'discussion_score' => rand(10, 25),
                    'problemsolving_score' => rand(10, 25),
                    'oral_total' => 0, // on calcule après
                    'final_result' => 'Bien',

                    'reading_max' => 75,
                    'grammar_max' => 30,
                    'listening_max' => 75,
                    'writing_max' => 45,
                    'presentation_max' => 25,
                    'discussion_max' => 25,
                    'problemsolving_max' => 25,
                    'written_max' => 225,
                    'oral_max' => 75,

                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }

        // Patch totals (written_total, oral_total) propre
        DB::table('certificates')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $c) {
                $written = (int)$c->reading_score + (int)$c->grammar_score + (int)$c->listening_score + (int)$c->writing_score;
                $oral = (int)$c->presentation_score + (int)$c->discussion_score + (int)$c->problemsolving_score;

                DB::table('certificates')->where('id', $c->id)->update([
                    'written_total' => $written,
                    'oral_total' => $oral,
                ]);
            }
        });

        // 4) Inscriptions (12 mois)
        for ($m = 0; $m < 12; $m++) {
            $monthStart = $now->copy()->subMonths($m)->startOfMonth();
            $count = rand(3, 20);

            for ($i = 1; $i <= $count; $i++) {
                $created = $randomDateInMonth($monthStart);

                DB::table('gls_inscriptions')->insert([
                    'name' => 'Etudiant ' . rand(100, 999),
                    'email' => 'etudiant' . Str::random(6) . '@example.com',
                    'phone' => '+2126' . rand(10000000, 99999999),
                    'adresse' => 'Adresse fake',
                    'niveau' => ['A1','A2','B1','B2'][array_rand(['A1','A2','B1','B2'])],
                    'type_cours' => 'Intensif',
                    'horaire_prefere' => 'Matin',
                    'date_start' => $created->copy()->addDays(rand(1, 30))->toDateString(),
                    'centre' => ['Marrakech','Rabat','Casablanca','Kenitra'][array_rand(['Marrakech','Rabat','Casablanca','Kenitra'])],
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }

        // 5) Consultations (12 mois)
        for ($m = 0; $m < 12; $m++) {
            $monthStart = $now->copy()->subMonths($m)->startOfMonth();
            $count = rand(1, 12);

            for ($i = 1; $i <= $count; $i++) {
                $created = $randomDateInMonth($monthStart);

                DB::table('consultations')->insert([
                    'name' => 'Lead ' . rand(100, 999),
                    'city' => ['Marrakech','Rabat','Casablanca','Kenitra'][array_rand(['Marrakech','Rabat','Casablanca','Kenitra'])],
                    'phone' => '+2126' . rand(10000000, 99999999),
                    'email' => 'lead' . Str::random(6) . '@example.com',
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }

        // 6) Newsletter (12 mois)
        for ($m = 0; $m < 12; $m++) {
            $monthStart = $now->copy()->subMonths($m)->startOfMonth();
            $count = rand(2, 15);

            for ($i = 1; $i <= $count; $i++) {
                $created = $randomDateInMonth($monthStart);

                DB::table('newsletter_subscribers')->insert([
                    'email' => 'sub' . Str::random(8) . '@example.com',
                    'locale' => ['fr','en','ar'][array_rand(['fr','en','ar'])],
                    'source' => 'dashboard_seed',
                    'subscribed_at' => $created,
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }

        // 7) Group applications (besoin d’un group_id existant)
        $groupId = DB::table('groups')->value('id');
        if ($groupId) {
            for ($i = 1; $i <= 30; $i++) {
                $created = $now->copy()->subDays(rand(0, 90));
                $status = ['pending','approved','rejected'][array_rand(['pending','approved','rejected'])];

                DB::table('group_applications')->insert([
                    'group_id' => $groupId,
                    'full_name' => 'Candidat ' . rand(100, 999),
                    'whatsapp_number' => '+2126' . rand(10000000, 99999999),
                    'email' => 'cand' . Str::random(7) . '@example.com',
                    'birthday' => $now->copy()->subYears(rand(18, 35))->subDays(rand(0, 365))->toDateString(),
                    'note' => 'Note fake',
                    'status' => $status,
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }
    }
}
