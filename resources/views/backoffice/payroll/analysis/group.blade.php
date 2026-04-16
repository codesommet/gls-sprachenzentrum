@extends('layouts.main')

@section('title', 'Analyse mensuelle — ' . $group->name)
@section('breadcrumb-item', 'Suivi Paiement')
@section('breadcrumb-item-link', route('backoffice.payroll.dashboard'))
@section('breadcrumb-item-active', 'Analyse mensuelle')

@section('css')
    <style>
        .summary-table th, .summary-table td { text-align: center; vertical-align: middle; }
        .count-initial { color: #0c5460; font-weight: bold; }
        .count-new { color: #155724; font-weight: bold; }
        .count-active { color: #383d41; }
        .count-returned { color: #856404; font-weight: bold; }
        .count-not-yet-paid { color: #e67e22; font-weight: bold; }
        .count-cancelled { color: #721c24; }
        .count-transferred { color: #6c757d; }
        .clickable-cell { cursor: pointer; transition: background-color 0.15s; }
        .clickable-cell:hover { background-color: rgba(0,0,0,0.07); }
    </style>
@endsection

@section('content')

    @if (session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Suivi Paiement</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $group->name }} — Analyse mensuelle</h5>
                            <span class="badge bg-light-primary me-2">{{ $group->level }}</span>
                            <span class="text-muted">
                                Enseignant: <strong>{{ $group->teacher?->name ?? '—' }}</strong>
                                @if($paymentRate)
                                    | Taux: <strong>{{ number_format($paymentRate, 2) }} DH/étudiant</strong>
                                @endif
                            </span>
                            <br>
                            <small class="text-muted">
                                Basé sur import v{{ $import->version }} du {{ $import->created_at->format('d/m/Y') }}
                            </small>
                        </div>
                        <div>
                            <form action="{{ route('backoffice.payroll.group.recalculate', $group) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="ph-duotone ph-arrows-clockwise me-1"></i> Recalculer
                                </button>
                            </form>
                            <a href="{{ route('backoffice.payroll.group.students', $group) }}"
                               class="btn btn-info btn-sm">
                                <i class="ph-duotone ph-users me-1"></i> Détail étudiants
                            </a>
                            <a href="{{ route('backoffice.payroll.group.imports', $group) }}"
                               class="btn btn-outline-secondary btn-sm">Historique</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Summary Table --}}
    @php
        $statusLabels = [
            'initial' => 'Initiaux', 'new' => 'Nouveaux', 'active' => 'Actifs',
            'returned' => 'Retournés', 'not_yet_paid' => 'Pas encore payé',
            'cancelled' => 'Annulés', 'transferred' => 'Archivés',
        ];
        $statusClasses = [
            'initial' => 'count-initial', 'new' => 'count-new', 'active' => 'count-active',
            'returned' => 'count-returned', 'not_yet_paid' => 'count-not-yet-paid',
            'cancelled' => 'count-cancelled', 'transferred' => 'count-transferred',
        ];
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Résumé par mois</h5>
                    <small class="text-muted">Cliquez sur un chiffre pour voir la liste des étudiants</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered summary-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Indicateur</th>
                                    @foreach($summary as $month => $data)
                                        <th>
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y') }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['initial', 'new', 'active', 'returned', 'not_yet_paid', 'cancelled', 'transferred'] as $status)
                                    <tr>
                                        <td class="fw-bold text-start">{{ $statusLabels[$status] }}</td>
                                        @foreach($summary as $month => $data)
                                            @php
                                                $count = $data[$status];
                                                $names = $data['students'][$status] ?? [];
                                            @endphp
                                            @if($count > 0)
                                                @php
                                                    $namesFormatted = collect($names)->map(fn($s) => [
                                                        'name'  => $s['name'],
                                                        'since' => \Carbon\Carbon::createFromFormat('Y-m', $s['since'])->translatedFormat('M Y'),
                                                        'same'  => $s['since'] === $month,
                                                    ])->values()->all();
                                                @endphp
                                                <td class="{{ $statusClasses[$status] }} clickable-cell"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#studentModal"
                                                    data-status="{{ $statusLabels[$status] }}"
                                                    data-month="{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}"
                                                    data-students='@json($namesFormatted)'
                                                    data-count="{{ $count }}">
                                                    {{ $count }}
                                                </td>
                                            @else
                                                <td class="{{ $statusClasses[$status] }}">—</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="table-info">
                                    <td class="fw-bold text-start">Total actifs (payant)</td>
                                    @foreach($summary as $data)
                                        <td class="fw-bold">{{ $data['total_active_students'] }}</td>
                                    @endforeach
                                </tr>
                                <tr class="table-success">
                                    <td class="fw-bold text-start">Total montant (DH)</td>
                                    @foreach($summary as $data)
                                        <td class="fw-bold">{{ number_format($data['total_amount'], 2) }}</td>
                                    @endforeach
                                </tr>
                                @if($paymentRate)
                                    <tr class="table-warning">
                                        <td class="fw-bold text-start">Paiement enseignant (DH)</td>
                                        @foreach($summary as $data)
                                            <td class="fw-bold">
                                                {{ number_format($data['total_active_students'] * $paymentRate, 2) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr class="table-primary">
                                        <td class="fw-bold text-start">Bénéfice net (DH)</td>
                                        @foreach($summary as $data)
                                            @php
                                                $profCost = $data['total_active_students'] * $paymentRate;
                                                $gain = $data['total_amount'] - $profCost;
                                            @endphp
                                            <td class="fw-bold {{ $gain < 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($gain, 2) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Student Detail Modal --}}
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" id="modalSubtitle"></p>
                    <ol class="mb-0" id="modalStudentList"></ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Toast
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();

            // Modal: populate with student names + "depuis" when a cell is clicked
            const modal = document.getElementById('studentModal');
            modal.addEventListener('show.bs.modal', function (event) {
                const cell = event.relatedTarget;
                const status = cell.dataset.status;
                const month = cell.dataset.month;
                const count = cell.dataset.count;

                let students = [];
                try { students = JSON.parse(cell.dataset.students); } catch (e) {}

                document.getElementById('modalTitle').textContent = status + ' — ' + month;
                document.getElementById('modalSubtitle').textContent = count + ' étudiant(s)';

                const list = document.getElementById('modalStudentList');
                list.innerHTML = '';
                students.forEach(function (s) {
                    const li = document.createElement('li');
                    li.className = 'py-1 d-flex justify-content-between align-items-center';

                    let html = '<strong>' + s.name + '</strong>';
                    if (!s.same) {
                        html += ' <span class="badge bg-light text-muted ms-2">depuis ' + s.since + '</span>';
                    }

                    li.innerHTML = html;
                    list.appendChild(li);
                });
            });
        });
    </script>
@endsection
