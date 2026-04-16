@extends('layouts.main')

@section('title', 'Exportation PDF')
@section('breadcrumb-item', 'RH / Planning')
@section('breadcrumb-item-active', 'Exportation PDF')

@section('content')

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('backoffice.schedules.index') }}" class="btn btn-outline-secondary">
            <i class="ph-duotone ph-arrow-left me-1"></i> Retour au planning
        </a>
        <h5 class="mb-0"><i class="ph-duotone ph-file-pdf text-danger me-1"></i> Exportation PDF Planning</h5>
    </div>

    <div class="row g-4">
        {{-- PDF par employé --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-1"><i class="ph-duotone ph-user me-1"></i> PDF par employé</h5>
                    <p class="text-muted mb-0 small">Planning individuel pour un employé.</p>
                </div>
                <div class="card-body">
                    <form method="GET" id="employeePdfForm">
                        <div class="mb-3">
                            <label class="form-label">Employé</label>
                            <select id="emp_select" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->site->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_to" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="ph-duotone ph-file-pdf me-1"></i> Télécharger PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- PDF par centre --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-1"><i class="ph-duotone ph-buildings me-1"></i> PDF par centre</h5>
                    <p class="text-muted mb-0 small">Planning combiné de tous les employés d'un centre.</p>
                </div>
                <div class="card-body">
                    <form method="GET" id="sitePdfForm">
                        <div class="mb-3">
                            <label class="form-label">Centre</label>
                            <select id="site_select" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_to" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="ph-duotone ph-file-pdf me-1"></i> Télécharger PDF centre
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.getElementById('employeePdfForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const empId = document.getElementById('emp_select').value;
    if (!empId) { alert('Veuillez choisir un employé'); return; }
    const params = new URLSearchParams(new FormData(this));
    window.location.href = '{{ route("backoffice.planning.pdf.employee", ":id") }}'.replace(':id', empId) + '?' + params.toString();
});
document.getElementById('sitePdfForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const siteId = document.getElementById('site_select').value;
    if (!siteId) { alert('Veuillez choisir un centre'); return; }
    const params = new URLSearchParams(new FormData(this));
    window.location.href = '{{ route("backoffice.planning.pdf.site", ":id") }}'.replace(':id', siteId) + '?' + params.toString();
});
</script>
@endsection
