<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planning - {{ $employee->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 15px; }
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #1e3a5f; }
        .header img { height: 45px; margin-bottom: 8px; }
        .header h1 { font-size: 16px; color: #1e3a5f; margin: 0; }
        .header h2 { font-size: 12px; color: #666; margin: 4px 0 0; font-weight: normal; }
        .info td { padding: 2px 8px; font-size: 10px; }
        .info .lbl { font-weight: bold; color: #666; width: 100px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background: #1e3a5f; color: white; padding: 5px 6px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.data td { border: 1px solid #e2e8f0; padding: 4px 6px; }
        table.data tr:nth-child(even) { background: #f8fafc; }
        .total { background: #eff6ff; font-weight: bold; }
        .footer { margin-top: 15px; text-align: center; font-size: 8px; color: #aaa; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .green { color: #059669; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
        <h1>Planning Employé</h1>
        <h2>{{ $employee->name }} ({{ $employee->staff_role ?? '—' }}) — {{ $site->name ?? '—' }}</h2>
    </div>

    <table class="info" style="margin-bottom: 10px;">
        <tr><td class="lbl">Employé :</td><td>{{ $employee->name }}</td><td class="lbl">Centre :</td><td>{{ $site->name }}</td></tr>
        <tr><td class="lbl">Poste :</td><td>{{ $employee->staff_role ?? '—' }}</td><td class="lbl">Période :</td><td>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</td></tr>
    </table>

    <table class="data">
        <thead><tr>
            <th>Date</th><th>Jour</th><th>Début</th><th>Fin</th><th>Amplitude</th><th>Pause</th><th>Durée pause</th><th>Travaillé</th><th>Notes</th>
        </tr></thead>
        <tbody>
            @forelse($schedules as $s)
            <tr>
                <td class="bold">{{ $s->date->format('d/m/Y') }}</td>
                <td>{{ $s->date->translatedFormat('l') }}</td>
                <td>{{ substr($s->start_time, 0, 5) }}</td>
                <td>{{ substr($s->end_time, 0, 5) }}</td>
                <td class="text-center">{{ $s->total_span_formatted }}</td>
                <td>{{ $s->break_start ? substr($s->break_start, 0, 5) . ' - ' . substr($s->break_end, 0, 5) : '—' }}</td>
                <td class="text-center">{{ $s->break_minutes > 0 ? $s->break_formatted : '—' }}</td>
                <td class="text-center bold green">{{ $s->worked_formatted }}</td>
                <td>{{ $s->notes ?? '' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center" style="padding:20px;color:#999;">Aucune entrée pour cette période.</td></tr>
            @endforelse
            @if($schedules->isNotEmpty())
            <tr class="total">
                <td colspan="4" class="bold">TOTAL — {{ $schedules->count() }} jours</td>
                <td class="text-center">{{ \App\Models\UserSchedule::formatMinutes($schedules->sum('total_span_minutes')) }}</td>
                <td></td>
                <td class="text-center">{{ \App\Models\UserSchedule::formatMinutes($totalBreak) }}</td>
                <td class="text-center bold green">{{ \App\Models\UserSchedule::formatMinutes($totalWorked) }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">GLS Sprachzentrum — Planning généré le {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>
