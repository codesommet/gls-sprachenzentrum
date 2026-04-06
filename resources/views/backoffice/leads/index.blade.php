@extends('layouts.main')

@section('title', 'Gestion des Leads')
@section('breadcrumb-item', 'Leads')
@section('breadcrumb-item-active', 'Liste')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/apexcharts.css') }}">
@endsection

@php
    $totalLeads = $consultations->count() + $inscriptions->count() + $applications->count();
@endphp

@section('content')

    {{-- Toast Notifications --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon"
                        style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">

            {{-- Centre Filter --}}
            <div class="card mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('backoffice.leads.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <label class="fw-semibold mb-0"><i class="ti ti-filter me-1"></i> Filtrer par centre :</label>
                        <select name="centre" class="form-select" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
                            <option value="all" {{ $centreFilter === 'all' ? 'selected' : '' }}>
                                Tous les centres
                            </option>
                            <option value="online" {{ $centreFilter === 'online' ? 'selected' : '' }}>
                                En ligne ({{ $centreCounts[0] ?? 0 }})
                            </option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ $centreFilter == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }} ({{ $centreCounts[$site->id] ?? 0 }})
                                </option>
                            @endforeach
                        </select>
                        @if($centreFilter !== 'all')
                            <a href="{{ route('backoffice.leads.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ti ti-x me-1"></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Leads</p>
                                    <h4 class="mb-0">{{ $totalLeads }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-primary rounded-circle">
                                    <i class="ti ti-users f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Consultations</p>
                                    <h4 class="mb-0">{{ $consultations->count() }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-info rounded-circle">
                                    <i class="ti ti-messages f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Inscriptions</p>
                                    <h4 class="mb-0">{{ $inscriptions->count() }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-success rounded-circle">
                                    <i class="ti ti-clipboard-list f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Applications</p>
                                    <h4 class="mb-0">{{ $applications->count() }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-warning rounded-circle">
                                    <i class="ti ti-file-description f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monthly Leads Chart --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-chart-bar me-1"></i> Statistiques des Leads par Mois
                        @if($centreFilter !== 'all')
                            <span class="badge bg-primary ms-2">
                                {{ $centreFilter === 'online' ? 'En ligne' : $sites->firstWhere('id', $centreFilter)?->name }}
                            </span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div id="leads-monthly-chart" style="min-height: 350px;"></div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="leadsTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'consultations' ? 'active' : '' }}"
                               href="{{ route('backoffice.leads.index', ['tab' => 'consultations']) }}">
                                <i class="ti ti-messages me-1"></i> Consultations
                                <span class="badge bg-info ms-1">{{ $consultations->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'inscriptions' ? 'active' : '' }}"
                               href="{{ route('backoffice.leads.index', ['tab' => 'inscriptions']) }}">
                                <i class="ti ti-clipboard-list me-1"></i> Inscriptions
                                <span class="badge bg-success ms-1">{{ $inscriptions->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'applications' ? 'active' : '' }}"
                               href="{{ route('backoffice.leads.index', ['tab' => 'applications']) }}">
                                <i class="ti ti-file-description me-1"></i> Applications
                                <span class="badge bg-warning ms-1">{{ $applications->count() }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body pt-3">

                    {{-- CONSULTATIONS TAB --}}
                    @if($tab === 'consultations')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Ville</th>
                                        <th>Sync</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($consultations as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td class="fw-semibold">{{ $item->name }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>{{ $item->city ?? '-' }}</td>
                                            <td>
                                                @if($item->isSyncedToSheet())
                                                    <span class="badge bg-light-success" title="Sync le {{ $item->google_sheet_synced_at->format('d/m/Y H:i') }}">
                                                        <i class="ti ti-check"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-secondary" title="Non synchronisé">
                                                        <i class="ti ti-cloud-off"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('backoffice.leads.consultation.destroy', $item) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Supprimer cette consultation ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">
                                                <div class="alert alert-info mb-0">Aucune consultation trouvée.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- INSCRIPTIONS TAB --}}
                    @if($tab === 'inscriptions')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Niveau</th>
                                        <th>Type</th>
                                        <th>Centre</th>
                                        <th>Sync</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inscriptions as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td class="fw-semibold">{{ $item->nom }}</td>
                                            <td>{{ $item->prenom }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->phone }}</td>
                                            <td>
                                                <span class="badge bg-light-primary">{{ $item->niveau ?? '-' }}</span>
                                            </td>
                                            <td>{{ $item->type_cours ?? '-' }}</td>
                                            <td>{{ $item->site?->name ?? '-' }}</td>
                                            <td>
                                                @if($item->isSyncedToSheet())
                                                    <span class="badge bg-light-success" title="Sync le {{ $item->google_sheet_synced_at->format('d/m/Y H:i') }}">
                                                        <i class="ti ti-check"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-secondary" title="Non synchronisé">
                                                        <i class="ti ti-cloud-off"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('backoffice.leads.inscription.destroy', $item) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Supprimer cette inscription ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11">
                                                <div class="alert alert-info mb-0">Aucune inscription trouvée.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- APPLICATIONS TAB --}}
                    @if($tab === 'applications')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom</th>
                                        <th>WhatsApp</th>
                                        <th>Email</th>
                                        <th>Centre</th>
                                        <th>Groupe</th>
                                        <th>Statut</th>
                                        <th>Sync</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td class="fw-semibold">{{ $item->full_name }}</td>
                                            <td>{{ $item->whatsapp_number }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->group?->site?->name ?? '-' }}</td>
                                            <td>{{ $item->group?->name ?? '-' }}</td>
                                            <td>
                                                @if($item->status === 'approved')
                                                    <span class="badge bg-light-success">Approuvé</span>
                                                @elseif($item->status === 'rejected')
                                                    <span class="badge bg-light-danger">Rejeté</span>
                                                @else
                                                    <span class="badge bg-light-warning">En attente</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->isSyncedToSheet())
                                                    <span class="badge bg-light-success" title="Sync le {{ $item->google_sheet_synced_at->format('d/m/Y H:i') }}">
                                                        <i class="ti ti-check"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-secondary" title="Non synchronisé">
                                                        <i class="ti ti-cloud-off"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('backoffice.leads.application.destroy', $item) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Supprimer cette application ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="avtar avtar-s btn-link-danger btn-pc-default border-0" title="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10">
                                                <div class="alert alert-info mb-0">Aucune application trouvée.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/simple-datatables.js') }}"></script>
    <script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('pc-dt-simple');
            if (el) new simpleDatatables.DataTable(el);

            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();

            // Monthly Leads Chart
            var chartEl = document.getElementById('leads-monthly-chart');
            if (chartEl && typeof ApexCharts !== 'undefined') {
                var monthlyData = @json($monthlyStats);
                var options = {
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: { show: true },
                        stacked: false,
                    },
                    series: [
                        {
                            name: 'Inscriptions',
                            data: monthlyData.map(function(m) { return m.inscriptions; }),
                            color: '#2ca87f',
                        },
                        {
                            name: 'Consultations',
                            data: monthlyData.map(function(m) { return m.consultations; }),
                            color: '#3ec9d6',
                        },
                        {
                            name: 'Applications',
                            data: monthlyData.map(function(m) { return m.applications; }),
                            color: '#e58a00',
                        }
                    ],
                    xaxis: {
                        categories: monthlyData.map(function(m) { return m.label; }),
                    },
                    yaxis: {
                        title: { text: 'Nombre de Leads' },
                        forceNiceScale: true,
                        min: 0,
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '50%',
                            borderRadius: 4,
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '12px' },
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                    },
                };
                new ApexCharts(chartEl, options).render();
            }
        });
    </script>
@endsection
