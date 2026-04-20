@extends('layouts.main')

@section('title', 'Mon Planning')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Semaine')

@section('content')

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

    @php
        $prevWeek = (clone $weekStart)->subWeek()->toDateString();
        $nextWeek = (clone $weekStart)->addWeek()->toDateString();
        $isSelf   = $authUser->id === $target->id;
    @endphp

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h5 class="mb-1">
                @if($isSelf)
                    Mon planning
                @else
                    Planning de {{ $target->name }}
                @endif
            </h5>
            <div class="text-muted small">
                Semaine du {{ $weekStart->locale('fr')->isoFormat('DD MMMM') }} au {{ $weekEnd->locale('fr')->isoFormat('DD MMMM YYYY') }}
                @if($target->site)
                    · <span class="badge bg-light-info">{{ $target->site->name }}</span>
                @endif
                @if($target->staff_role)
                    · <span class="badge bg-light-primary">{{ $target->staff_role }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('backoffice.schedules.week', ['week' => $prevWeek, 'user_id' => $target->id]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ph-duotone ph-caret-left"></i> Précédente
            </a>
            <form method="GET" class="d-inline">
                <input type="hidden" name="user_id" value="{{ $target->id }}">
                <input type="date" name="week" class="form-control form-control-sm" value="{{ $weekStart->toDateString() }}" onchange="this.form.submit()" style="width: 160px;">
            </form>
            <a href="{{ route('backoffice.schedules.week', ['week' => $nextWeek, 'user_id' => $target->id]) }}" class="btn btn-outline-secondary btn-sm">
                Suivante <i class="ph-duotone ph-caret-right"></i>
            </a>
        </div>
    </div>

    {{-- Admin-only: switch target user --}}
    @if($isAdmin && $staffOptions->isNotEmpty())
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                    <div class="col-auto">
                        <label class="form-label small mb-1 fw-semibold">
                            <i class="ph-duotone ph-users-three me-1"></i> Gérer le planning de :
                        </label>
                    </div>
                    <div class="col">
                        <select name="user_id" class="form-select" onchange="this.form.submit()">
                            <option value="{{ $authUser->id }}" {{ $target->id === $authUser->id ? 'selected' : '' }}>— Moi-même —</option>
                            @foreach($staffOptions as $u)
                                <option value="{{ $u->id }}" {{ $target->id === $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} · {{ $u->staff_role ?? 'Sans rôle' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('backoffice.schedules.week.save') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ $target->id }}">
        <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;">Jour</th>
                                <th class="text-center" style="width: 120px;">Début</th>
                                <th class="text-center" style="width: 120px;">Fin</th>
                                <th class="text-center" style="width: 120px;">Pause début</th>
                                <th class="text-center" style="width: 120px;">Pause fin</th>
                                <th>Notes</th>
                                <th class="text-end" style="width: 90px;">Travaillé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $i => $day)
                                @php
                                    $s = $day['schedule'];
                                    $isWeekend = in_array($day['date']->dayOfWeek, [0, 6]);
                                @endphp
                                <tr class="{{ $isWeekend ? 'table-light text-muted' : '' }}">
                                    <td class="fw-medium">
                                        {{ $day['label'] }}
                                        <input type="hidden" name="days[{{ $i }}][date]" value="{{ $day['key'] }}">
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][start_time]" class="form-control form-control-sm"
                                               value="{{ old('days.'.$i.'.start_time', $s ? substr($s->start_time, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][end_time]" class="form-control form-control-sm"
                                               value="{{ old('days.'.$i.'.end_time', $s ? substr($s->end_time, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][break_start]" class="form-control form-control-sm"
                                               value="{{ old('days.'.$i.'.break_start', $s && $s->break_start ? substr($s->break_start, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="time" name="days[{{ $i }}][break_end]" class="form-control form-control-sm"
                                               value="{{ old('days.'.$i.'.break_end', $s && $s->break_end ? substr($s->break_end, 0, 5) : '') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="days[{{ $i }}][notes]" class="form-control form-control-sm"
                                               maxlength="500" placeholder="—"
                                               value="{{ old('days.'.$i.'.notes', $s?->notes) }}">
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ $s ? $s->worked_formatted : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="6" class="text-end">Total semaine</th>
                                <th class="text-end text-success">{{ \App\Models\UserSchedule::formatMinutes($totalWorked) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="text-muted small mb-0">
                <i class="ph-duotone ph-info me-1"></i>
                Laissez Début/Fin vide pour supprimer l'entrée d'un jour.
            </p>
            <button type="submit" class="btn btn-primary">
                <i class="ph-duotone ph-check me-1"></i> Enregistrer la semaine
            </button>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) { new bootstrap.Toast(toastEl).show(); }
        });
    </script>
@endsection
