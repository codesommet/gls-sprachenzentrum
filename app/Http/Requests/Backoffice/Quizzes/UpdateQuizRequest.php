<?php

namespace App\Http\Requests\Backoffice\Quizzes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $quizId = $this->route('quiz')?->id ?? $this->route('quiz');

        return [
            'level' => ['required', Rule::in(['A1','A2','B1','B2']), Rule::unique('quizzes','level')->ignore($quizId)],
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'time_limit_seconds' => ['nullable','integer','min:30','max:7200'],
            'questions_per_attempt' => ['required','integer','min:1','max:100'],
            'is_active' => ['sometimes','boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => (bool) $this->input('is_active', false),
        ]);
    }
}
