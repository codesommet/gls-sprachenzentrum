<?php

namespace App\Http\Requests\Backoffice\Quizzes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => ['required','string'],
            'difficulty' => ['required','integer','min:1','max:5'],
            'points' => ['required','integer','min:1','max:50'],
            'sort_order' => ['nullable','integer','min:0','max:9999'],
            'is_active' => ['sometimes','boolean'],

            'media_caption' => ['nullable','string','max:255'],
            'image' => ['nullable','file','mimes:jpg,jpeg,png,webp','max:5120'],
            'audio' => ['nullable','file','mimes:mp3,wav,ogg,m4a','max:10240'],

            'options' => ['required','array','min:2','max:6'],
            'options.*.text' => ['required','string','max:255'],
            'correct_index' => ['required','integer','min:0','max:5'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => (bool) $this->input('is_active', false),
        ]);
    }
}
