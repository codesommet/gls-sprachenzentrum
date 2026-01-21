@php
    $isEdit = !empty($question);
    $options = old('options');

    if ($options === null) {
        $options = $isEdit
            ? $question->options->sortBy('sort_order')->values()->map(fn($o) => ['text' => $o->option_text])->toArray()
            : [
                ['text' => ''],
                ['text' => ''],
                ['text' => ''],
                ['text' => ''],
            ];
    }

    $correctIndex = old('correct_index');
    if ($correctIndex === null && $isEdit) {
        $correct = $question->options->sortBy('sort_order')->values()->search(fn($o) => (bool)$o->is_correct);
        $correctIndex = ($correct === false) ? 0 : $correct;
    }

    // Existing media URLs (Spatie)
    $imageUrl = $isEdit ? ($question->getFirstMediaUrl('question_image') ?: null) : null;
    $audioUrl = $isEdit ? ($question->getFirstMediaUrl('question_audio') ?: null) : null;
@endphp

<div class="row g-3">
    {{-- Question text --}}
    <div class="col-12">
        <label class="form-label">Question</label>
        <textarea name="question_text" rows="3" class="form-control @error('question_text') is-invalid @enderror">{{ old('question_text', $question->question_text ?? '') }}</textarea>
        @error('question_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Optional Media (Image/Audio) --}}
    <div class="col-12">
        <label class="form-label">Media (optional)</label>
        <div class="row g-3">

            {{-- Image upload --}}
            <div class="col-md-6">
                <label class="form-label">Image (optional)</label>
                <input
                    type="file"
                    name="image"
                    accept="image/*"
                    class="form-control @error('image') is-invalid @enderror"
                >
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if($imageUrl)
                    <div class="mt-2">
                        <img
                            src="{{ $imageUrl }}"
                            alt="Question image"
                            style="max-width: 220px; height:auto; border-radius: 8px;"
                        >
                        <div class="text-muted small mt-1">Current image</div>
                    </div>
                @endif
            </div>

            {{-- Audio upload --}}
            <div class="col-md-6">
                <label class="form-label">Audio (optional)</label>
                <input
                    type="file"
                    name="audio"
                    accept="audio/*"
                    class="form-control @error('audio') is-invalid @enderror"
                >
                @error('audio') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if($audioUrl)
                    <div class="mt-2">
                        <audio controls style="width: 100%;">
                            <source src="{{ $audioUrl }}">
                            Your browser does not support the audio element.
                        </audio>
                        <div class="text-muted small mt-1">Current audio</div>
                    </div>
                @endif
            </div>

            {{-- Media caption --}}
            <div class="col-12">
                <label class="form-label">Media caption (optional)</label>
                <input
                    type="text"
                    name="media_caption"
                    value="{{ old('media_caption', $question->media_caption ?? '') }}"
                    class="form-control @error('media_caption') is-invalid @enderror"
                    placeholder="Example: Listen and choose the correct answer"
                >
                @error('media_caption') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>
    </div>

    {{-- Difficulty --}}
    <div class="col-md-3">
        <label class="form-label">Difficulty (1-5)</label>
        <input type="number" name="difficulty" value="{{ old('difficulty', $question->difficulty ?? 1) }}"
               class="form-control @error('difficulty') is-invalid @enderror" min="1" max="5">
        @error('difficulty') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Points --}}
    <div class="col-md-3">
        <label class="form-label">Points</label>
        <input type="number" name="points" value="{{ old('points', $question->points ?? 1) }}"
               class="form-control @error('points') is-invalid @enderror" min="1" max="50">
        @error('points') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Sort order --}}
    <div class="col-md-3">
        <label class="form-label">Sort order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $question->sort_order ?? 0) }}"
               class="form-control @error('sort_order') is-invalid @enderror" min="0" max="9999">
        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Active --}}
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="q_is_active"
                   @checked(old('is_active', $question->is_active ?? true))>
            <label class="form-check-label" for="q_is_active">Active</label>
        </div>
    </div>

    {{-- Options --}}
    <div class="col-12">
        <hr>
        <h6 class="mb-2">Options</h6>

        @error('options') <div class="text-danger mb-2">{{ $message }}</div> @enderror
        @error('correct_index') <div class="text-danger mb-2">{{ $message }}</div> @enderror

        <div class="row g-2">
            @foreach($options as $i => $opt)
                <div class="col-12 col-md-6">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_index" value="{{ $i }}"
                                   @checked((int)$correctIndex === (int)$i)>
                        </div>

                        <input type="text"
                               name="options[{{ $i }}][text]"
                               value="{{ $opt['text'] ?? '' }}"
                               class="form-control @error('options.'.$i.'.text') is-invalid @enderror"
                               placeholder="Option {{ $i + 1 }}">

                        @error('options.'.$i.'.text')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-muted small mt-2">
            Select the radio button to mark the correct answer.
        </div>
    </div>
</div>
