@php
    $isEdit = !empty($quiz);
@endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Niveau</label>
        <select name="level" class="form-select @error('level') is-invalid @enderror">
            @foreach ($levels as $lvl)
                <option value="{{ $lvl }}" @selected(old('level', $quiz->level ?? '') === $lvl)>
                    {{ $lvl }}
                </option>
            @endforeach
        </select>
        @error('level')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-9">
        <label class="form-label">Titre</label>
        <input type="text" name="title" value="{{ old('title', $quiz->title ?? '') }}"
            class="form-control @error('title') is-invalid @enderror">
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $quiz->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Durée limite (secondes)</label>
        <input type="number" name="time_limit_seconds"
            value="{{ old('time_limit_seconds', $quiz->time_limit_seconds ?? '') }}"
            class="form-control @error('time_limit_seconds') is-invalid @enderror">
        @error('time_limit_seconds')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Questions par tentative</label>
        <input type="number" name="questions_per_attempt"
            value="{{ old('questions_per_attempt', $quiz->questions_per_attempt ?? 10) }}"
            class="form-control @error('questions_per_attempt') is-invalid @enderror">
        @error('questions_per_attempt')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                @checked(old('is_active', $quiz->is_active ?? true))>
            <label class="form-check-label" for="is_active">
                Actif
            </label>
        </div>
    </div>
</div>
