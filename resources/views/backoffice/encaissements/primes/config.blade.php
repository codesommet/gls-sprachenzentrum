@extends('layouts.main')

@section('title', 'Configuration des primes')
@section('breadcrumb-item', 'Primes')
@section('breadcrumb-item-active', 'Configuration')

@section('content')
    @if(session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast hide"><div class="toast-header"><strong class="me-auto">Configuration</strong></div><div class="toast-body">{{ session('success') }}</div></div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ph-duotone ph-gear me-1"></i> Configuration des primes automatiques</h5>
                        <a href="{{ route('backoffice.encaissements.primes.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('backoffice.encaissements.primes.config.update') }}">
                        @csrf @method('PUT')

                        <h6 class="text-muted mb-3"><i class="ph-duotone ph-calendar me-1"></i> Durée de la prime</h6>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Période par défaut <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                @foreach([1 => '1 mois', 3 => '3 mois', 6 => '6 mois', 12 => '12 mois'] as $val => $label)
                                    <div class="col-md-3">
                                        <input type="radio" class="btn-check" id="period{{ $val }}" name="period_months"
                                               value="{{ $val }}" {{ $config['period_months'] == $val ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary w-100" for="period{{ $val }}">
                                            <i class="ph-duotone ph-calendar-check me-1"></i> {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Durée couverte par la prime (ex: 3 mois = prime qui couvre un trimestre).</small>
                        </div>

                        <hr>

                        <h6 class="text-muted mb-3"><i class="ph-duotone ph-chart-line-up me-1"></i> Règles de calcul</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Seuil d'éligibilité (%) <span class="text-danger">*</span></label>
                                <input type="number" name="threshold_rate" class="form-control"
                                       value="{{ old('threshold_rate', $config['threshold_rate']) }}"
                                       min="0" max="100" required>
                                <small class="text-muted">Taux de recouvrement minimum pour être éligible (ex: 70%).</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Montant par point (DH) <span class="text-danger">*</span></label>
                                <input type="number" name="amount_per_point" class="form-control"
                                       value="{{ old('amount_per_point', $config['amount_per_point']) }}"
                                       min="0" required>
                                <small class="text-muted">DH ajoutés à la prime pour chaque % au-dessus du seuil.</small>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>Exemple :</strong> Seuil 70%, Montant par point 200 DH
                            <br>→ Taux 87% → Prime = (87 - 70) × 200 = <strong>3 400 DH</strong>, répartis entre les employés éligibles
                        </div>

                        <hr>

                        <h6 class="text-muted mb-3"><i class="ph-duotone ph-users me-1"></i> Rôles éligibles</h6>
                        <div class="row g-2">
                            @php $selectedRoles = is_array($config['eligible_roles']) ? $config['eligible_roles'] : explode(',', $config['eligible_roles']); @endphp
                            @foreach(['Administration','Réception','Commercial','Manager','Coordination','Autre'] as $role)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="eligible_roles[]"
                                               value="{{ $role }}" id="role{{ $loop->index }}"
                                               {{ in_array($role, $selectedRoles) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role{{ $loop->index }}">{{ $role }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Seuls les employés avec ces rôles reçoivent une part de la prime.</small>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph-duotone ph-check me-1"></i> Enregistrer la configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
