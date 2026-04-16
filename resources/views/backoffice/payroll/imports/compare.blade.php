@extends('layouts.main')

@section('title', 'Comparaison v' . $previousImport->version . ' → v' . $import->version)
@section('breadcrumb-item', 'Suivi Paiement')
@section('breadcrumb-item-link', route('backoffice.payroll.dashboard'))
@section('breadcrumb-item-active', 'Comparaison imports')

@php
    $statusLabels = ['active' => 'Actif', 'cancelled' => 'Annulé', 'transferred' => 'Archivé'];
@endphp

@section('css')
    <style>
        .change-added { background-color: #d4edda; }
        .change-removed { background-color: #f8d7da; }
        .change-modified { background-color: #fff3cd; }
        .movement-table th, .movement-table td { text-align: center; vertical-align: middle; }
        .count-initial { color: #0c5460; font-weight: bold; }
        .count-new { color: #155724; font-weight: bold; }
        .count-active { color: #383d41; }
        .count-lost { color: #721c24; font-weight: bold; }
        .count-returned { color: #856404; font-weight: bold; }
        .count-cancelled { color: #721c24; }
        .count-transferred { color: #6c757d; }
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
                            <h5 class="mb-1">
                                {{ $group->name }} — Comparaison
                                <span class="badge bg-secondary">v{{ $previousImport->version }}</span>
                                <i class="ph-duotone ph-arrow-right mx-2"></i>
                                <span class="badge bg-primary">v{{ $import->version }}</span>
                            </h5>
                            <span class="text-muted">
                                {{ $previousImport->created_at->format('d/m/Y') }}
                                → {{ $import->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                        <a href="{{ route('backoffice.payroll.group.imports', $group) }}"
                           class="btn btn-outline-secondary btn-sm">Retour historique</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 1: Mouvement des étudiants (lifecycle from latest import) --}}
    {{-- ============================================================ --}}
    @if(!empty($summary))
        @php
            // Count UNIQUE students per lifecycle category (not summed across months)
            $lifecycleEntries = $import->lifecycleEntries;
            $totalInitial  = $lifecycleEntries->where('status', 'initial')->pluck('group_import_student_id')->unique()->count();
            $totalNew      = $lifecycleEntries->where('status', 'new')->pluck('group_import_student_id')->unique()->count();
            $totalLost     = $lifecycleEntries->where('status', 'lost')->pluck('group_import_student_id')->unique()->count();
            $totalReturned = $lifecycleEntries->where('status', 'returned')->pluck('group_import_student_id')->unique()->count();
            $totalCancelled = $import->students->where('status', 'cancelled')->count();
            $totalArchived  = $import->students->where('status', 'transferred')->count();
        @endphp

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Mouvement des étudiants (v{{ $import->version }})</h5>
                <small class="text-muted">Analyse basée sur les paiements mensuels — depuis le début du groupe</small>
            </div>
            <div class="card-body">

                {{-- Movement summary cards --}}
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card border-info">
                            <div class="card-body text-center py-2">
                                <small class="text-muted">Initiaux</small>
                                <h4 class="count-initial mb-0">{{ $totalInitial }}</h4>
                                <small>depuis le début</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-success">
                            <div class="card-body text-center py-2">
                                <small class="text-muted">Ajoutés</small>
                                <h4 class="text-success mb-0">+{{ $totalNew }}</h4>
                                <small>après le début</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-danger">
                            <div class="card-body text-center py-2">
                                <small class="text-muted">Perdus</small>
                                <h4 class="text-danger mb-0">{{ $totalLost }}</h4>
                                <small>ont arrêté</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-warning">
                            <div class="card-body text-center py-2">
                                <small class="text-muted">Retournés</small>
                                <h4 class="text-warning mb-0">{{ $totalReturned }}</h4>
                                <small>sont revenus</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-danger">
                            <div class="card-body text-center py-2">
                                <small class="text-muted">Annulés</small>
                                <h4 class="count-cancelled mb-0">{{ $totalCancelled }}</h4>
                                <small>inscription annulée</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card" style="border-color: #6c757d">
                            <div class="card-body text-center py-2">
                                <small class="text-muted">Archivés</small>
                                <h4 class="count-transferred mb-0">{{ $totalArchived }}</h4>
                                <small>déplacés</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Monthly movement table --}}
                <div class="table-responsive">
                    <table class="table table-bordered movement-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Indicateur</th>
                                @foreach($summary as $month => $data)
                                    <th>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-start">Initiaux</td>
                                @foreach($summary as $data)
                                    <td class="count-initial">{{ $data['initial'] ?: '—' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold text-start">Nouveaux (ajoutés)</td>
                                @foreach($summary as $data)
                                    <td class="count-new">{{ $data['new'] ?: '—' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold text-start">Actifs</td>
                                @foreach($summary as $data)
                                    <td class="count-active">{{ $data['active'] ?: '—' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold text-start">Perdus (arrêtés)</td>
                                @foreach($summary as $data)
                                    <td class="count-lost">{{ $data['lost'] ?: '—' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold text-start">Retournés (rappelés)</td>
                                @foreach($summary as $data)
                                    <td class="count-returned">{{ $data['returned'] ?: '—' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold text-start">Annulés</td>
                                @foreach($summary as $data)
                                    <td class="count-cancelled">{{ $data['cancelled'] ?: '—' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold text-start">Archivés</td>
                                @foreach($summary as $data)
                                    <td class="count-transferred">{{ $data['transferred'] ?: '—' }}</td>
                                @endforeach
                            </tr>
                            <tr class="table-info">
                                <td class="fw-bold text-start">Total payant</td>
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
                                        <td class="fw-bold">{{ number_format($data['total_active_students'] * $paymentRate, 2) }}</td>
                                    @endforeach
                                </tr>
                                <tr class="table-primary">
                                    <td class="fw-bold text-start">Bénéfice net (DH)</td>
                                    @foreach($summary as $data)
                                        @php $gain = $data['total_amount'] - ($data['total_active_students'] * $paymentRate); @endphp
                                        <td class="fw-bold {{ $gain < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($gain, 2) }}</td>
                                    @endforeach
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SECTION 2: Différences entre imports v(N-1) et v(N)         --}}
    {{-- ============================================================ --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Différences entre v{{ $previousImport->version }} et v{{ $import->version }}</h5>
            <small class="text-muted">Comparaison des fichiers CRM importés</small>
        </div>
        <div class="card-body">

            {{-- Diff summary cards --}}
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center py-2">
                            <small class="text-muted">Avant</small>
                            <h4 class="mb-0">{{ $comparison['summary']['total_old'] }}</h4>
                            <small>étudiants</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center py-2">
                            <small class="text-muted">Après</small>
                            <h4 class="mb-0">{{ $comparison['summary']['total_new'] }}</h4>
                            <small>étudiants</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-success">
                        <div class="card-body text-center py-2">
                            <small class="text-success">Ajoutés</small>
                            <h4 class="text-success mb-0">+{{ $comparison['summary']['added_count'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-danger">
                        <div class="card-body text-center py-2">
                            <small class="text-danger">Retirés</small>
                            <h4 class="text-danger mb-0">-{{ $comparison['summary']['removed_count'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-warning">
                        <div class="card-body text-center py-2">
                            <small class="text-warning">Paiements modifiés</small>
                            <h4 class="text-warning mb-0">{{ $comparison['summary']['payment_changes'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-info">
                        <div class="card-body text-center py-2">
                            <small class="text-info">Statuts changés</small>
                            <h4 class="text-info mb-0">{{ $comparison['summary']['status_changes'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Added Students --}}
            @if(count($comparison['added_students']) > 0)
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-success">
                            <i class="ph-duotone ph-user-plus me-1"></i>
                            Etudiants ajoutés ({{ count($comparison['added_students']) }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Etudiant</th><th>Paiements</th></tr></thead>
                            <tbody>
                                @foreach($comparison['added_students'] as $student)
                                    <tr class="change-added">
                                        <td><strong>{{ $student['student_name'] }}</strong></td>
                                        <td>
                                            @foreach($student['payments'] as $month => $amount)
                                                <span class="badge bg-light text-dark me-1">{{ $month }}: {{ number_format($amount, 2) }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Removed Students --}}
            @if(count($comparison['removed_students']) > 0)
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-danger">
                            <i class="ph-duotone ph-user-minus me-1"></i>
                            Etudiants retirés ({{ count($comparison['removed_students']) }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Etudiant</th><th>Derniers paiements</th></tr></thead>
                            <tbody>
                                @foreach($comparison['removed_students'] as $student)
                                    <tr class="change-removed">
                                        <td><strong>{{ $student['student_name'] }}</strong></td>
                                        <td>
                                            @foreach($student['payments'] as $month => $amount)
                                                <span class="badge bg-light text-dark me-1">{{ $month }}: {{ number_format($amount, 2) }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Payment Changes --}}
            @if(count($comparison['payment_changes']) > 0)
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-warning">
                            <i class="ph-duotone ph-currency-circle-dollar me-1"></i>
                            Paiements modifiés ({{ count($comparison['payment_changes']) }} étudiants)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Etudiant</th><th>Mois</th><th>Avant</th><th>Après</th><th>Type</th></tr></thead>
                            <tbody>
                                @foreach($comparison['payment_changes'] as $change)
                                    @foreach($change['changes'] as $mc)
                                        <tr class="change-modified">
                                            <td><strong>{{ $change['student_name'] }}</strong></td>
                                            <td>{{ $mc['month'] }}</td>
                                            <td>{{ $mc['old_amount'] !== null ? number_format($mc['old_amount'], 2) : '—' }}</td>
                                            <td>{{ $mc['new_amount'] !== null ? number_format($mc['new_amount'], 2) : '—' }}</td>
                                            <td>
                                                @switch($mc['type'])
                                                    @case('payment_added') <span class="badge bg-success">Ajouté</span> @break
                                                    @case('payment_removed') <span class="badge bg-danger">Retiré</span> @break
                                                    @case('new_month') <span class="badge bg-info">Nouveau mois</span> @break
                                                    @case('amount_changed') <span class="badge bg-warning">Modifié</span> @break
                                                    @default <span class="badge bg-secondary">{{ $mc['type'] }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Status Changes --}}
            @if(count($comparison['status_changes']) > 0)
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-info">
                            <i class="ph-duotone ph-swap me-1"></i>
                            Changements de statut ({{ count($comparison['status_changes']) }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Etudiant</th><th>Ancien statut</th><th></th><th>Nouveau statut</th></tr></thead>
                            <tbody>
                                @foreach($comparison['status_changes'] as $change)
                                    <tr>
                                        <td><strong>{{ $change['student_name'] }}</strong></td>
                                        <td><span class="badge bg-secondary">{{ $statusLabels[$change['old_status']] ?? $change['old_status'] }}</span></td>
                                        <td><i class="ph-duotone ph-arrow-right"></i></td>
                                        <td><span class="badge bg-primary">{{ $statusLabels[$change['new_status']] ?? $change['new_status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- No diff changes --}}
            @if(
                count($comparison['added_students']) === 0 &&
                count($comparison['removed_students']) === 0 &&
                count($comparison['payment_changes']) === 0 &&
                count($comparison['status_changes']) === 0
            )
                <div class="text-center py-4">
                    <i class="ph-duotone ph-check-circle text-success" style="font-size: 2rem"></i>
                    <p class="mt-2 text-muted mb-0">Aucune différence entre les deux fichiers importés.</p>
                </div>
            @endif

        </div>
    </div>

@endsection
