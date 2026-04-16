<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LevelQuizController extends Controller
{
    /**
     * Calculate remaining seconds for the current quiz attempt.
     * If time expired, returns <= 0
     */
    private function getRemainingSeconds(int $timeLimitSeconds): int
    {
        $startedAt = session('quiz_attempt_started_at');
        if (!$startedAt) {
            return $timeLimitSeconds;
        }

        $elapsed = now()->diffInSeconds(Carbon::createFromTimestamp($startedAt));
        return $timeLimitSeconds - $elapsed;
    }

    /**
     * Initialize or get quiz attempt session data
     */
    private function initializeQuizAttempt(int $timeLimitSeconds): void
    {
        if (!session()->has('quiz_attempt_started_at')) {
            session([
                'quiz_attempt_started_at' => now()->timestamp,
                'quiz_time_limit_seconds' => $timeLimitSeconds,
            ]);
        }
    }

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

        // ✅ GLOBAL TIMER: Initialize attempt on first load
        $timeLimitSeconds = (int) ($quizModel->time_limit_seconds ?? 0);
        $this->initializeQuizAttempt($timeLimitSeconds);
        $remainingSeconds = max(0, $this->getRemainingSeconds($timeLimitSeconds));

        // ✅ Check if time has expired (time-cheat prevention)
        if ($timeLimitSeconds > 0 && $remainingSeconds <= 0) {
            // Time expired => redirect to result with zero score
            session()->put('level_quiz_result', [
                'quiz_level' => $quizLevel,
                'answered' => 0,
                'correct' => 0,
                'total' => $questions->count(),
                'percent' => 0,
                'score_points' => 0,
                'total_points' => $questions->sum('points') ?? 0,
                'time_expired' => true,
            ]);
            session()->forget(['quiz_attempt_started_at', 'quiz_time_limit_seconds']);
            return redirect()->route('front.discover-your-level', ['level' => 'A1']);
        }

        $quiz = [
            'id' => $quizModel->id,
            'title' => $quizModel->title,
            'subtitle' => $quizModel->description,
            'time_limit_seconds' => (int) ($quizModel->time_limit_seconds ?? 0),
            'remaining_seconds' => $remainingSeconds,
            'questions' => $questions->map(function ($q) {

                // ===== RULE: If options_type="image", FORCE question_media_type to "none" =====
                $optionsType = (string) $q->options_type; // 'text' | 'image'
                $questionMediaType = $optionsType === 'image' ? 'none' : (string) $q->question_media_type;

                // ===== Question media URLs =====
                $imageUrl = $q->getFirstMediaUrl('question_image') ?: null;
                // ✅ NEW: Use audio_url column (external URL)
                // Fallback to Spatie for backward compatibility
                $audioUrl = $q->audio_url ?: ($q->getFirstMediaUrl('question_audio') ?: null);

                // ===== Determine which media to expose (only if NOT in image-options mode) =====
                $mediaType = 'none';
                $mediaUrl  = null;

                if ($questionMediaType === 'image' && $imageUrl) {
                    $mediaType = 'image';
                    $mediaUrl = $imageUrl;
                } elseif ($questionMediaType === 'audio' && $audioUrl) {
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

                    // ===== NEW: Send options_type to frontoffice =====
                    'options_type' => $optionsType,
                    'question_media_type' => $questionMediaType,

                    'media' => [
                        'type' => $mediaType, // none | image | audio
                        'url'  => $mediaUrl,
                    ],

                    // ===== Render choices with image URLs for image-options mode =====
                    // ✅ Shuffle options so the correct answer isn't always first
                    'choices' => $q->options->shuffle()->values()->map(function ($opt) {
                        return [
                            'id' => (string) $opt->id,
                            'label' => $opt->option_text,
                            'image_url' => $opt->getFirstMediaUrl('option_image') ?: null,
                            // ❌ DO NOT send is_correct to frontoffice
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        // Check if returning with result from answer()
        $quizResult = session()->pull('level_quiz_result');

        return view('frontoffice.quiz.index', compact('quiz', 'quizLevel', 'quizResult'));
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

        // ✅ GLOBAL TIMER: Check if time expired (anti-cheat)
        $timeLimitSeconds = (int) ($quizModel->time_limit_seconds ?? 0);
        $remainingSeconds = $this->getRemainingSeconds($timeLimitSeconds);

        if ($timeLimitSeconds > 0 && $remainingSeconds <= 0) {
            // Time expired => return 0 score
            session()->put('level_quiz_result', [
                'quiz_level' => $quizLevel,
                'answered' => 0,
                'correct' => 0,
                'total' => $quizModel->questions()->count(),
                'percent' => 0,
                'score_points' => 0,
                'total_points' => $quizModel->questions()->sum('points') ?? 0,
                'time_expired' => true,
            ]);
            session()->forget(['quiz_attempt_started_at', 'quiz_time_limit_seconds']);
            return redirect()->route('front.discover-your-level', ['level' => 'A1']);
        }

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

        $request->validate([
            'answers_json' => ['required', 'json'],
        ]);

        // Parse answers from JSON: { "question_id": "choice_id", ... }
        $answersJson = json_decode($request->input('answers_json', '{}'), true) ?? [];

        $answeredCount = 0;
        $correctCount  = 0;
        $scorePoints = 0;
        $totalPoints = 0;

        foreach ($questions as $question) {
            $qid = (string) $question->id;

            $points = (int) ($question->points ?? 1);
            $totalPoints += $points;

            // Get the choice ID from answers (string, not index)
            $chosenChoiceId = $answersJson[$qid] ?? null;

            if ($chosenChoiceId === null || $chosenChoiceId === '') {
                // Not answered
                continue;
            }

            $answeredCount++;

            // Find the chosen option by ID
            $chosenOption = $question->options
                ->where('id', (int) $chosenChoiceId)
                ->first();

            // If option exists and is correct, increment score
            if ($chosenOption && (bool) $chosenOption->is_correct) {
                $correctCount++;
                $scorePoints += $points;
            }
        }

        // Percent based on question count
        $percent = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

        // Store result in session so you can show it in any view
        session()->put('level_quiz_result', [
            'quiz_level' => $quizLevel,
            'answered' => $answeredCount,
            'correct' => $correctCount,
            'total' => $total,
            'percent' => $percent,
            'score_points' => $scorePoints,
            'total_points' => $totalPoints,
        ]);

        // ✅ GLOBAL TIMER: Clean up session data
        session()->forget(['quiz_attempt_started_at', 'quiz_time_limit_seconds']);

        // Redirect back to quiz with result
        return redirect()->route('front.discover-your-level.quiz', [
            'quiz' => $quizLevel,
            'result' => 1,
        ]);
    }

}
