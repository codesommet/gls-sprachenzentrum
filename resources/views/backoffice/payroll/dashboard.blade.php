@extends('layouts.main')

@section('title', 'Suivi Paiement — Tableau de bord')
@section('breadcrumb-item', 'Suivi Paiement')
@section('breadcrumb-item-active', 'Tableau de bord')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Suivi Paiement</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Groupes avec imports</h6>
                    <h3 class="mb-0">{{ $groups->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total imports</h6>
                    <h3 class="mb-0">{{ $groups->sum(fn($g) => $g->imports->count()) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Dernier import</h6>
                    <h3 class="mb-0">
                        @php
                            $lastImport = $groups->pluck('latestImport')->filter()->sortByDesc('created_at')->first();
                        @endphp
                        {{ $lastImport ? $lastImport->created_at->format('d/m/Y') : '—' }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-end">
                    <a href="{{ route('backoffice.payroll.import.create') }}" class="btn btn-primary">
                        <i class="ph-duotone ph-upload-simple me-1"></i> Nouvel import
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Groups Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <h5>Groupes suivis</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th>Enseignant</th>
                                    <th>Niveau</th>
                                    <th>Taux/Etudiant</th>
                                    <th>Versions</th>
                                    <th>Dernier import</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $group)
                                    @php
                                        $latest = $group->latestImport;
                                        $rate = $latest?->getEffectivePaymentPerStudent();
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $group->name }}</strong>
                                            <br><small class="text-muted">{{ $group->time_range }}</small>
                                        </td>
                                        <td>{{ $group->teacher?->name ?? '—' }}</td>
                                        <td><span class="badge bg-light-primary">{{ $group->level }}</span></td>
                                        <td>{{ $rate ? number_format($rate, 2) . ' DH' : '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">v{{ $latest?->version ?? 0 }}</span>
                                        </td>
                                        <td>{{ $latest?->created_at->format('d/m/Y H:i') ?? '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.payroll.group.imports', $group) }}"
                                               class="btn btn-sm btn-outline-primary" title="Historique">
                                                <i class="ph-duotone ph-clock-counter-clockwise"></i>
                                            </a>
                                            <a href="{{ route('backoffice.payroll.group.analysis', $group) }}"
                                               class="btn btn-sm btn-outline-success" title="Analyse mensuelle">
                                                <i class="ph-duotone ph-chart-bar"></i>
                                            </a>
                                            <a href="{{ route('backoffice.payroll.group.students', $group) }}"
                                               class="btn btn-sm btn-outline-info" title="Suivi étudiants">
                                                <i class="ph-duotone ph-users"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Aucun import pour le moment.
                                            <a href="{{ route('backoffice.payroll.import.create') }}">Importer un fichier CRM</a>
                                        </td>
                                    </tr>
                                @endforelse
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
        window.dt = new DataTable("#pc-dt-simple");
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
