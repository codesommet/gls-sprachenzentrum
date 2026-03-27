<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi niveau groupe</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin: 0; font-size: 20px; }
        .header-container { text-align: center; margin-bottom: 15px; }
        .logo { max-width: 140px; margin-bottom: 8px; }
        h2 { margin: 0 0 6px; font-size: 15px; }
        .meta { color: #6b7280; font-size: 11px; margin-top: 4px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; margin: 14px 0; }
        .grid { width: 100%; }
        .grid td { padding: 4px 8px 4px 0; vertical-align: top; }
        .label { color: #6b7280; width: 130px; }
        .value { font-weight: 600; }
        .progress-wrap { margin-top: 10px; width: 100%; text-align: center; }
        .progress-outer { width: 100%; height: 6px; background: #e5e7eb; border-radius: 99px; margin: 0 auto 10px auto; }
        .progress-inner { height: 6px; background: #10b981; border-radius: 99px; }
        .levels-container { display: block; width: 100%; text-align: center; }
        .level-circle { display: inline-block; width: 34px; height: 34px; line-height: 34px; border-radius: 50%; text-align: center; font-size: 11px; font-weight: bold; margin: 0 6px; }
        .circle-done { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .circle-current { background: #dbeafe; color: #1d4ed8; border: 1px solid #3b82f6; }
        .circle-pending { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
        .circle-inactive { background: #f3f4f6; color: #9ca3af; border: 1px solid #d1d5db; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-size: 11px; text-transform: uppercase; color: #374151; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 10px; }
        .ok { background: #d1fae5; color: #065f46; }
        .active { background: #dbeafe; color: #1d4ed8; }
        .warn { background: #fef3c7; color: #92400e; }
        .danger { background: #fee2e2; color: #991b1b; }
        .muted { color: #9ca3af; }
    </style>
</head>
<body>
@php
    $formationStart = $group->date_debut ? \Carbon\Carbon::parse($group->date_debut) : null;
    $formationEnd = $group->date_fin ? \Carbon\Carbon::parse($group->date_fin) : null;

    // Count only weekdays (Mon-Fri)
    $countWeekdays = function($from, $to) {
        $count = 0;
        $cur = $from->copy();
        while ($cur->lte($to)) {
            if (!$cur->isWeekend()) {
                $count++;
            }
            $cur->addDay();
        }
        return $count;
    };

    $totalDays = 0;
    $elapsedDays = 0;

    foreach ($followups as $seg) {
        $segStart = $seg->level_start_date ? \Carbon\Carbon::parse($seg->level_start_date)->startOfDay() : null;
        $segEnd = $seg->level_end_date ? \Carbon\Carbon::parse($seg->level_end_date)->startOfDay() : null;
        if (!$segStart || !$segEnd || $segEnd->lt($segStart)) continue;

        $segDays = $countWeekdays($segStart, $segEnd);
        $totalDays += $segDays;

        if ($now->lt($segStart)) {
            continue;
        }

        if ($now->gt($segEnd)) {
            $elapsedDays += $segDays;
            continue;
        }

        $elapsedDays += $countWeekdays($segStart, $now);
    }

    $progress = $totalDays > 0 ? (int) round(($elapsedDays / $totalDays) * 100) : 0;
@endphp

<div class="header-container">
    <img src="{{ public_path('assets/images/logo/gls.png') }}" class="logo" alt="GLS">
    <h1>Rapport Suivi Niveau</h1>
    <div class="meta">Date d’export: {{ $now->format('d/m/Y H:i') }}</div>
</div>

<div class="card">
    <h2>Informations du groupe</h2>
    <table class="grid">
        <tr>
            <td class="label">Groupe</td>
            <td class="value">{{ $group->name ?? '-' }}</td>
            <td class="label">Prof</td>
            <td class="value">{{ $group->teacher?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Centre</td>
            <td class="value">{{ $group->site?->name ?? '-' }}</td>
            <td class="label">Date début formation</td>
            <td class="value">{{ $formationStart ? $formationStart->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date fin formation</td>
            <td class="value">{{ $formationEnd ? $formationEnd->format('d/m/Y') : '-' }}</td>
            <td class="label">Niveau de départ</td>
            <td class="value">{{ $group->level ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Progression</td>
            <td class="value" colspan="3">{{ $progress }}%</td>
        </tr>
    </table>
    <div class="progress-wrap">
        <div class="progress-outer">
            <div class="progress-inner" style="width: {{ $progress }}%;"></div>
        </div>
    </div>
</div>

<h2>Planning des niveaux</h2>
<table>
    <thead>
    <tr>
        <th>Niveau</th>
        <th>Début niveau</th>
        <th>Date terminé</th>
        <th>Statut</th>
        <th>Notes</th>
    </tr>
    </thead>
    <tbody>
    @forelse($followups as $f)
        @php
            $start = $f->level_start_date ? \Carbon\Carbon::parse($f->level_start_date) : null;
            $end = $f->level_end_date ? \Carbon\Carbon::parse($f->level_end_date) : null;
            $finishedAt = $f->done_at ? \Carbon\Carbon::parse($f->done_at) : null;
            $isCurrent = $start && $end && $now->betweenIncluded($start, $end) && $f->status !== 'done';
        @endphp
        @php
            $daysLeft = ($isCurrent && $end) ? (int) $now->diffInDays($end, false) : null;
            $isUrgent = $daysLeft !== null && $daysLeft <= 14 && $daysLeft >= 0;
            $isOverdue = $f->status !== 'done' && $end && $now->gt($end->copy()->endOfDay());
        @endphp
        <tr>
            <td><strong>{{ $f->level }}</strong></td>
            <td>{{ $start ? $start->format('d/m/Y') : '-' }}</td>
            <td>
                @if($finishedAt)
                    <span style="color:#065f46;">Termine le {{ $finishedAt->format('d/m/Y') }}</span>
                @elseif($isOverdue)
                    <span style="color:#991b1b;">En retard!</span>
                @elseif($isUrgent)
                    <span style="color:#92400e;">{{ $daysLeft }} jours restants</span>
                @else
                    {{ $end ? $end->format('d/m/Y') : '-' }}
                @endif
            </td>
            <td>
                @if($f->status === 'done')
                    <span class="badge ok">Termine</span>
                @elseif($isOverdue)
                    <span class="badge danger">En retard</span>
                @elseif($isCurrent)
                    <span class="badge active">En cours</span>
                @else
                    <span class="badge warn">En attente</span>
                @endif
            </td>
            <td>{{ $f->done_notes ?: '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="muted">Aucun suivi niveau trouvé pour ce groupe.</td>
        </tr>
    @endforelse
    </tbody>
</table>
</body>
</html>

