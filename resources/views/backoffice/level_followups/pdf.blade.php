<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi niveau</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { font-size: 11px; color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 10px; }
        .ok { background: #d1fae5; color: #065f46; }
        .warn { background: #fef3c7; color: #92400e; }
        .danger { background: #fee2e2; color: #991b1b; }
        .muted { color: #777; }
    </style>
</head>
<body>
    <h1>Suivi niveau — Rapport prof</h1>
    <div class="meta">Date d’export: {{ $now->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Groupe</th>
                <th>Prof</th>
                <th>Niveau actuel</th>
                <th>Échéance</th>
                <th>Statut</th>
                <th>Progression</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $order = ['A1', 'A2', 'B1', 'B2'];
                    $groupItems = $levelFollowupsByGroup[$row->group_id] ?? collect();

                    $doneCount = $groupItems->where('status', 'done')->count();
                    $total = max(1, $groupItems->count());
                    $progress = (int) round(($doneCount / $total) * 100);

                    $isDue = ($row->status === 'pending') && $row->due_date && \Carbon\Carbon::parse($row->due_date)->lte($now);
                @endphp
                <tr>
                    <td>{{ $row->group?->name ?? '-' }}</td>
                    <td>{{ $row->group?->teacher?->name ?? '-' }}</td>
                    <td>{{ $row->level }}</td>
                    <td>{{ $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d/m/Y') : '-' }}</td>
                    <td>
                        @if($row->status === 'done')
                            <span class="badge ok">Terminé</span>
                        @elseif($isDue)
                            <span class="badge danger">Dû</span>
                        @else
                            <span class="badge warn">En attente</span>
                        @endif
                    </td>
                    <td>{{ $progress }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">Aucune donnée de suivi niveau.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

