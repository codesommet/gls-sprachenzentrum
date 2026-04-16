@extends('layouts.main')

@section('title', 'Suivi étudiants — ' . $group->name)
@section('breadcrumb-item', 'Suivi Paiement')
@section('breadcrumb-item-link', route('backoffice.payroll.dashboard'))
@section('breadcrumb-item-active', 'Suivi étudiants')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .lifecycle-table { font-size: 0.85rem; }
        .lifecycle-table th, .lifecycle-table td { text-align: center; padding: 4px 8px; }
        .lc-initial { background-color: #d1ecf1; }
        .lc-new { background-color: #d4edda; }
        .lc-active { background-color: #e8f5e9; }
        .lc-lost { background-color: #f8d7da; }
        .lc-returned { background-color: #fff3cd; }
        .lc-cancelled { background-color: #f5c6cb; }
        .lc-transferred { background-color: #d6d8db; }
        .lc-inactive { background-color: #f8f9fa; color: #adb5bd; }
        .student-name-col { min-width: 180px; text-align: left !important; }
    </style>
@endsection

@section('content')

    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $group->name }} — Cycle de vie des étudiants</h5>
                            <span class="badge bg-light-primary me-2">{{ $group->level }}</span>
                            <span class="text-muted">
                                Enseignant: <strong>{{ $group->teacher?->name ?? '—' }}</strong>
                                | Import v{{ $import->version }}
                                | {{ $students->count() }} étudiants
                            </span>
                        </div>
                        <div>
                            <a href="{{ route('backoffice.payroll.group.analysis', $group) }}"
                               class="btn btn-success btn-sm">
                                <i class="ph-duotone ph-chart-bar me-1"></i> Analyse mensuelle
                            </a>
                            <a href="{{ route('backoffice.payroll.group.imports', $group) }}"
                               class="btn btn-outline-secondary btn-sm">Historique</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Student Timeline --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Timeline des étudiants</h5>
                        <small class="text-muted">
                            Chaque cellule: montant payé + statut lifecycle
                        </small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm lifecycle-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="student-name-col">Etudiant</th>
                                    <th>Statut</th>
                                    <th>1er paiement</th>
                                    @foreach($months as $month)
                                        <th>
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('M \'y') }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    @php
                                        $paymentsByMonth = $student->payments->keyBy(fn($p) => $p->month->format('Y-m'));
                                        $lifecycleByMonth = $student->lifecycleEntries->keyBy(fn($e) => $e->month->format('Y-m'));
                                        $firstPaid = $student->payments->where('amount', '>', 0)->sortBy('month')->first();
                                    @endphp
                                    <tr>
                                        <td class="student-name-col">
                                            <strong>{{ $student->student_name }}</strong>
                                        </td>
                                        <td>
                                            @switch($student->status)
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Annulé</span>
                                                    @break
                                                @case('transferred')
                                                    <span class="badge bg-secondary">Archivé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ ucfirst($student->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            {{ $firstPaid ? $firstPaid->month->format('m/Y') : '—' }}
                                        </td>
                                        @foreach($months as $month)
                                            @php
                                                $payment = $paymentsByMonth[$month] ?? null;
                                                $lifecycle = $lifecycleByMonth[$month] ?? null;
                                                $amount = $payment ? (float)$payment->amount : 0;
                                                $status = $lifecycle?->status ?? 'inactive';
                                            @endphp
                                            <td class="lc-{{ $status }}" title="{{ ucfirst($status) }}: {{ number_format($amount, 2) }} DH">
                                                @if($amount > 0)
                                                    {{ number_format($amount, 0) }}
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">
        import { DataTable } from "/build/js/plugins/module.js";
    </script>
@endsection
