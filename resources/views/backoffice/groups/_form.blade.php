<div class="row">

    {{-- NOM DU GROUPE (DEFAULT / BACKUP) --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Nom du groupe (Fallback)</label>
        <input type="text" name="name"
               class="form-control"
               value="{{ old('name', $group->name ?? '') }}"
               placeholder="Ex: Groupe A1 Intensif">
        <small class="text-muted">Utilisé si les versions FR/EN sont vides.</small>
    </div>

    {{-- NOM FR --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du groupe (FR)</label>
        <input type="text" name="name_fr"
               class="form-control"
               value="{{ old('name_fr', $group->name_fr ?? '') }}"
               placeholder="Ex: Groupe A1 Intensif">
    </div>

    {{-- NOM EN --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Nom du groupe (EN)</label>
        <input type="text" name="name_en"
               class="form-control"
               value="{{ old('name_en', $group->name_en ?? '') }}"
               placeholder="Ex: A1 Intensive Group">
    </div>

    {{-- SITE --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Centre GLS</label>
        <select name="site_id" class="form-select" required>
            <option value="">Sélectionner un centre</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}"
                    {{ old('site_id', $group->site_id ?? '') == $site->id ? 'selected' : '' }}>
                    {{ $site->name }} ({{ $site->city }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- ENSEIGNANT (OPTIONNEL) --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Enseignant</label>
        <select name="teacher_id" class="form-select">
            <option value="">(Non défini pour le moment)</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}"
                    {{ old('teacher_id', $group->teacher_id ?? '') == $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Tu peux l’ajouter plus tard après validation.</small>
    </div>

    {{-- NIVEAU --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Niveau</label>
        <select name="level" class="form-select" required>
            @foreach (['A1', 'A2', 'B1', 'B2'] as $level)
                <option value="{{ $level }}"
                    {{ old('level', $group->level ?? '') == $level ? 'selected' : '' }}>
                    {{ $level }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- STATUT --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Statut</label>
        <select name="status" class="form-select" required id="group_status">
            <option value="active"   {{ old('status', $group->status ?? '') == 'active' ? 'selected' : '' }}>Actif</option>
            <option value="upcoming" {{ old('status', $group->status ?? '') == 'upcoming' ? 'selected' : '' }}>À venir</option>
        </select>
        <small class="text-muted">Si “À venir”, le suivi (dates) sera désactivé.</small>
    </div>

    {{-- HORAIRE --}}
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Horaire</label>
        <input type="text" name="time_range"
               class="form-control"
               value="{{ old('time_range', $group->time_range ?? '') }}"
               placeholder="Ex: 10:00 - 12:30" required>
        <small class="text-muted">La période sera détectée automatiquement.</small>
    </div>

</div>


{{-- ========================================================== --}}
{{-- ===============  SUIVI DU GROUPE (DATE) ================== --}}
{{-- ========================================================== --}}

<div class="row mt-4 pt-3 border-top" id="suivi_block">
    <h5 class="fw-bold mb-3">📅 Suivi du groupe</h5>

    {{-- MODE CHOIX --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Mode de saisie</label>

        <div class="d-flex gap-4 flex-wrap">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="date_mode" id="mode_picker" value="picker"
                       {{ old('date_mode', 'picker') === 'picker' ? 'checked' : '' }}>
                <label class="form-check-label" for="mode_picker">Date picker (Début → Fin)</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="date_mode" id="mode_manual" value="manual"
                       {{ old('date_mode') === 'manual' ? 'checked' : '' }}>
                <label class="form-check-label" for="mode_manual">Manuel (Début + Fin / Durée)</label>
            </div>
        </div>

        <small class="text-muted">
            Astuce : pour un nouveau parcours A1 → B2, utilise “Début + 10 mois”.
        </small>
    </div>

    {{-- MODE 1: RANGE PICKER --}}
    <div class="col-md-12 mb-3" id="picker_block">
        <label class="form-label fw-bold">Période du groupe (Début → Fin)</label>

        <input
            type="text"
            id="date_range_picker"
            class="form-control"
            placeholder="Sélectionner la période"
            value="
                @if(old('date_debut') && old('date_fin'))
                    {{ old('date_debut') }} to {{ old('date_fin') }}
                @elseif(!empty($group->date_debut) && !empty($group->date_fin))
                    {{ $group->date_debut }} to {{ $group->date_fin }}
                @endif
            "
        >

        <small class="text-muted">
            Les week-ends (samedi et dimanche) sont automatiquement désactivés.
        </small>
    </div>

    {{-- MODE 2: MANUEL --}}
    <div class="col-md-12 mb-3" id="manual_block" style="display:none;">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Date de début</label>
                <input type="date" id="manual_start" class="form-control"
                       value="{{ old('date_debut', $group->date_debut ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Durée (en mois)</label>
                <select id="manual_months" class="form-select">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ $i }}" {{ (int)old('duration_months', 10) === $i ? 'selected' : '' }}>
                            {{ $i }} mois
                        </option>
                    @endfor
                </select>
                <small class="text-muted">Par défaut: 10 mois (A1 → B2).</small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Date de fin (auto)</label>
                <input type="date" id="manual_end" class="form-control"
                       value="{{ old('date_fin', $group->date_fin ?? '') }}">
                <small class="text-muted">Tu peux aussi ajuster la fin manuellement.</small>
            </div>
        </div>
    </div>

    {{-- INFO DURÉE --}}
    <div class="col-md-12 mb-2">
        <div class="text-muted">
            Durée détectée : <strong id="duration_label">—</strong>
        </div>
    </div>

    {{-- HIDDEN FIELDS (Sent to Backend) --}}
    <input type="hidden" name="date_debut" id="date_debut_value"
           value="{{ old('date_debut', $group->date_debut ?? '') }}">

    <input type="hidden" name="date_fin" id="date_fin_value"
           value="{{ old('date_fin', $group->date_fin ?? '') }}">

</div>

<script>
(function () {
  const $status = document.getElementById('group_status');
  const $suiviBlock = document.getElementById('suivi_block');

  const $modePicker = document.getElementById('mode_picker');
  const $modeManual = document.getElementById('mode_manual');

  const $pickerBlock = document.getElementById('picker_block');
  const $manualBlock = document.getElementById('manual_block');

  const $rangeInput = document.getElementById('date_range_picker');

  const $manualStart = document.getElementById('manual_start');
  const $manualMonths = document.getElementById('manual_months');
  const $manualEnd = document.getElementById('manual_end');

  const $dateDebut = document.getElementById('date_debut_value');
  const $dateFin = document.getElementById('date_fin_value');
  const $durationLabel = document.getElementById('duration_label');

  if (!$status || !$suiviBlock || !$modePicker || !$modeManual || !$dateDebut || !$dateFin || !$durationLabel) return;

  const pad2 = (n) => String(n).padStart(2, '0');
  const toYMD = (d) => `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;

  const parseYMD = (s) => {
    if (!s) return null;
    const [y, m, d] = s.split('-').map(Number);
    if (!y || !m || !d) return null;
    return new Date(y, m - 1, d);
  };

  const lastDayOfMonth = (year, monthIndex) => new Date(year, monthIndex + 1, 0).getDate();

  const addMonthsSafe = (date, months) => {
    const y = date.getFullYear();
    const m = date.getMonth();
    const day = date.getDate();

    const targetMonth = m + months;
    const ty = y + Math.floor(targetMonth / 12);
    const tm = ((targetMonth % 12) + 12) % 12;

    const ld = lastDayOfMonth(ty, tm);
    const td = Math.min(day, ld);

    return new Date(ty, tm, td);
  };

  const diffMonths = (start, end) => {
    if (!start || !end) return null;
    let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
    if (end.getDate() < start.getDate()) months -= 1;
    return Math.max(0, months);
  };

  const updateDurationLabel = () => {
    const s = parseYMD($dateDebut.value);
    const e = parseYMD($dateFin.value);
    if (!s || !e) {
      $durationLabel.textContent = '—';
      return;
    }
    const m = diffMonths(s, e);
    $durationLabel.textContent = `${m} mois`;
  };

  const setMode = (mode) => {
    const isPicker = mode === 'picker';
    $pickerBlock.style.display = isPicker ? '' : 'none';
    $manualBlock.style.display = isPicker ? 'none' : '';

    if (!isPicker) {
      if ($dateDebut.value) $manualStart.value = $dateDebut.value;
      if ($dateFin.value) $manualEnd.value = $dateFin.value;
    }

    updateDurationLabel();
  };

  const clearDates = () => {
    $dateDebut.value = '';
    $dateFin.value = '';
    if ($rangeInput) $rangeInput.value = '';
    if ($manualStart) $manualStart.value = '';
    if ($manualEnd) $manualEnd.value = '';
    $durationLabel.textContent = '—';
  };

  const toggleSuiviByStatus = () => {
    const upcoming = ($status.value === 'upcoming');

    if (upcoming) {
      $suiviBlock.style.display = 'none';
      clearDates();
    } else {
      $suiviBlock.style.display = '';
      updateDurationLabel();
    }
  };

  // --- Events mode
  $modePicker.addEventListener('change', () => $modePicker.checked && setMode('picker'));
  $modeManual.addEventListener('change', () => $modeManual.checked && setMode('manual'));

  $status.addEventListener('change', toggleSuiviByStatus);

  // --- Manual logic
  const recomputeEndFromMonths = () => {
    const start = parseYMD($manualStart.value);
    const months = parseInt(($manualMonths && $manualMonths.value) ? $manualMonths.value : '0', 10);
    if (!start || !months) return;

    const end = addMonthsSafe(start, months);
    if ($manualEnd) $manualEnd.value = toYMD(end);

    $dateDebut.value = $manualStart.value || '';
    $dateFin.value = ($manualEnd && $manualEnd.value) ? $manualEnd.value : '';
    updateDurationLabel();
  };

  $manualStart && $manualStart.addEventListener('change', () => {
    $dateDebut.value = $manualStart.value || '';
    recomputeEndFromMonths();
  });

  $manualMonths && $manualMonths.addEventListener('change', () => {
    recomputeEndFromMonths();
  });

  $manualEnd && $manualEnd.addEventListener('change', () => {
    $dateDebut.value = $manualStart ? ($manualStart.value || '') : '';
    $dateFin.value = $manualEnd.value || '';
    updateDurationLabel();
  });

  // --- Range picker hookup (tu gardes ton flatpickr existant)
  window.__syncGroupDatesFromRange = function (startYMD, endYMD) {
    // si status upcoming => on ignore
    if ($status.value === 'upcoming') return;

    $dateDebut.value = startYMD || '';
    $dateFin.value = endYMD || '';
    updateDurationLabel();
  };

  // init mode
  const initialMode = ($modeManual.checked ? 'manual' : 'picker');
  setMode(initialMode);

  // init toggle suivi
  toggleSuiviByStatus();

  // init duration label
  updateDurationLabel();
})();
</script>
