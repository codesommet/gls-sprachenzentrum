@extends('layouts.main')

@section('title', $employee->name)
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', $employee->name)

@section('content')

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('backoffice.employees.index') }}" class="btn btn-outline-secondary">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
        </a>
        <h5 class="mb-0">{{ $employee->name }}</h5>
        <span class="badge bg-light-primary">{{ $employee->role }}</span>
        <span class="text-muted">{{ $employee->site->name }}</span>
    </div>

    {{-- Info cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Téléphone</p>
                    <h6 class="mb-0">{{ $employee->phone ?? '—' }}</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Email</p>
                    <h6 class="mb-0">{{ $employee->email ?? '—' }}</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Date d'embauche</p>
                    <h6 class="mb-0">{{ $employee->hired_at?->format('d/m/Y') ?? '—' }}</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Entrées planning</p>
                    <h3 class="mb-0 fw-bold">{{ $employee->schedules->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if($employee->schedules->isNotEmpty())
        @php
            $totalWorked = $employee->schedules->sum('worked_minutes');
            $totalBreak = $employee->schedules->sum('break_minutes');
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-start border-start-width-4 border-success">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Heures travaillées</p>
                        <h3 class="mb-0 text-success fw-bold">{{ \App\Models\EmployeeSchedule::formatMinutes($totalWorked) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-start border-start-width-4 border-warning">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Total pause</p>
                        <h3 class="mb-0 fw-bold">{{ \App\Models\EmployeeSchedule::formatMinutes($totalBreak) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Planning récent</h5>
                <a href="{{ route('backoffice.schedules.index', ['employee_id' => $employee->id]) }}" class="btn btn-link btn-sm">
                    Voir tout <i class="ph-duotone ph-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-center">Début</th>
                                <th class="text-center">Fin</th>
                                <th class="text-center">Amplitude</th>
                                <th class="text-center">Pause</th>
                                <th class="text-center text-success">Travaillé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->schedules as $s)
                                <tr>
                                    <td class="fw-medium">{{ $s->date->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ substr($s->start_time, 0, 5) }}</td>
                                    <td class="text-center">{{ substr($s->end_time, 0, 5) }}</td>
                                    <td class="text-center">{{ $s->total_span_formatted }}</td>
                                    <td class="text-center">{{ $s->break_minutes > 0 ? $s->break_formatted : '—' }}</td>
                                    <td class="text-center fw-bold text-success">{{ $s->worked_formatted }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="ph-duotone ph-info me-1"></i> Aucune entrée de planning pour cet employé.
        </div>
    @endif

@endsection
