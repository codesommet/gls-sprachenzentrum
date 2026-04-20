@extends('layouts.main')

@section('title', 'Import impayés #' . $import->id)
@section('breadcrumb-item', 'Recouvrement')
@section('breadcrumb-item-active', 'Import #' . $import->id)

@section('content')
    @if (session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header"><strong class="me-auto">Impayés</strong></div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-2">Import impayés #{{ $import->id }}
                        <span class="badge bg-light-{{ $import->status === 'completed' ? 'success' : 'danger' }} ms-2">
                            {{ $import->status === 'completed' ? 'Terminé' : 'Échoué' }}
                        </span>
                    </h5>
                    <div class="row text-muted">
                        <div class="col-auto"><strong>Centre :</strong> {{ $import->site->name ?? '—' }}</div>
                        <div class="col-auto"><strong>Fichier :</strong> {{ $import->file_name }}</div>
                        @if($import->month)
                            <div class="col-auto"><strong>Mois :</strong>
                                <span class="badge bg-primary">{{ \Carbon\Carbon::parse($import->month . '-01')->translatedFormat('F Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.encaissements.impayes.imports.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main KPIs --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-light-danger"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Total Impayés</h6>
                <h3 class="text-danger mb-0">{{ number_format($import->total_amount, 0, ',', ' ') }} <small>DH</small></h3>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-primary"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Nombre d'impayés</h6>
                <h3 class="text-primary mb-0">{{ $import->success_rows }}</h3>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-info"><div class="card-body text-center py-3">
                <h6 class="text-muted mb-1">Moyenne par impayé</h6>
                <h3 class="text-info mb-0">{{ $import->success_rows > 0 ? number_format($import->total_amount / $import->success_rows, 0, ',', ' ') : 0 }} <small>DH</small></h3>
            </div></div>
        </div>
    </div>

    {{-- Reconciliation vs previous import --}}
    @if($import->previous_import_id || $import->new_rows || $import->resolved_rows || $import->kept_rows)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-light-info py-2">
                    <h6 class="mb-0">
                        <i class="ph-duotone ph-git-branch me-1"></i> Comparaison avec l'import précédent
                        @if($import->previousImport)
                            <small class="text-muted">(#{{ $import->previous_import_id }} du {{ $import->previousImport->created_at->format('d/m/Y H:i') }})</small>
                        @endif
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">
                                    <i class="ph-duotone ph-plus-circle me-1 text-warning"></i> Nouveaux impayés
                                </h6>
                                <h4 class="text-warning mb-0">{{ $import->new_rows ?? 0 }}</h4>
                                <small class="text-muted">Absents de l'import précédent</small>
                            </div>
                        </div>
                        <div class="col-md-4 border-start">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">
                                    <i class="ph-duotone ph-check-circle me-1 text-success"></i> Résolus (payés)
                                </h6>
                                <h4 class="text-success mb-0">{{ $import->resolved_rows ?? 0 }}</h4>
                                <small class="text-muted">Étudiants qui ont payé depuis</small>
                            </div>
                        </div>
                        <div class="col-md-4 border-start">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">
                                    <i class="ph-duotone ph-arrow-right me-1 text-info"></i> Encore impayés
                                </h6>
                                <h4 class="text-info mb-0">{{ $import->kept_rows ?? 0 }}</h4>
                                <small class="text-muted">Présents dans les deux imports</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card table-card">
        <div class="card-header"><h5>Liste des impayés</h5></div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Réf.</th>
                            <th>Élève</th>
                            <th>Téléphone</th>
                            <th>Groupe</th>
                            <th>Frais</th>
                            <th class="text-end">Reste à payer</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($impayes as $imp)
                            <tr class="{{ $imp->status === 'recovered' ? 'table-success' : '' }}">
                                <td>{{ $imp->order_number }}</td>
                                <td><span class="text-muted">{{ $imp->reference ?? '—' }}</span></td>
                                <td class="fw-semibold">{{ $imp->student_name }}</td>
                                <td><small>{{ $imp->phone ?? '—' }}</small></td>
                                <td>{{ $imp->group_name ?? '—' }}</td>
                                <td><small>{{ $imp->fee_description ?? '—' }}</small></td>
                                <td class="text-end fw-bold text-danger">{{ number_format($imp->amount_due, 2, ',', ' ') }}</td>
                                <td class="text-center">
                                    @if($imp->status === 'recovered')
                                        <span class="badge bg-success">Recouvré</span>
                                    @elseif($imp->status === 'cancelled')
                                        <span class="badge bg-secondary">Annulé</span>
                                    @else
                                        <span class="badge bg-light-warning">En attente</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($imp->status === 'pending')
                                        <form action="{{ route('backoffice.encaissements.impayes.recover', $imp) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-success" title="Marquer recouvré">
                                                <i class="ph-duotone ph-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Aucun impayé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $impayes->links() }}
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
