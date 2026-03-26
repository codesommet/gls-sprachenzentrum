@extends('layouts.main')

@section('title', 'Suivi niveau')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Suivi niveau')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .level-stepper {
            --circle-size: 44px;
            --track-size: 10px;
            --track-color: #e4e4e4;
            --fill-color: #2f7ed8;
            --done-color: #09b10f;
            --current-color: #2f7ed8;
            position: relative;
            padding-top: 8px;
        }

        .level-stepper__track,
        .level-stepper__fill {
            position: absolute;
            top: calc((var(--circle-size) / 2) - (var(--track-size) / 2) + 8px);
            height: var(--track-size);
            border-radius: 999px;
        }

        .level-stepper__track {
            left: calc(var(--circle-size) / 2);
            right: calc(var(--circle-size) / 2);
            background: var(--track-color);
        }

        .level-stepper__fill {
            left: calc(var(--circle-size) / 2 + ((100% - var(--circle-size)) * var(--level-fill-start) / 100));
            width: calc((100% - var(--circle-size)) * var(--level-fill-width) / 100);
            background: var(--fill-color);
        }

        .level-stepper__steps {
            position: relative;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0;
        }

        .level-stepper__step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .level-stepper__circle {
            width: var(--circle-size);
            height: var(--circle-size);
            border-radius: 50%;
            border: 4px solid #8d8d8d;
            background: #fff;
            position: relative;
            box-shadow: 0 0 0 4px #fff;
        }

        .level-stepper__label {
            margin-top: 12px;
            font-size: 1rem;
            font-weight: 700;
            color: #121212;
            line-height: 1.2;
        }

        .level-stepper__step--done .level-stepper__circle {
            background: var(--done-color);
            border-color: var(--done-color);
        }

        .level-stepper__step--done .level-stepper__circle::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 13px;
            height: 7px;
            border-left: 4px solid #fff;
            border-bottom: 4px solid #fff;
            transform: translate(-50%, -65%) rotate(-45deg);
        }

        .level-stepper__step--done .level-stepper__label {
            color: var(--done-color);
        }

        .level-stepper__step--current .level-stepper__label {
            color: var(--current-color);
        }

        .level-stepper__step--inactive .level-stepper__circle {
            border-color: #cfcfcf;
            background: #f7f7f7;
        }

        .level-stepper__step--inactive .level-stepper__label {
            color: #999;
        }

        .level-stepper__meta {
            margin-top: 10px;
        }
    </style>
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
                                        $stepCount = count($order);
                                        $fillStartPercent = $stepCount > 1 ? ($startIndex / ($stepCount - 1)) * 100 : 0;
                                        $fillWidthPercent = (($stepCount > 1 ? (100 - $fillStartPercent) : 0) * $percent) / 100;

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
                                        <td style="min-width:320px;">
                                            <div class="level-stepper"
                                                 style="--level-fill-start: {{ $fillStartPercent }}; --level-fill-width: {{ $fillWidthPercent }};">
                                                <div class="level-stepper__track" aria-hidden="true"></div>
                                                <div class="level-stepper__fill" aria-hidden="true"></div>

                                                <div class="level-stepper__steps">
                                                    @foreach($order as $idx => $lvl)
                                                        @php
                                                            $inactive = $idx < $startIndex;
                                                            $state = $inactive ? 'inactive' : ($levelState[$lvl] ?? 'pending');
                                                        @endphp
                                                        <div class="level-stepper__step level-stepper__step--{{ $state }}">
                                                            <span class="level-stepper__circle" aria-hidden="true"></span>
                                                            <span class="level-stepper__label">{{ $lvl }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="level-stepper__meta text-muted text-sm">
                                                    Niveau actuel : <strong>{{ $currentLevel ?? $followup->level }}</strong>
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
                                                <div class="d-flex justify-content-end gap-2 mb-2">
                                                    <a href="{{ route('backoffice.level_followups.group_pdf', $followup->group_id) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Exporter PDF du groupe">
                                                        <i class="ph-duotone ph-file-pdf"></i>
                                                    </a>
                                                    <form method="POST"
                                                          action="{{ route('backoffice.level_followups.destroy', $followup) }}"
                                                          onsubmit="return confirm('Supprimer ce suivi niveau ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Supprimer">
                                                            <i class="ph-duotone ph-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="text-muted text-sm">
                                                    Terminé le {{ $followup->done_at ? \Carbon\Carbon::parse($followup->done_at)->format('d/m/Y H:i') : '-' }}
                                                </div>
                                            @else
                                                <form method="POST"
                                                      action="{{ route('backoffice.level_followups.complete', $followup) }}">
                                                    @csrf
                                                    <div class="d-flex justify-content-end gap-2 mb-2">
                                                        <a href="{{ route('backoffice.level_followups.group_pdf', $followup->group_id) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Exporter PDF du groupe">
                                                            <i class="ph-duotone ph-file-pdf"></i>
                                                        </a>
                                                        <button type="submit"
                                                                form="delete-followup-{{ $followup->id }}"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Supprimer">
                                                            <i class="ph-duotone ph-trash"></i>
                                                        </button>
                                                    </div>
                                                    <textarea name="done_notes"
                                                              class="form-control form-control-sm"
                                                              rows="2"
                                                              placeholder="Notes (facultatif)"></textarea>
                                                    <button type="submit" class="btn btn-success btn-sm mt-2">
                                                        Marquer terminé
                                                    </button>
                                                </form>
                                                <form id="delete-followup-{{ $followup->id }}"
                                                      method="POST"
                                                      action="{{ route('backoffice.level_followups.destroy', $followup) }}"
                                                      class="d-none"
                                                      onsubmit="return confirm('Supprimer ce suivi niveau ?')">
                                                    @csrf
                                                    @method('DELETE')
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
