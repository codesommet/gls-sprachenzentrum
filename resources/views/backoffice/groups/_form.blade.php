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

    {{-- DATE DE DÉBUT --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de début</label>
        <input
            type="text"
            id="date_range_picker"
            class="form-control"
            placeholder="Sélectionner la date de début"
            value="{{ old('date_debut', $group->date_debut ?? '') }}"
        >
        <small class="text-muted">
            Les week-ends sont automatiquement désactivés.
        </small>
    </div>

    {{-- DATE DE FIN (editable, auto-calculated to +10 months) --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Date de fin</label>
        <input type="date" id="picker_end_display" name="date_fin" class="form-control"
               value="{{ old('date_fin', $group->date_fin ?? '') }}">
        <small class="text-muted">
            Calculée automatiquement à 10 mois. Vous pouvez la modifier manuellement.
        </small>
    </div>

    {{-- INFO DURÉE --}}
    <div class="col-md-12 mb-2">
        <div class="text-muted">
            Durée détectée : <strong id="duration_label">—</strong>
        </div>
    </div>

    {{-- HIDDEN FIELD for date_debut (Sent to Backend) --}}
    <input type="hidden" name="date_debut" id="date_debut_value"
           value="{{ old('date_debut', $group->date_debut ?? '') }}">

</div>

<script>
(function () {
  const $status = document.getElementById('group_status');
  const $suiviBlock = document.getElementById('suivi_block');
  const $rangeInput = document.getElementById('date_range_picker');
  const $pickerEndDisplay = document.getElementById('picker_end_display');
  const $dateDebut = document.getElementById('date_debut_value');
  const $durationLabel = document.getElementById('duration_label');

  const FORMATION_MONTHS = 10;

  if (!$status || !$suiviBlock || !$dateDebut || !$durationLabel) return;

  const pad2 = (n) => String(n).padStart(2, '0');
  const toYMD = (d) => `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;

  const parseYMD = (s) => {
    if (!s) return null;
    const clean = String(s).trim();
    const [y, m, d] = clean.split('-').map(Number);
    if (!y || !m || !d) return null;
    return new Date(y, m - 1, d);
  };

  const addMonths = (date, months) => {
    const result = new Date(date);
    result.setMonth(result.getMonth() + months);
    return result;
  };

  const diffMonths = (start, end) => {
    if (!start || !end) return null;
    let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
    if (end.getDate() < start.getDate()) months -= 1;
    return Math.max(0, months);
  };

  const updateDurationLabel = () => {
    const s = parseYMD($dateDebut.value);
    const e = parseYMD($pickerEndDisplay ? $pickerEndDisplay.value : '');
    if (!s || !e) {
      $durationLabel.textContent = '—';
      return;
    }
    $durationLabel.textContent = diffMonths(s, e) + ' mois';
  };

  const clearDates = () => {
    $dateDebut.value = '';
    if ($rangeInput) $rangeInput.value = '';
    if ($pickerEndDisplay) $pickerEndDisplay.value = '';
    $durationLabel.textContent = '—';
  };

  const toggleSuiviByStatus = () => {
    if ($status.value === 'upcoming') {
      $suiviBlock.style.display = 'none';
      clearDates();
    } else {
      $suiviBlock.style.display = '';
      updateDurationLabel();
    }
  };

  $status.addEventListener('change', toggleSuiviByStatus);

  // Auto-calculate end date: start + 10 months
  const autoCalcEnd = (startValue) => {
    const start = parseYMD(startValue);
    if (!start) return;

    const end = addMonths(start, FORMATION_MONTHS);
    const endYMD = toYMD(end);

    $dateDebut.value = startValue;
    if ($pickerEndDisplay) $pickerEndDisplay.value = endYMD;
    updateDurationLabel();
  };

  // Called from the date picker when user picks a start date
  window.__syncGroupDatesFromPicker = function (startYMD) {
    if ($status.value === 'upcoming') return;
    autoCalcEnd(startYMD);
  };

  // When user manually edits end date, just update duration label
  if ($pickerEndDisplay) {
    $pickerEndDisplay.addEventListener('change', updateDurationLabel);
  }

  // Confirm popup if duration is not ~10 months on form submit
  const form = $status.closest('form');
  if (form) {
    form.addEventListener('submit', function (e) {
      if ($status.value !== 'active') return; // no check for upcoming

      const s = parseYMD($dateDebut.value);
      const endVal = $pickerEndDisplay ? $pickerEndDisplay.value : '';
      const en = parseYMD(endVal);

      if (!s || !en) return; // dates missing, let backend validate

      const days = Math.round((en - s) / (1000 * 60 * 60 * 24));
      const months = (days / 30.44).toFixed(1);

      // Allow ±15 days tolerance around 10 months (~304 days)
      if (days < 289 || days > 319) {
        const ok = confirm(
          'La durée du groupe est de ' + months + ' mois au lieu des 10 mois habituels.\n\n' +
          'Voulez-vous continuer quand même ?'
        );
        if (!ok) {
          e.preventDefault();
          e.stopPropagation();
        }
      }
    });
  }

  // init
  toggleSuiviByStatus();
  updateDurationLabel();
})();
</script>
