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
            // question core
            'question_text' => ['required', 'string'],
            'difficulty' => ['required', 'integer', 'min:1', 'max:5'],
            'points' => ['nullable', 'integer', 'min:1', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],

            // new selectors (required)
            'question_media_type' => ['required', 'in:none,audio,image'],
            'options_type' => ['required', 'in:text,image'],

            // optional caption
            'media_caption' => ['nullable', 'string', 'max:255'],

            // ✅ NEW: audio_url (external URL, not file upload)
            'audio_url' => ['nullable', 'url', 'max:2048'],

            // image upload (Spatie - still used)
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            // options
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*.text' => ['nullable', 'string', 'max:255'],
            'options.*.image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'options.*.has_image' => ['sometimes', 'in:0,1'],

            // correct answer
            'correct_index' => ['required', 'integer', 'min:0', 'max:5'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $qType = (string) $this->input('question_media_type', 'none');
            $oType = (string) $this->input('options_type', 'text');
            $audioUrl = (string) $this->input('audio_url', '');

            /**
             * ===== NEW STRICT BUSINESS RULES =====
             *
             * If options_type == "image":
             *   - FORCE question_media_type = "none"
             *   - Do NOT allow audio_url
             *   - REQUIRE each option to have an image (or has_image=1)
             *
             * If options_type == "text":
             *   - question_media_type can be: none|image|audio
             *   - If question_media_type="audio": allow audio_url (required if new, optional if existing)
             *   - If question_media_type="image": REQUIRE question image (or existing)
             *   - REQUIRE each option to have text
             */

            // ===== RULE: If options_type=image, force question_media_type=none =====
            if ($oType === 'image') {
                $this->merge(['question_media_type' => 'none']);
                $qType = 'none';

                // Forbid audio_url when using image options
                if ($audioUrl !== '') {
                    $validator->errors()->add('audio_url', 'Aucune URL audio autorisée en mode "Options images".');
                }
            }

            // ===== Question media validation (only if options_type=text) =====
            if ($oType === 'text') {
                if ($qType === 'audio') {
                    // For update: allow existing audio_url from DB
                    $existingAudioUrl = $this->route('question')?->audio_url;
                    $hasNewUrl = $audioUrl !== '';

                    if (!$hasNewUrl && !$existingAudioUrl) {
                        $validator->errors()->add('audio_url', 'URL audio obligatoire pour une question avec audio.');
                    }
                }
                if ($qType === 'image') {
                    $hasNewFile = $this->hasFile('image');
                    $hasExisting = !!$this->route('question')?->getFirstMediaUrl('question_image');
                    if (!$hasNewFile && !$hasExisting) {
                        $validator->errors()->add('image', 'Image obligatoire pour une question avec image.');
                    }
                }
            }

            $options = $this->input('options', []);
            if (!is_array($options) || count($options) < 2) {
                return;
            }

            $correctIndex = (int) $this->input('correct_index', -1);
            if (!array_key_exists($correctIndex, $options)) {
                $validator->errors()->add('correct_index', 'La réponse correcte est invalide (index).');
            }

            /**
             * ===== OPTIONS VALIDATION =====
             */
            foreach ($options as $i => $opt) {
                $text = trim((string) ($opt['text'] ?? ''));
                $hasText = $text !== '';
                $hasImage = $this->hasFile("options.$i.image");
                $hasExistingImage = ((int) ($opt['has_image'] ?? 0)) === 1;

                if ($oType === 'text' && !$hasText) {
                    // Text mode: REQUIRE option text
                    $validator->errors()->add(
                        "options.$i.text",
                        'Option #' . ($i + 1) . ' : texte obligatoire.'
                    );
                }

                if ($oType === 'image' && !$hasImage && !$hasExistingImage) {
                    // Image mode: REQUIRE option image (or existing in edit)
                    $validator->errors()->add(
                        "options.$i.image",
                        'Option #' . ($i + 1) . ' : image obligatoire.'
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // When options_type='image', JS disables question_media_type select (not submitted)
        // Default to 'none' so the required rule passes
        $qmt = $this->input('question_media_type');
        if ($qmt === null || $qmt === '') {
            $qmt = 'none';
        }

        $this->merge([
            'is_active' => (bool) $this->input('is_active', false),
            'question_media_type' => $qmt,
            'points' => (int) ($this->input('points', 1) ?: 1),
            'sort_order' => (int) ($this->input('sort_order', 0) ?: 0),
        ]);
    }
}
