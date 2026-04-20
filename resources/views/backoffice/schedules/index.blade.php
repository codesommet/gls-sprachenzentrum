@extends('layouts.main')

@section('title', 'Planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Planning')

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Maintenant</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="mb-0">Planning</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('backoffice.schedules.week') }}" class="btn btn-outline-primary">
                <i class="ph-duotone ph-calendar-check me-1"></i> Vue semaine
            </a>
            <a href="{{ route('backoffice.planning.export-form') }}" class="btn btn-outline-danger">
                <i class="ph-duotone ph-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('backoffice.schedules.create') }}" class="btn btn-primary">
                <i class="ph-duotone ph-plus me-1"></i> Nouvelle entrée
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold mb-1"><i class="ph-duotone ph-funnel me-1"></i> Filtres</label>
                </div>
                <div class="col-6 col-md">
                    <select name="site_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous centres</option>
                        @foreach($sites as $s)
                            <option value="{{ $s->id }}" {{ request('site_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md">
                    <select name="user_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous employés</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ request('user_id') == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md">
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous postes</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ request('role') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', now()->startOfMonth()->toDateString()) }}" onchange="this.form.submit()">
                </div>
                <div class="col-6 col-md">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', now()->endOfMonth()->toDateString()) }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small text-uppercase">Employés</p>
                    <h3 class="mb-0 fw-bold">{{ $employeeCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-start border-start-width-4 border-primary">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small text-uppercase">Amplitude</p>
                    <h3 class="mb-0 fw-bold">{{ \App\Models\UserSchedule::formatMinutes($totalSpan) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-start border-start-width-4 border-warning">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small text-uppercase">Pauses</p>
                    <h3 class="mb-0 fw-bold">{{ \App\Models\UserSchedule::formatMinutes($totalBreak) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-start border-start-width-4 border-success">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small text-uppercase">Travaillé</p>
                    <h3 class="mb-0 fw-bold text-success">{{ \App\Models\UserSchedule::formatMinutes($totalWorked) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Per-employee totals --}}
    @if($employeeTotals->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Totaux par employé</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Poste</th>
                                <th class="text-center">Jours</th>
                                <th class="text-center text-success">Travaillé</th>
                                <th class="text-center">Pause</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employeeTotals as $et)
                                <tr>
                                    <td class="fw-medium">{{ $et['employee']->name }}</td>
                                    <td><span class="badge bg-light-primary">{{ $et['employee']->staff_role ?? '—' }}</span></td>
                                    <td class="text-center">{{ $et['days'] }}</td>
                                    <td class="text-center fw-bold text-success">{{ \App\Models\UserSchedule::formatMinutes($et['worked_minutes']) }}</td>
                                    <td class="text-center">{{ \App\Models\UserSchedule::formatMinutes($et['break_minutes']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Detailed schedule table --}}
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Détail ({{ $schedules->count() }} entrées)</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employé</th>
                            <th class="text-center">Début</th>
                            <th class="text-center">Fin</th>
                            <th class="text-center">Amplitude</th>
                            <th class="text-center">Pause</th>
                            <th class="text-center">Durée</th>
                            <th class="text-center text-success">Travaillé</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $s)
                            <tr>
                                <td class="fw-medium">
                                    {{ $s->date->format('d/m') }}
                                    <span class="text-muted small">{{ $s->date->translatedFormat('D') }}</span>
                                </td>
                                <td>{{ $s->user->name ?? '—' }}</td>
                                <td class="text-center">{{ substr($s->start_time, 0, 5) }}</td>
                                <td class="text-center">{{ substr($s->end_time, 0, 5) }}</td>
                                <td class="text-center">{{ $s->total_span_formatted }}</td>
                                <td class="text-center text-muted">{{ $s->break_start ? substr($s->break_start, 0, 5) . '-' . substr($s->break_end, 0, 5) : '—' }}</td>
                                <td class="text-center">{{ $s->break_minutes > 0 ? $s->break_formatted : '—' }}</td>
                                <td class="text-center fw-bold text-success">{{ $s->worked_formatted }}</td>
                                <td class="text-muted small">{{ Str::limit($s->notes, 20) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('backoffice.schedules.edit', $s) }}" class="avtar avtar-xs btn-link-warning" title="Modifier">
                                        <i class="ph-duotone ph-pencil-simple"></i>
                                    </a>
                                    <form action="{{ route('backoffice.schedules.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Supprimer">
                                            <i class="ph-duotone ph-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Aucune entrée pour cette période.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) { new bootstrap.Toast(toastEl).show(); }
        });
    </script>
@endsection
