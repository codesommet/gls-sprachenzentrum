@extends('layouts.main')

@section('title', 'Historique des imports')
@section('breadcrumb-item', 'Encaissements')
@section('breadcrumb-item-link', route('backoffice.encaissements.dashboard'))
@section('breadcrumb-item-active', 'Historique imports')

@section('content')

    {{-- Toast --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Encaissements</strong>
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
                    <label class="form-label fw-semibold">
                        <i class="ph-duotone ph-funnel me-1"></i> Filtres
                    </label>
                </div>
                <div class="col-12 col-sm">
                    <select name="site_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les centres</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm">
                    <input type="month" name="month" class="form-control form-control-sm"
                           value="{{ request('month') }}"
                           placeholder="Filtrer par mois"
                           onchange="this.form.submit()">
                </div>
                @if(request('site_id') || request('month'))
                    <div class="col-12 col-sm-auto">
                        <a href="{{ route('backoffice.encaissements.imports.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card table-card">
        <div class="card-header">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h5 class="mb-3 mb-sm-0">Historique des imports</h5>
                <a href="{{ route('backoffice.encaissements.imports.create') }}" class="btn btn-primary btn-sm">
                    <i class="ph-duotone ph-upload me-1"></i> Nouvel import
                </a>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Date import</th>
                            <th>Centre</th>
                            <th>Mois</th>
                            <th>Format CRM</th>
                            <th>Type fichier</th>
                            <th>Fichier</th>
                            <th class="text-center">Lignes</th>
                            <th class="text-end">Montant total</th>
                            <th class="text-center">Statut</th>
                            <th>Importe par</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($imports as $import)
                            <tr>
                                <td class="text-nowrap">{{ $import->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $import->site->name ?? '—' }}</td>
                                <td class="fw-semibold">
                                    @if($import->month)
                                        {{ \Carbon\Carbon::parse($import->month . '-01')->translatedFormat('M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light-{{ $import->source_system === 'old_crm' ? 'warning' : 'info' }}">
                                        {{ $import->source_system === 'old_crm' ? 'Nawaat' : 'Wimsschool' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light-secondary">
                                        {{ strtoupper($import->file_type ?? 'excel') }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted" title="{{ $import->file_name }}">
                                        {{ \Illuminate\Support\Str::limit($import->file_name, 30) }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="text-success" title="Succes">{{ $import->success_rows ?? 0 }}</span>
                                    /
                                    <span class="text-danger" title="Erreurs">{{ $import->error_rows ?? 0 }}</span>
                                    /
                                    <span title="Total">{{ $import->total_rows ?? 0 }}</span>
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($import->total_amount ?? 0, 2, ',', ' ') }} DH
                                </td>
                                <td class="text-center">
                                    @switch($import->status)
                                        @case('completed')
                                            <span class="badge bg-light-success">Termine</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-light-danger">Echoue</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-light-warning">En cours</span>
                                            @break
                                        @default
                                            <span class="badge bg-light-secondary">En attente</span>
                                    @endswitch
                                </td>
                                <td>{{ $import->importedBy->name ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('backoffice.encaissements.imports.show', $import) }}"
                                       class="avtar avtar-xs btn-link-primary" title="Voir">
                                        <i class="ph-duotone ph-eye"></i>
                                    </a>
                                    <form action="{{ route('backoffice.encaissements.imports.destroy', $import) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cet import et tous ses encaissements ?')">
                                        @csrf @method('DELETE')
                                        <button class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Supprimer">
                                            <i class="ph-duotone ph-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">Aucun import trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $imports->links() }}
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) { new bootstrap.Toast(toastEl).show(); }
});
</script>
@endsection
