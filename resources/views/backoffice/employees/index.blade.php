@extends('layouts.main')

@section('title', 'Employés RH')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Employés')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast Notifications --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Maintenant</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') ?? session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-auto">
                    <label class="form-label fw-semibold mb-1"><i class="ph-duotone ph-funnel me-1"></i> Filtres</label>
                </div>
                <div class="col-12 col-sm">
                    <select name="site_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les centres</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les postes</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                @if(request('site_id') || request('role') || request('status'))
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.employees.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Employés</h5>
                        <a href="{{ route('backoffice.employees.create') }}" class="btn btn-primary">
                            <i class="ph-duotone ph-plus me-1"></i> Nouvel employé
                        </a>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Centre</th>
                                    <th>Poste</th>
                                    <th>Contact</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-center">Plannings</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr>
                                        <td class="fw-semibold">{{ $emp->name }}</td>
                                        <td>{{ $emp->site->name }}</td>
                                        <td><span class="badge bg-light-primary">{{ $emp->role }}</span></td>
                                        <td class="text-muted">{{ $emp->phone ?? $emp->email ?? '—' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $emp->is_active ? 'bg-light-success' : 'bg-light-secondary' }}">
                                                {{ $emp->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $emp->schedules_count }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.employees.show', $emp) }}" class="avtar avtar-xs btn-link-primary" title="Voir">
                                                <i class="ph-duotone ph-eye"></i>
                                            </a>
                                            <a href="{{ route('backoffice.employees.edit', $emp) }}" class="avtar avtar-xs btn-link-warning" title="Modifier">
                                                <i class="ph-duotone ph-pencil-simple"></i>
                                            </a>
                                            <form action="{{ route('backoffice.employees.destroy', $emp) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet employé ?')">
                                                @csrf @method('DELETE')
                                                <button class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Supprimer">
                                                    <i class="ph-duotone ph-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Aucun employé trouvé.</td>
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
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) { new bootstrap.Toast(toastEl).show(); }
        });
    </script>
@endsection
