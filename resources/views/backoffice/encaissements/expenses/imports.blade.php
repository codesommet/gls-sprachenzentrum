@extends('layouts.main')

@section('title', 'Historique imports dépenses')
@section('breadcrumb-item', 'Charges')
@section('breadcrumb-item-active', 'Historique imports')

@section('content')
    @if (session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header"><strong class="me-auto">Charges</strong></div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto"><label class="form-label fw-semibold"><i class="ph-duotone ph-funnel me-1"></i> Filtres</label></div>
                <div class="col">
                    <select name="site_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tous les centres</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}" onchange="this.form.submit()">
                </div>
                @if(request('site_id') || request('month'))
                    <div class="col-auto">
                        <a href="{{ route('backoffice.encaissements.expenses.imports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ph-duotone ph-x me-1"></i> Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="card-header">
            <div class="d-sm-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historique imports dépenses</h5>
                <a href="{{ route('backoffice.encaissements.expenses.imports.create') }}" class="btn btn-primary btn-sm">
                    <i class="ph-duotone ph-upload me-1"></i> Nouvel import
                </a>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Centre</th>
                            <th>Mois</th>
                            <th>Fichier</th>
                            <th class="text-center">Lignes</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Statut</th>
                            <th>Par</th>
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
                                    @else —
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($import->file_name, 25) }}</small></td>
                                <td class="text-center">{{ $import->success_rows }}/{{ $import->total_rows }}</td>
                                <td class="text-end fw-semibold">{{ number_format($import->total_amount, 2, ',', ' ') }} DH</td>
                                <td class="text-center">
                                    <span class="badge bg-light-{{ $import->status === 'completed' ? 'success' : ($import->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ $import->status === 'completed' ? 'Terminé' : ($import->status === 'failed' ? 'Échoué' : 'En cours') }}
                                    </span>
                                </td>
                                <td>{{ $import->importedBy->name ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('backoffice.encaissements.expenses.imports.show', $import) }}" class="avtar avtar-xs btn-link-primary" title="Voir"><i class="ph-duotone ph-eye"></i></a>
                                    <form action="{{ route('backoffice.encaissements.expenses.imports.destroy', $import) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer?')">
                                        @csrf @method('DELETE')
                                        <button class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Supprimer"><i class="ph-duotone ph-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Aucun import trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $imports->links() }}
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const t = document.getElementById('liveToast');
    if (t) new bootstrap.Toast(t).show();
});
</script>
@endsection
