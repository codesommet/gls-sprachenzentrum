@extends('layouts.main')

@section('title', 'Détails Application')
@section('breadcrumb-item', 'Applications')
@section('breadcrumb-item-link', route('backoffice.applications.index'))
@section('breadcrumb-item-active', 'Détails')

@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
<link rel="stylesheet" href="{{ URL::asset('build/css/plugins/animate.min.css') }}">
@endsection

@section('content')

    {{-- Toast --}}
    @if (session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">

            {{-- Applicant Info --}}
            <div class="card animate__animated animate__fadeIn">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Application #{{ $application->id }}</h5>
                    <div>
                        <a href="{{ route('backoffice.applications.edit', $application) }}" class="btn btn-sm btn-warning">
                            <i class="ti ti-edit me-1"></i>Modifier
                        </a>
                        <a href="{{ route('backoffice.applications.index') }}" class="btn btn-sm btn-secondary">Retour</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">Nom complet</label>
                            <strong>{{ $application->full_name }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">Email</label>
                            <strong>{{ $application->email }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">WhatsApp</label>
                            <strong>{{ $application->whatsapp_number }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">Date de naissance</label>
                            <strong>{{ $application->birthday?->format('d/m/Y') ?? '-' }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">Groupe</label>
                            <strong>{{ $application->group?->name ?? $application->group?->name_fr ?? '-' }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">Centre</label>
                            <strong>{{ $application->group?->site?->name ?? '-' }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">Niveau</label>
                            <strong>{{ $application->group?->level ?? '-' }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block">Statut</label>
                            @if($application->status === 'approved')
                                <span class="badge bg-light-success f-14">Approuvé</span>
                            @elseif($application->status === 'rejected')
                                <span class="badge bg-light-danger f-14">Rejeté</span>
                            @else
                                <span class="badge bg-light-warning f-14">En attente</span>
                            @endif
                        </div>
                    </div>

                    @if($application->note)
                        <div class="mt-2">
                            <label class="text-muted d-block">Notes</label>
                            <p>{{ $application->note }}</p>
                        </div>
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted d-block">Créé le</label>
                            <strong>{{ $application->created_at?->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted d-block">Mis à jour le</label>
                            <strong>{{ $application->updated_at?->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Google Sheets Sync Panel --}}
        <div class="col-md-4">
            <div class="card animate__animated animate__fadeIn">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-cloud me-2"></i>Google Sheets</h5>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="text-muted d-block">Statut sync</label>
                        @if($application->isSyncedToSheet())
                            <span class="badge bg-success">Synchronisé</span>
                        @else
                            <span class="badge bg-secondary">Non synchronisé</span>
                        @endif
                    </div>

                    @if($application->google_sheet_name)
                        <div class="mb-3">
                            <label class="text-muted d-block">Feuille</label>
                            <strong>{{ $application->google_sheet_name }}</strong>
                        </div>
                    @endif

                    @if($application->google_sheet_row)
                        <div class="mb-3">
                            <label class="text-muted d-block">Ligne</label>
                            <strong>#{{ $application->google_sheet_row }}</strong>
                        </div>
                    @endif

                    @if($application->google_sheet_synced_at)
                        <div class="mb-3">
                            <label class="text-muted d-block">Sync le</label>
                            <strong>{{ $application->google_sheet_synced_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="text-muted d-block">Sync confirmation</label>
                        @if($application->isSyncedToConfirmedSheet())
                            <span class="badge bg-success">Oui</span>
                            <small class="text-muted d-block mt-1">
                                {{ $application->google_sheet_confirmed_synced_at->format('d/m/Y H:i') }}
                            </small>
                        @else
                            <span class="badge bg-secondary">Non</span>
                        @endif
                    </div>

                    <hr>
                    <form action="{{ route('backoffice.applications.resync', $application) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                            <i class="ti ti-refresh me-1"></i>Re-synchroniser
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('liveToast');
        if (toastEl) new bootstrap.Toast(toastEl).show();
    });
</script>
@endsection
