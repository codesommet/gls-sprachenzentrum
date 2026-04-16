<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use App\Models\WeeklyReport;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WeeklyReportSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::all();

        if ($teachers->isEmpty()) {
            $this->command->warn('Aucun enseignant trouvé. Lancez DemoDataSeeder d\'abord.');
            return;
        }

        $createdBy = User::first()?->id;

        // Notes templates — realistic teacher activity reports
        $notesPool = [
            'Cours A1 – Révision des verbes irréguliers + exercices de conjugaison. Les étudiants progressent bien.',
            'Test de niveau pour 3 nouveaux étudiants. Résultat : 2 × A2, 1 × B1.',
            'Préparation intensive Telc B1 – simulation d\'examen écrit (Leseverstehen + Sprachbausteine).',
            'Cours B2 – Débat en classe sur le thème "Umweltschutz". Participation active de tous les étudiants.',
            'Correction des devoirs + exercices de grammaire (Konjunktiv II). Quelques difficultés observées.',
            'Atelier expression orale A2 – jeux de rôle : au restaurant, à la gare, chez le médecin.',
            'Préparation Goethe-Zertifikat B1 – entraînement à l\'expression écrite (Brief/E-Mail).',
            'Révision générale avant examen. Distribution des fiches récapitulatives.',
            'Cours A1 – Introduction au Perfekt avec haben/sein. Exercices pratiques.',
            'Session de rattrapage pour 4 étudiants absents la semaine dernière.',
            'Cours B1 – Compréhension orale : écoute de podcasts + questionnaire.',
            'Examen blanc Telc B2 – partie orale (Präsentation + Diskussion).',
            'Cours A2 – Les prépositions de lieu (Wechselpräpositionen) avec activités ludiques.',
            'Réunion pédagogique avec la coordination – planification du programme du mois prochain.',
            'Cours intensif – Vocabulaire professionnel (Bewerbung, Lebenslauf, Vorstellungsgespräch).',
            'Évaluation continue : tests de vocabulaire + note de participation pour le mois.',
            'Cours B1 – Lecture d\'un article de journal + discussion en groupe.',
            'Atelier écriture créative – les étudiants rédigent un texte sur leur ville natale.',
            'Préparation au TestDaF – stratégies pour le Leseverstehen et le Hörverstehen.',
            'Cours A1 – Alphabet, chiffres, se présenter. Premier cours du nouveau groupe.',
            'Remise des certificats A1 aux étudiants ayant réussi l\'examen du mois dernier.',
            'Cours B2 – Analyse d\'un texte littéraire (Kafka, "Die Verwandlung" – extrait).',
            'Soutien individuel pour 2 étudiants en difficulté – exercices ciblés sur la syntaxe.',
            'Cours A2 – Les verbes à particule séparable + exercices contextualisés.',
            'Organisation de la journée portes ouvertes – préparation des activités pédagogiques.',
        ];

        // Seed 4 weeks of reports: current week + 3 previous weeks
        $startMonday = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(3);
        $noteIndex = 0;
        $created = 0;

        for ($week = 0; $week < 4; $week++) {
            $monday = $startMonday->copy()->addWeeks($week);

            foreach ($teachers as $teacher) {
                // Each teacher gets 3-5 reports per week (not every day)
                $daysToReport = collect([0, 1, 2, 3, 4])->shuffle()->take(rand(3, 5));

                foreach ($daysToReport as $dayOffset) {
                    $date = $monday->copy()->addDays($dayOffset);

                    // Don't seed future dates
                    if ($date->isAfter(Carbon::today())) {
                        continue;
                    }

                    $notes = $notesPool[$noteIndex % count($notesPool)];
                    $noteIndex++;

                    WeeklyReport::firstOrCreate(
                        [
                            'teacher_id'  => $teacher->id,
                            'report_date' => $date->format('Y-m-d'),
                        ],
                        [
                            'notes'      => $notes,
                            'created_by' => $createdBy,
                        ]
                    );

                    $created++;
                }
            }
        }

        $this->command->info("{$created} rapports semaine créés pour {$teachers->count()} enseignants sur 4 semaines.");
    }
}
