<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /**
             * Dataset complet (DE) par niveau
             * - quizzes: level unique
             * - quiz_questions: quiz_id, question_text, difficulty, points, sort_order, media_type, media_caption, is_active
             * - quiz_options: question_id, option_text, is_correct, sort_order
             *
             * NOTE:
             * - Media Spatie (audio/image) ne sont pas seedés ici par défaut.
             *   Tu peux les uploader depuis le backoffice.
             */
            $quizzes = [
                'A1' => [
                    'title' => 'Deutsch Einstufungstest A1',
                    'description' => 'Grundlagen: einfache Sätze, Alltag, Vorstellen, Fragen beantworten.',
                    'time_limit_seconds' => 600,
                    'questions_per_attempt' => 10,
                    'questions' => [
                        [
                            'question_text' => 'Welche Antwort ist richtig? „Ich ____ Adam.“',
                            'difficulty' => 1,
                            'points' => 1,
                            'sort_order' => 1,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'heiße', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'heißt', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'heißen', 'is_correct' => false,'sort_order' => 2],
                                ['option_text' => 'heißt du', 'is_correct' => false,'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wie fragt man nach dem Namen?',
                            'difficulty' => 1,
                            'points' => 1,
                            'sort_order' => 2,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Wie heißt du?', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Wo wohnst du?', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Wie alt bist du?', 'is_correct' => false,'sort_order' => 2],
                                ['option_text' => 'Was machst du?', 'is_correct' => false,'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Antwort passt? „Woher kommst du?“',
                            'difficulty' => 1,
                            'points' => 1,
                            'sort_order' => 3,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Ich komme aus Marokko.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich wohne zwei Zimmer.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich heiße Marokko.', 'is_correct' => false,'sort_order' => 2],
                                ['option_text' => 'Ich bin nach Hause.', 'is_correct' => false,'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Artikel ist richtig? ____ Haus',
                            'difficulty' => 2,
                            'points' => 1,
                            'sort_order' => 4,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'das', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'der', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'die', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'den', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Zahl ist „zwölf“?',
                            'difficulty' => 1,
                            'points' => 1,
                            'sort_order' => 5,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => '12', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => '20', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => '2',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => '11', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],

                'A2' => [
                    'title' => 'Deutsch Einstufungstest A2',
                    'description' => 'Erweiterte Grundlagen: Alltagssituationen, einfache Grammatik, kurze Texte.',
                    'time_limit_seconds' => 720,
                    'questions_per_attempt' => 10,
                    'questions' => [
                        [
                            'question_text' => 'Welche Antwort ist richtig? „Gestern ____ ich zu Hause.“',
                            'difficulty' => 2,
                            'points' => 2,
                            'sort_order' => 1,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'war', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'bin', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'ist', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'wäre','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wähle die richtige Präposition: „Ich gehe ____ die Schule.“',
                            'difficulty' => 2,
                            'points' => 2,
                            'sort_order' => 2,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'in',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'auf', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'bei', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'mit', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Satzstellung ist richtig?',
                            'difficulty' => 2,
                            'points' => 2,
                            'sort_order' => 3,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Morgen gehe ich einkaufen.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Gehe ich morgen einkaufen.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich morgen gehe einkaufen.', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Einkaufen gehe morgen ich.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Form ist richtig? „Er ____ nicht kommen.“',
                            'difficulty' => 3,
                            'points' => 2,
                            'sort_order' => 4,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'kann',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'können', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'kannst', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'könnt',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Antwort passt? „Was hast du am Wochenende gemacht?“',
                            'difficulty' => 3,
                            'points' => 2,
                            'sort_order' => 5,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Ich habe meine Familie besucht.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich besuche morgen meine Familie.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich werde meine Familie.', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich bin meine Familie besuchen.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],

                'B1' => [
                    'title' => 'Deutsch Einstufungstest B1',
                    'description' => 'Mittelstufe: Meinungen ausdrücken, Erfahrungen berichten, längere Sätze.',
                    'time_limit_seconds' => 900,
                    'questions_per_attempt' => 10,
                    'questions' => [
                        [
                            'question_text' => 'Welche Konjunktion passt? „Ich bleibe zu Hause, ____ es regnet.“',
                            'difficulty' => 3,
                            'points' => 3,
                            'sort_order' => 1,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'weil', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'aber', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'und',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'oder', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wähle die richtige Form: „Wenn ich Zeit ____ , komme ich vorbei.“',
                            'difficulty' => 3,
                            'points' => 3,
                            'sort_order' => 2,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'habe',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'hatte', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'hätte', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'haben', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche indirekte Frage ist richtig?',
                            'difficulty' => 4,
                            'points' => 3,
                            'sort_order' => 3,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Kannst du mir sagen, wo die Bank ist?', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Kannst du mir sagen, wo ist die Bank?', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Kannst du mir sagen, ist wo die Bank?', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Kannst du mir sagen, die Bank wo ist?', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Aussage ist korrekt?',
                            'difficulty' => 4,
                            'points' => 3,
                            'sort_order' => 4,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Ich interessiere mich für Sprachen.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich interessiere mir für Sprachen.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich interessiere für mich Sprachen.', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich interessiere mich auf Sprachen.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Zeitform passt? „Früher ____ ich oft ins Kino gegangen.“',
                            'difficulty' => 4,
                            'points' => 3,
                            'sort_order' => 5,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'bin',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'habe', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'war',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'werde','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],

                'B2' => [
                    'title' => 'Deutsch Einstufungstest B2',
                    'description' => 'Obere Mittelstufe: komplexere Strukturen, Synonyme, Textverständnis.',
                    'time_limit_seconds' => 1200,
                    'questions_per_attempt' => 10,
                    'questions' => [
                        [
                            'question_text' => 'Welche Formulierung ist stilistisch am besten?',
                            'difficulty' => 4,
                            'points' => 4,
                            'sort_order' => 1,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Ich bin der Ansicht, dass diese Maßnahme sinnvoll ist.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich denke, dass ist gut Maßnahme.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich Meinung habe, dass diese Maßnahme ist sinnvoll.', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich bin Ansicht, dass diese Maßnahme sinnvoll.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wähle das passende Wort: „Die Ergebnisse sind ____ überzeugend.“',
                            'difficulty' => 4,
                            'points' => 4,
                            'sort_order' => 2,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'äußerst', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'außen',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'äußern',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'außer',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Konstruktion ist korrekt?',
                            'difficulty' => 5,
                            'points' => 4,
                            'sort_order' => 3,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Je mehr man übt, desto besser wird man.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Je mehr man übt, je besser wird man.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Desto mehr man übt, je besser wird man.', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Je mehr man übt, besser wird man desto.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Umformung ist richtig?',
                            'difficulty' => 5,
                            'points' => 4,
                            'sort_order' => 4,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Obwohl er krank war, ging er zur Arbeit.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Obwohl er war krank, ging er zur Arbeit.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Obwohl krank er war, ging er zur Arbeit.', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Obwohl er krank, er ging zur Arbeit.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz ist grammatikalisch korrekt?',
                            'difficulty' => 5,
                            'points' => 4,
                            'sort_order' => 5,
                            'media_type' => 'none',
                            'media_caption' => null,
                            'options' => [
                                ['option_text' => 'Er behauptet, er habe davon nichts gewusst.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Er behauptet, er hat davon nichts gewusst.', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Er behauptet, er hätte davon nichts wusste.', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Er behauptet, er haben davon nichts gewusst.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],
            ];

            /**
             * 1) Create/Update quizzes (level unique)
             */
            $quizIds = [];

            foreach ($quizzes as $level => $quizData) {
                $quiz = Quiz::query()->updateOrCreate(
                    ['level' => $level],
                    [
                        'title' => $quizData['title'],
                        'description' => $quizData['description'] ?? null,
                        'time_limit_seconds' => $quizData['time_limit_seconds'] ?? null,
                        'questions_per_attempt' => $quizData['questions_per_attempt'] ?? 10,
                        'is_active' => true,
                    ]
                );

                $quizIds[$level] = $quiz->id;
            }

            /**
             * 2) Cleanup (questions/options + Spatie media) for these quizzes
             */
            $ids = array_values($quizIds);

            $existingQuestions = QuizQuestion::query()
                ->whereIn('quiz_id', $ids)
                ->with(['options.media', 'media'])
                ->get();

            foreach ($existingQuestions as $q) {
                // clear question media
                $q->clearMediaCollection('question_image');
                $q->clearMediaCollection('question_audio');

                // clear options media
                foreach ($q->options as $opt) {
                    $opt->clearMediaCollection('option_image');
                }
            }

            // delete options then questions
            QuizOption::query()
                ->whereIn('question_id', $existingQuestions->pluck('id'))
                ->delete();

            QuizQuestion::query()
                ->whereIn('quiz_id', $ids)
                ->delete();

            /**
             * 3) Insert fresh questions/options
             */
            foreach ($quizzes as $level => $quizData) {
                $quizId = $quizIds[$level];

                foreach ($quizData['questions'] as $qData) {

                    $question = QuizQuestion::query()->create([
                        'quiz_id' => $quizId,
                        'question_text' => $qData['question_text'],
                        'media_type' => $qData['media_type'] ?? 'none',
                        'media_caption' => $qData['media_caption'] ?? null,
                        'difficulty' => $qData['difficulty'] ?? 1,
                        'points' => $qData['points'] ?? 1,
                        'sort_order' => $qData['sort_order'] ?? 0,
                        'is_active' => true,
                    ]);

                    foreach ($qData['options'] as $optData) {
                        QuizOption::query()->create([
                            'question_id' => $question->id,
                            'option_text' => $optData['option_text'] ?? null,
                            'is_correct' => (bool) ($optData['is_correct'] ?? false),
                            'sort_order' => $optData['sort_order'] ?? 0,
                        ]);
                    }
                }
            }
        });
    }
}
