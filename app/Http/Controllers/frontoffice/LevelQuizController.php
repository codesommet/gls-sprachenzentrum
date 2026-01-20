<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class LevelQuizController extends Controller
{
    public function showQuiz(Request $request)
    {
        $quizLevel = strtoupper($request->query('quiz', 'A1'));
        $allowed = ['A1', 'A2', 'B1', 'B2'];
        $quizLevel = in_array($quizLevel, $allowed, true) ? $quizLevel : 'A1';

        $quizModel = Quiz::query()
            ->where('level', $quizLevel)
            ->where('is_active', true)
            ->firstOrFail();

        $questions = $quizModel->questions()
            ->where('is_active', true)
            ->with(['options' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $quiz = [
            'id' => $quizModel->id,
            'title' => $quizModel->title,
            'subtitle' => $quizModel->description,
            'questions' => $questions->map(function ($q) {

                // ✅ Spatie Media Library URLs (will be /media/... with your config)
                $imageUrl = $q->getFirstMediaUrl('question_image') ?: null;
                $audioUrl = $q->getFirstMediaUrl('question_audio') ?: null;

                // Decide which media to expose (image first, then audio)
                $mediaType = 'none';
                $mediaUrl  = null;

                if ($imageUrl) {
                    $mediaType = 'image';
                    $mediaUrl = $imageUrl;
                } elseif ($audioUrl) {
                    $mediaType = 'audio';
                    $mediaUrl = $audioUrl;
                }

                return [
                    'id' => $q->id,
                    'type' => 'single',

                    // ✅ use media_caption as title
                    'title' => $q->media_caption,

                    'prompt' => null,
                    'question' => $q->question_text,

                    'media' => [
                        'type' => $mediaType, // none | image | audio
                        'url'  => $mediaUrl,
                    ],

                    'choices' => $q->options->values()->map(function ($opt) {
                        return [
                            'id' => (string) $opt->id,
                            'label' => $opt->option_text,
                            'is_correct' => (bool) $opt->is_correct,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        return view('frontoffice.quiz.index', compact('quiz', 'quizLevel'));
    }

    public function answer(Request $request)
    {
        $quizLevel = strtoupper($request->input('quiz', ''));
        $allowed = ['A1', 'A2', 'B1', 'B2'];
        $quizLevel = in_array($quizLevel, $allowed, true) ? $quizLevel : '';

        if (!$quizLevel) {
            return redirect()->route('front.discover-your-level');
        }

        $quizModel = Quiz::query()
            ->where('level', $quizLevel)
            ->where('is_active', true)
            ->firstOrFail();

        $questions = $quizModel->questions()
            ->where('is_active', true)
            ->with(['options' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $total = $questions->count();
        if ($total === 0) {
            return redirect()->route('front.discover-your-level', ['quiz' => $quizLevel]);
        }

        $qIndex = (int) $request->input('q', 1);
        if ($qIndex < 1) $qIndex = 1;
        if ($qIndex > $total) $qIndex = $total;

        $request->validate([
            // index du choix choisi (0..N). Tu as mis max:10, on garde comme toi.
            'answer_index' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        // Save answer in session
        $key = 'level_quiz_answers.' . $quizLevel;
        $answers = session()->get($key, []);
        $answers[$qIndex] = (int) $request->input('answer_index');
        session()->put($key, $answers);

        // Finished?
        if ($qIndex >= $total) {

            $answeredCount = 0;
            $correctCount  = 0;

            $scorePoints = 0;
            $totalPoints = 0;

            foreach ($questions as $idx => $question) {
                $n = $idx + 1;

                $points = (int) ($question->points ?? 1);
                $totalPoints += $points;

                $chosenIndex = $answers[$n] ?? null;
                if ($chosenIndex === null) {
                    continue;
                }

                $answeredCount++;

                $opt = $question->options->values()->get((int) $chosenIndex);

                if ($opt && (bool) $opt->is_correct) {
                    $correctCount++;
                    $scorePoints += $points;
                }
            }

            // Percent based on question count (simple + stable)
            $percent = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

            // Detect level from percent (adjust thresholds if you want)
            $detectedLevel = $this->detectLevelFromPercent($percent);

            // Store result in session so you can show it in any view
            session()->put('level_quiz_result', [
                'quiz_level' => $quizLevel,
                'detected_level' => $detectedLevel,
                'answered' => $answeredCount,
                'correct' => $correctCount,
                'total' => $total,
                'percent' => $percent,
                'score_points' => $scorePoints,
                'total_points' => $totalPoints,
            ]);

            // Clear answers session for this level (optional but clean)
            session()->forget($key);

            // Redirect where you want (keep your existing route)
            return redirect()->route('front.discover-your-level', [
                'level' => $detectedLevel, // ✅ niveau estimé final
            ]);
        }

        // Next question
        return redirect()->route('front.discover-your-level', [
            'quiz' => $quizLevel,
            'q'    => $qIndex + 1,
        ]);
    }

    private function detectLevelFromPercent(int $percent): string
    {
        // ✅ Simple thresholds (tu peux les changer)
        if ($percent >= 85) return 'B2';
        if ($percent >= 70) return 'B1';
        if ($percent >= 50) return 'A2';
        return 'A1';
    }
}
