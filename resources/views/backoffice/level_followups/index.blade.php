@extends('layouts.main')

@section('title', 'Suivi niveau')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Suivi niveau')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Suivi niveau (rappels profs)</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light-primary">{{ $dueFollowups->count() }} rappel(s) dû(s)</span>
                        <a href="{{ route('backoffice.level_followups.pdf') }}" class="btn btn-sm btn-primary">
                            Exporter PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($followups->isEmpty())
                        <div class="alert alert-info mb-0">
                            Aucun suivi niveau généré. Lance: <code>php artisan gls:generate-level-followups</code>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Prof</th>
                                    <th>Niveaux</th>
                                    <th>Échéance</th>
                                    <th>Statut</th>
                                    <th class="text-end">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($followups as $followup)
                                    @php
                                        $order = ['A1', 'A2', 'B1', 'B2'];
                                        $startLevel = $followup->group?->level;
                                        $startIndex = is_string($startLevel) ? array_search($startLevel, $order, true) : 0;
                                        $startIndex = ($startIndex === false) ? 0 : $startIndex;

                                        $allForGroup = $levelFollowupsByGroup[$followup->group_id] ?? collect();
                                        $activeSegments = $allForGroup
                                            ->whereIn('level', array_slice($order, $startIndex))
                                            ->sortBy('level_start_date')
                                            ->values();

                                        $totalDays = 0;
                                        $elapsedDays = 0;
                                        $currentLevel = null;

                                        foreach ($activeSegments as $seg) {
                                            $segStart = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
                                            $segEnd = $seg->level_end_date ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay() : null;
                                            if (!$segStart || !$segEnd || $segEnd->lt($segStart)) continue;

                                            $segDays = $segStart->diffInDays($segEnd) + 1;
                                            $totalDays += $segDays;

                                            if ($now->lt($segStart)) {
                                                // future segment => 0 elapsed
                                                continue;
                                            }

                                            if ($now->gt($segEnd)) {
                                                // past segment => fully elapsed
                                                $elapsedDays += $segDays;
                                                continue;
                                            }

                                            // current segment
                                            $currentLevel = $seg->level;
                                            $elapsedDays += $segStart->diffInDays($now) + 1;
                                        }

                                        $percent = $totalDays > 0 ? (int) round(($elapsedDays / $totalDays) * 100) : 0;

                                        // Determine visual state for each level circle based on dates (not manual done)
                                        $levelState = [];
                                        foreach ($order as $lvl) {
                                            $levelState[$lvl] = 'inactive';
                                        }
                                        foreach ($activeSegments as $seg) {
                                            $lvl = $seg->level;
                                            $segStart = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
                                            $segEnd = $seg->level_end_date ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay() : null;
                                            if (!$segStart || !$segEnd) continue;

                                            if ($now->gt($segEnd)) $levelState[$lvl] = 'done';
                                            elseif ($now->betweenIncluded($segStart, $segEnd)) $levelState[$lvl] = 'current';
                                            else $levelState[$lvl] = 'pending';
                                        }

                                        $isDue = ($followup->status === 'pending') && $followup->due_date && \Carbon\Carbon::parse($followup->due_date)->lte($now);
                                    @endphp

                                    <tr>
                                        <td class="fw-semibold">{{ $followup->group?->name }}</td>
                                        <td>{{ $followup->group?->teacher?->name ?? '-' }}</td>
                                        <td style="min-width:260px;">
                                            <div class="d-flex flex-column gap-2">
                                                <div class="progress" style="height:6px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                         style="width: {{ $percent }}%;">
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    @foreach($order as $idx => $lvl)
                                                        @php
                                                            $inactive = $idx < $startIndex;
                                                            $state = $inactive ? 'inactive' : ($levelState[$lvl] ?? 'pending');
                                                            $circleClass = match ($state) {
                                                                'inactive' => 'bg-light-secondary',
                                                                'done' => 'bg-light-success',
                                                                'current' => 'bg-light-primary',
                                                                default => 'bg-light-warning',
                                                            };
                                                        @endphp
                                                        <span class="avtar rounded-circle {{ $circleClass }}"
                                                              style="min-width:44px;text-align:center;">
                                                            {{ $lvl }}
                                                        </span>
                                                    @endforeach
                                                </div>

                                                <div class="text-muted text-sm">
                                                    Niveau : <strong>{{ $currentLevel ?? $followup->level }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $followup->due_date ? \Carbon\Carbon::parse($followup->due_date)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>
                                            @if($followup->status === 'done')
                                                <span class="badge bg-light-success">Terminé</span>
                                            @else
                                                <span class="badge {{ $isDue ? 'bg-light-danger' : 'bg-light-warning' }}">
                                                    {{ $isDue ? 'Dû' : 'En attente' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end" style="min-width:260px;">
                                            @if($followup->status === 'done')
                                                <a href="{{ route('backoffice.level_followups.group_pdf', $followup->group_id) }}"
                                                   class="btn btn-sm btn-outline-primary mb-2"
                                                   title="Exporter PDF du groupe">
                                                    <i class="ph-duotone ph-file-pdf"></i>
                                                </a>
                                                <div class="text-muted text-sm">
                                                    Terminé le {{ $followup->done_at ? \Carbon\Carbon::parse($followup->done_at)->format('d/m/Y H:i') : '-' }}
                                                </div>
                                            @else
                                                <form method="POST"
                                                      action="{{ route('backoffice.level_followups.complete', $followup) }}">
                                                    @csrf
                                                    <div class="text-end mb-2">
                                                        <a href="{{ route('backoffice.level_followups.group_pdf', $followup->group_id) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Exporter PDF du groupe">
                                                            <i class="ph-duotone ph-file-pdf"></i>
                                                        </a>
                                                    </div>
                                                    <textarea name="done_notes"
                                                              class="form-control form-control-sm"
                                                              rows="2"
                                                              placeholder="Notes (facultatif)"></textarea>
                                                    <button type="submit" class="btn btn-success btn-sm mt-2">
                                                        Marquer terminé
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

