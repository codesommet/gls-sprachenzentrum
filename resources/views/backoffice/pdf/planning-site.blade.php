<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planning Centre - {{ $site->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 15px; }
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #1e3a5f; }
        .header img { height: 45px; margin-bottom: 8px; }
        .header h1 { font-size: 16px; color: #1e3a5f; margin: 0; }
        .header h2 { font-size: 12px; color: #666; margin: 4px 0 0; font-weight: normal; }
        .emp-header { background: #1e3a5f; color: white; padding: 6px 10px; font-size: 12px; font-weight: bold; margin-top: 15px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #f1f5f9; padding: 4px 6px; text-align: left; font-size: 9px; text-transform: uppercase; color: #475569; border: 1px solid #e2e8f0; }
        table.data td { border: 1px solid #e2e8f0; padding: 3px 6px; }
        table.data tr:nth-child(even) { background: #f8fafc; }
        .total { background: #eff6ff; font-weight: bold; }
        .footer { margin-top: 15px; text-align: center; font-size: 8px; color: #aaa; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .green { color: #059669; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @foreach($employeePlannings as $index => $planning)
        @if($index > 0)<div class="page-break"></div>@endif

        <div class="header">
            <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
            <h1>Planning Centre — {{ $site->name }}</h1>
            <h2>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</h2>
        </div>

        <div class="emp-header">{{ $planning['employee']->name }} — {{ $planning['employee']->staff_role ?? '—' }}</div>
        <table class="data">
            <thead><tr>
                <th>Date</th><th>Jour</th><th>Début</th><th>Fin</th><th>Amplitude</th><th>Pause</th><th>Durée</th><th>Travaillé</th><th>Notes</th>
            </tr></thead>
            <tbody>
                @foreach($planning['schedules'] as $s)
                <tr>
                    <td class="bold">{{ $s->date->format('d/m/Y') }}</td>
                    <td>{{ $s->date->translatedFormat('l') }}</td>
                    <td>{{ substr($s->start_time, 0, 5) }}</td>
                    <td>{{ substr($s->end_time, 0, 5) }}</td>
                    <td class="text-center">{{ $s->total_span_formatted }}</td>
                    <td>{{ $s->break_start ? substr($s->break_start, 0, 5) . '-' . substr($s->break_end, 0, 5) : '—' }}</td>
                    <td class="text-center">{{ $s->break_minutes > 0 ? $s->break_formatted : '—' }}</td>
                    <td class="text-center bold green">{{ $s->worked_formatted }}</td>
                    <td>{{ $s->notes ?? '' }}</td>
                </tr>
                @endforeach
                <tr class="total">
                    <td colspan="4">TOTAL — {{ $planning['schedules']->count() }} jours</td>
                    <td class="text-center">{{ \App\Models\UserSchedule::formatMinutes($planning['schedules']->sum('total_span_minutes')) }}</td>
                    <td></td>
                    <td class="text-center">{{ \App\Models\UserSchedule::formatMinutes($planning['totalBreak']) }}</td>
                    <td class="text-center bold green">{{ \App\Models\UserSchedule::formatMinutes($planning['totalWorked']) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">GLS Sprachzentrum — Planning généré le {{ now()->format('d/m/Y à H:i') }}</div>
    @endforeach

    @if(empty($employeePlannings))
        <div class="header">
            <img src="{{ public_path('assets/images/logo/gls.png') }}" alt="GLS">
            <h1>Planning Centre — {{ $site->name }}</h1>
        </div>
        <p style="text-align:center;color:#999;padding:40px;">Aucune entrée de planning pour cette période.</p>
    @endif
</body>
</html>
