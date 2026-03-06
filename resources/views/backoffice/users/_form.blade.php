<div class="row">

    {{-- NAME --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom complet</label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $user->name ?? '') }}"
               placeholder="Nom de l'utilisateur"
               required>
    </div>

    {{-- EMAIL --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Email</label>
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

</div>
