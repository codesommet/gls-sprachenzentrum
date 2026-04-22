@extends('layouts.main')

@section('title', 'Nouvelle campagne WhatsApp')
@section('breadcrumb-item', 'Communication')
@section('breadcrumb-item-active', 'Nouvelle campagne')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('backoffice.whatsapp_campaigns.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">1. Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom de la campagne *</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name') }}" placeholder="Ex. GLS — Liste annulés / Liste intéressés" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Centre</label>
                            <select name="site_id" class="form-select">
                                <option value="">— Aucun centre —</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ (string) old('site_id', auth()->user()->site_id ?? '') === (string) $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Permet de filtrer et regrouper les campagnes par centre.</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Délai min (s)</label>
                                <input type="number" name="delay_min" class="form-control" value="{{ old('delay_min', 45) }}" min="30">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Délai max (s)</label>
                                <input type="number" name="delay_max" class="form-control" value="{{ old('delay_max', 90) }}" min="40">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Attente chargement (s)</label>
                                <input type="number" name="launch_wait" class="form-control" value="{{ old('launch_wait', 7) }}" min="3" max="30">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Pièce jointe (optionnel)</label>
                            <input type="file" name="attachment" class="form-control"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4">
                            <small class="text-muted">
                                Formats acceptés : PDF, JPG, PNG, WEBP, MP4 (max 20 Mo).
                                Si fourni, le message devient la légende.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">2. Liste des numéros</h5>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Un numéro par ligne. Format: <code>numéro[,nom]</code></label>
                        <textarea name="numbers" id="wa-numbers" class="form-control font-monospace" rows="10" required
                                  placeholder="+212612345678,Nom client&#10;0661234567,Autre client&#10;0612345678">{{ old('numbers') }}</textarea>
                        <div id="wa-numbers-summary" class="mt-2 small text-muted">Chargement…</div>
                        <div id="wa-duplicates-warning" class="alert alert-warning mt-2 small d-none">
                            <strong>⚠ Doublons détectés :</strong>
                            <span id="wa-duplicates-count"></span> numéro(s) ont déjà été contactés dans une campagne précédente.
                            <button type="button" id="wa-show-duplicates" class="btn btn-sm btn-link p-0 ms-2">Afficher</button>
                            <button type="button" id="wa-remove-duplicates" class="btn btn-sm btn-link p-0 ms-2">Retirer automatiquement</button>
                            <div id="wa-duplicates-list" class="mt-2 d-none font-monospace small" style="max-height:140px;overflow:auto"></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">3. Message</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="message" class="form-control" rows="8" required
                                  placeholder="Bonjour {business},&#10;&#10;Nous...">{{ old('message') }}</textarea>
                        <small class="text-muted mt-2 d-block">
                            Variables disponibles : <code>{business}</code>, <code>{name}</code>, <code>{phone}</code>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Enregistrer la campagne</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning small">
                            <strong>⚠ Avertissement :</strong> Utilisez un numéro secondaire.
                            L'envoi massif peut entraîner la suspension de votre compte WhatsApp.
                            WhatsApp Desktop doit être ouvert et connecté sur la machine qui fait tourner le serveur.
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ph-duotone ph-floppy-disk me-1"></i> Créer la campagne
                        </button>
                        <a href="{{ route('backoffice.whatsapp_campaigns.index') }}" class="btn btn-light w-100 mt-2">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
(function(){
    const URL_CONTACTED = @json(route('backoffice.whatsapp_campaigns.contacted_phones'));
    const textarea    = document.getElementById('wa-numbers');
    const summary     = document.getElementById('wa-numbers-summary');
    const warning     = document.getElementById('wa-duplicates-warning');
    const countEl     = document.getElementById('wa-duplicates-count');
    const listEl      = document.getElementById('wa-duplicates-list');
    const btnShow     = document.getElementById('wa-show-duplicates');
    const btnRemove   = document.getElementById('wa-remove-duplicates');

    let contactedSet = new Set();

    function normPhone(p){
        let s = String(p||'').replace(/[\s\-+\(\)]/g,'');
        if (s.length === 10 && /^0[567]/.test(s)) s = '212' + s.slice(1);
        return s;
    }
    function isFax(p){
        const s = String(p||'').replace(/[\s\-+\(\)]/g,'');
        if (/^2125/.test(s)) return true;
        return s.length === 10 && s.startsWith('05');
    }

    function parseLines(){
        const text = textarea.value;
        return text.split(/\r?\n/).map(raw => {
            const t = raw.trim();
            if (!t) return null;
            const parts = t.split(',').map(p=>p.trim());
            return { raw, line: t, phone: parts[0], name: parts[1] || '', normalized: normPhone(parts[0]) };
        }).filter(Boolean);
    }

    function analyze(){
        const rows = parseLines();
        const total = rows.length;
        const duplicates = rows.filter(r => !isFax(r.phone) && contactedSet.has(r.normalized));
        const faxes = rows.filter(r => isFax(r.phone));

        summary.innerHTML = total
            ? `<strong>${total}</strong> numéros`
              + (faxes.length ? ` · <span class="text-warning">${faxes.length} fax/fixe (ignorés)</span>` : '')
              + (duplicates.length ? ` · <span class="text-danger">${duplicates.length} déjà contactés</span>` : '')
            : '<em>Aucun numéro</em>';

        if (duplicates.length) {
            warning.classList.remove('d-none');
            countEl.textContent = duplicates.length;
            listEl.innerHTML = duplicates.map(d=>d.line).join('<br>');
        } else {
            warning.classList.add('d-none');
            listEl.classList.add('d-none');
        }
        return { rows, duplicates, faxes };
    }

    btnShow.addEventListener('click', ()=>{
        listEl.classList.toggle('d-none');
    });
    btnRemove.addEventListener('click', ()=>{
        const { duplicates } = analyze();
        if (!duplicates.length) return;
        const dupSet = new Set(duplicates.map(d=>d.normalized));
        const kept = textarea.value.split(/\r?\n/).filter(line => {
            const t = line.trim(); if (!t) return true;
            const phone = t.split(',')[0].trim();
            return !dupSet.has(normPhone(phone));
        });
        textarea.value = kept.join('\n');
        analyze();
    });

    textarea.addEventListener('input', analyze);

    // Fetch contacted phones then do the first analysis
    fetch(URL_CONTACTED, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => {
            contactedSet = new Set(d.phones || []);
            analyze();
        })
        .catch(()=> analyze());
})();
</script>
@endsection
