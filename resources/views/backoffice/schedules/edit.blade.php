@extends('layouts.main')

@section('title', 'Modifier planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Modifier entrée')

@section('content')

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">Modifier entrée</h5>
                            <p class="text-muted mb-0 small">{{ $schedule->user->name ?? '—' }} — {{ $schedule->date->format('d/m/Y') }}</p>
                        </div>
                        <a href="{{ route('backoffice.schedules.index') }}" class="btn btn-outline-secondary">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('backoffice.schedules.update', $schedule) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Employé <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('user_id', $schedule->user_id) == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ old('date', $schedule->date->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label text-primary fw-semibold">Début</label>
                                <input type="time" name="start_time" class="form-control" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-primary fw-semibold">Fin</label>
                                <input type="time" name="end_time" class="form-control" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-warning fw-semibold">Pause début</label>
                                <input type="time" name="break_start" class="form-control" value="{{ old('break_start', $schedule->break_start ? substr($schedule->break_start, 0, 5) : '') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-warning fw-semibold">Pause fin</label>
                                <input type="time" name="break_end" class="form-control" value="{{ old('break_end', $schedule->break_end ? substr($schedule->break_end, 0, 5) : '') }}">
                            </div>
                        </div>

                        <div class="alert alert-info mb-4">
                            <strong>Actuel :</strong> {{ $schedule->worked_formatted }} travaillées
                            (amplitude {{ $schedule->total_span_formatted }}, pause {{ $schedule->break_formatted }})
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $schedule->notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph-duotone ph-check me-1"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
