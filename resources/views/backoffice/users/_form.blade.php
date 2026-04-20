<div class="row">

    {{-- ─── Compte ─────────────────────────────────────────────── --}}
    <div class="col-12 mb-2">
        <h6 class="text-uppercase text-muted small fw-semibold mb-0">
            <i class="ph-duotone ph-user me-1"></i> Compte
        </h6>
        <hr class="mt-1 mb-3">
    </div>

    {{-- NAME --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom complet <span class="text-danger">*</span></label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $user->name ?? '') }}"
               placeholder="Nom de l'utilisateur"
               required>
    </div>

    {{-- EMAIL --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
        <input type="email" name="email"
               class="form-control"
               value="{{ old('email', $user->email ?? '') }}"
               placeholder="email@exemple.com"
               required>
    </div>

    {{-- PASSWORD --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">
            Mot de passe
            @if(isset($user))
                <small class="text-muted">(laisser vide pour ne pas changer)</small>
            @endif
        </label>
        <input type="password" name="password"
               class="form-control"
               placeholder="Mot de passe"
               {{ isset($user) ? '' : 'required' }}>
    </div>

    {{-- PASSWORD CONFIRMATION --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation"
               class="form-control"
               placeholder="Confirmer le mot de passe"
               {{ isset($user) ? '' : 'required' }}>
    </div>

    {{-- ROLE (application role) --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Rôle application <span class="text-danger">*</span></label>
        <select name="role" class="form-select" required>
            <option value="">-- Sélectionner un rôle --</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}"
                    {{ old('role', isset($user) ? $user->roles->first()?->name : '') === $role->name ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Détermine les permissions dans le backoffice.</small>
    </div>

    {{-- ACTIVE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold d-block">Statut</label>
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_switch"
                {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active_switch">Compte actif</label>
        </div>
    </div>

    {{-- ─── Fiche employé ──────────────────────────────────────── --}}
    <div class="col-12 mb-2 mt-3">
        <h6 class="text-uppercase text-muted small fw-semibold mb-0">
            <i class="ph-duotone ph-identification-badge me-1"></i> Fiche employé
        </h6>
        <hr class="mt-1 mb-3">
        <p class="text-muted small mb-0">
            Renseignez ces informations si l'utilisateur travaille dans un centre GLS.
            Elles alimentent le planning, les primes et les exports RH.
        </p>
    </div>

    {{-- SITE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Centre affecté</label>
        <select name="site_id" class="form-select">
            <option value="">— Aucun —</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ (string) old('site_id', $user->site_id ?? '') === (string) $site->id ? 'selected' : '' }}>
                    {{ $site->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- STAFF ROLE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Poste</label>
        <select name="staff_role" class="form-select">
            <option value="">— Non défini —</option>
            @foreach($staffRoles as $r)
                <option value="{{ $r }}"
                    {{ old('staff_role', $user->staff_role ?? '') === $r ? 'selected' : '' }}>
                    {{ $r }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- HIRED AT --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Date d'embauche</label>
        <input type="date" name="hired_at" class="form-control"
               value="{{ old('hired_at', isset($user) && $user->hired_at ? $user->hired_at->format('Y-m-d') : '') }}">
    </div>

    {{-- PHONE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Téléphone</label>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $user->phone ?? '') }}"
               placeholder="+212 6…">
    </div>

    {{-- NOTES --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Notes internes</label>
        <input type="text" name="staff_notes" class="form-control"
               value="{{ old('staff_notes', $user->staff_notes ?? '') }}"
               maxlength="2000"
               placeholder="Remarques RH (optionnel)">
    </div>

</div>
