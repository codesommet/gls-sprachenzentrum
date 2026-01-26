<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Quizzes\StoreQuizQuestionRequest;
use App\Http\Requests\Backoffice\Quizzes\UpdateQuizQuestionRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;

class QuizQuestionController extends Controller
{
    public function index(Quiz $quiz)
    {
        $questions = $quiz->questions()->with('options')->orderBy('sort_order')->orderBy('id')->get();

        return view('backoffice.quizzes.questions.index', compact('quiz', 'questions'));
    }

    public function create(Quiz $quiz)
    {
        return view('backoffice.quizzes.questions.create', compact('quiz'));
    }

    public function store(StoreQuizQuestionRequest $request, Quiz $quiz)
    {
        $data = $request->validated();

        $question = $quiz->questions()->create([
            'question_text' => $data['question_text'],
            'difficulty' => $data['difficulty'],
            'points' => $data['points'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),

            // optional media meta
            'media_caption' => $data['media_caption'] ?? null,
            'media_type' => 'none',
        ]);

        /* ==========================
       OPTIONS (QCM)
    ========================== */
        $correctIndex = (int) $data['correct_index'];

        foreach ($data['options'] as $i => $opt) {
            $question->options()->create([
                'option_text' => $opt['text'],
                'is_correct' => $i === $correctIndex,
                'sort_order' => $i,
            ]);
        }

        $this->handleQuestionMediaUpload($question, $request);

        if ($request->input('action') === 'save_next') {
            return redirect()->route('backoffice.quizzes.questions.create', $quiz)->with('success', 'Question saved. You can add the next one.');
        }

        return redirect()->route('backoffice.quizzes.questions.index', $quiz)->with('success', 'Question created successfully.');
    }

    public function edit(Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        $question->load('options');

        return view('backoffice.quizzes.questions.edit', compact('quiz', 'question'));
    }

    public function update(UpdateQuizQuestionRequest $request, Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        $data = $request->validated();

        $question->update([
            'question_text' => $data['question_text'],
            'difficulty' => $data['difficulty'],
            'points' => $data['points'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),

            // optional meta
            'media_caption' => $data['media_caption'] ?? null,
        ]);

        // Options (reset)
        $question->options()->delete();

        $correctIndex = (int) $data['correct_index'];

        foreach ($data['options'] as $i => $opt) {
            $question->options()->create([
                'option_text' => $opt['text'],
                'is_correct' => $i === $correctIndex,
                'sort_order' => $i,
            ]);
        }

        // Optional media upload (Spatie)
        $this->handleQuestionMediaUpload($question, $request);

        return redirect()->route('backoffice.quizzes.questions.index', $quiz)->with('success', 'Question updated successfully.');
    }

    public function destroy(Quiz $quiz, QuizQuestion $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        $question->delete();

        return back()->with('success', 'Question deleted successfully.');
    }

    /**
     * Handle optional image/audio uploads and set media_type.
     * - If no file uploaded => keep existing media, only recompute type
     * - If file uploaded => replace the corresponding collection (singleFile)
     */
    private function handleQuestionMediaUpload(QuizQuestion $question, $request): void
    {
        // Replace image only if uploaded
        if ($request->hasFile('image')) {
            $question->addMedia($request->file('image'))->toMediaCollection('question_image');
        }

        // Replace audio only if uploaded
        if ($request->hasFile('audio')) {
            $question->addMedia($request->file('audio'))->toMediaCollection('question_audio');
        }

        // Compute media_type from current stored media
        $hasImage = !empty($question->getFirstMedia('question_image'));
        $hasAudio = !empty($question->getFirstMedia('question_audio'));

        $mediaType = 'none';
        if ($hasImage && $hasAudio) {
            $mediaType = 'both';
        } elseif ($hasImage) {
            $mediaType = 'image';
        } elseif ($hasAudio) {
            $mediaType = 'audio';
        }

        $question->update(['media_type' => $mediaType]);
    }
}
