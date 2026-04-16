@extends('layouts.main')

@section('title', 'Historique imports — ' . $group->name)
@section('breadcrumb-item', 'Suivi Paiement')
@section('breadcrumb-item-link', route('backoffice.payroll.dashboard'))
@section('breadcrumb-item-active', 'Historique imports')

@section('content')

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

    <div class="row">
        <div class="col-12">

            {{-- Group Info Header --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $group->name }}</h5>
                            <span class="badge bg-light-primary me-2">{{ $group->level }}</span>
                            <span class="text-muted">Enseignant: <strong>{{ $group->teacher?->name ?? '—' }}</strong></span>
                        </div>
                        <div>
                            <a href="{{ route('backoffice.payroll.import.create', ['group_id' => $group->id]) }}"
                               class="btn btn-primary btn-sm">
                                <i class="ph-duotone ph-upload-simple me-1"></i> Nouvel import
                            </a>
                            <a href="{{ route('backoffice.payroll.group.analysis', $group) }}"
                               class="btn btn-success btn-sm">
                                <i class="ph-duotone ph-chart-bar me-1"></i> Analyse
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Import History Table --}}
            <div class="card table-card">
                <div class="card-header">
                    <h5>Historique des imports</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Fichier</th>
                                    <th>Mois début</th>
                                    <th>Taux/Etudiant</th>
                                    <th>Etudiants</th>
                                    <th>Date import</th>
                                    <th>Importé par</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($imports as $import)
                                    <tr>
                                        <td><span class="badge bg-primary">v{{ $import->version }}</span></td>
                                        <td>{{ $import->file_name }}</td>
                                        <td>{{ $import->start_month->format('m/Y') }}</td>
                                        <td>
                                            @php $rate = $import->getEffectivePaymentPerStudent(); @endphp
                                            {{ $rate ? number_format($rate, 2) . ' DH' : '—' }}
                                        </td>
                                        <td>{{ $import->students_count }}</td>
                                        <td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $import->importedBy?->name ?? '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.payroll.import.show', ['group' => $group->id, 'import' => $import->id]) }}"
                                               class="btn btn-sm btn-outline-primary" title="Détails">
                                                <i class="ph-duotone ph-eye"></i>
                                            </a>
                                            @if($import->version > 1)
                                                <a href="{{ route('backoffice.payroll.import.compare', ['group' => $group->id, 'import' => $import->id]) }}"
                                                   class="btn btn-sm btn-outline-warning" title="Comparer">
                                                    <i class="ph-duotone ph-arrows-left-right"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('backoffice.payroll.import.destroy', $import) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cet import ?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="ph-duotone ph-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Aucun import.</td>
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
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) new bootstrap.Toast(toastEl).show();
        });
    </script>
@endsection
