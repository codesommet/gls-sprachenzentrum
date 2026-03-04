@extends('layouts.main')

@section('title', 'Gestion des Applications')
@section('breadcrumb-item', 'Applications')
@section('breadcrumb-item-active', 'Liste')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
@endsection

@php
    $appsCollection = collect($applications);
    $approvedCount = $appsCollection->where('status', 'approved')->count();
    $pendingCount  = $appsCollection->where('status', 'pending')->count();
    $rejectedCount = $appsCollection->where('status', 'rejected')->count();
    $syncedCount   = $appsCollection->whereNotNull('google_sheet_synced_at')->count();
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

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total</p>
                                    <h4 class="mb-0">{{ $appsCollection->count() }}</h4>
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
                                    <p class="text-muted mb-1">En attente</p>
                                    <h4 class="mb-0">{{ $pendingCount }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-warning rounded-circle">
                                    <i class="ti ti-clock f-24"></i>
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
                                    <p class="text-muted mb-1">Approuvées</p>
                                    <h4 class="mb-0">{{ $approvedCount }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-success rounded-circle">
                                    <i class="ti ti-check f-24"></i>
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
                                    <p class="text-muted mb-1">Sync Google Sheets</p>
                                    <h4 class="mb-0">{{ $syncedCount }}/{{ $appsCollection->count() }}</h4>
                                </div>
                                <span class="avtar avtar-l bg-light-info rounded-circle">
                                    <i class="ti ti-cloud-upload f-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('backoffice.applications.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Centre</label>
                            <select name="center" class="form-select form-select-sm">
                                <option value="">Tous les centres</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" @selected(request('center') == $site->id)>{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                                <option value="approved" @selected(request('status') === 'approved')>Approuvé</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Rejeté</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                            <a href="{{ route('backoffice.applications.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Applications</h5>
                        <a href="{{ route('backoffice.applications.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Nouvelle Application
                        </a>
                    </div>
                </div>

                <div class="card-body pt-3">
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
                                @forelse($applications as $app)
                                    <tr>
                                        <td>{{ $app->id }}</td>
                                        <td>{{ $app->full_name }}</td>
                                        <td>{{ $app->whatsapp_number }}</td>
                                        <td>{{ $app->email }}</td>
                                        <td>{{ $app->group?->site?->name ?? '-' }}</td>
                                        <td>{{ $app->group?->name ?? '-' }}</td>
                                        <td>
                                            @if($app->status === 'approved')
                                                <span class="badge bg-light-success">Approuvé</span>
                                            @elseif($app->status === 'rejected')
                                                <span class="badge bg-light-danger">Rejeté</span>
                                            @else
                                                <span class="badge bg-light-warning">En attente</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($app->isSyncedToSheet())
                                                <span class="badge bg-light-success" title="Sync le {{ $app->google_sheet_synced_at->format('d/m/Y H:i') }}">
                                                    <i class="ti ti-check"></i>
                                                </span>
                                            @else
                                                <span class="badge bg-light-secondary" title="Non synchronisé">
                                                    <i class="ti ti-cloud-off"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $app->created_at?->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.applications.show', $app) }}" class="avtar avtar-s btn-link-primary btn-pc-default border-0" title="Voir">
                                                <i class="ti ti-eye f-20"></i>
                                            </a>
                                            <a href="{{ route('backoffice.applications.edit', $app) }}" class="avtar avtar-s btn-link-warning btn-pc-default border-0" title="Modifier">
                                                <i class="ti ti-edit f-20"></i>
                                            </a>
                                            <form action="{{ route('backoffice.applications.destroy', $app) }}" method="POST" class="d-inline"
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
                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/simple-datatables.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('pc-dt-simple');
            if (el) new simpleDatatables.DataTable(el);

            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
