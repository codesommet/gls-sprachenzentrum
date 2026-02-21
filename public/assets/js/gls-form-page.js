/**
 * GLS Inscription Form (ONE PAGE)
 * File: public/assets/js/gls-form-page.js
 *
 * ✅ Works for BOTH: Popup/Modal (with #glsInscriptionRoot) AND Full Page (without wrapper)
 * ✅ One-page (no steps / no progress)
 * ✅ Loads centers -> groups -> dates (flatpickr) + fills level/time
 * ✅ Tracks analytics for both contexts
 */

// Guard to prevent double initialization
if (window.__glsFormPageInitialized) {
  console.warn('[GLS Form Page] Already initialized, skipping');
} else {
  window.__glsFormPageInitialized = true;

function initGlsFormPage() {
  // Support both modal and page contexts
  const root = document.getElementById("glsInscriptionRoot") || document;
  const form = root.querySelector ? root.querySelector("#glsMultiStepForm") : document.getElementById("glsMultiStepForm");
  if (!form) {
    console.warn('[GLS Form Page] Form not found');
    return;
  }
  
  console.log('[GLS Form Page] Initializing...');

  // Get API URLs from meta tags
  const apiCentersUrl = document.querySelector('meta[name="api-centers-url"]')?.content || '/api/centers';
  const apiGroupsUrlTemplate = document.querySelector('meta[name="api-groups-url-template"]')?.content || '/api/groups/__SITE__';
  const apiDatesUrlTemplate = document.querySelector('meta[name="api-dates-url-template"]')?.content || '/api/groups/dates/__GROUP__';
  
  console.log('[GLS Form Page] API URLs:', {
    centers: apiCentersUrl,
    groups: apiGroupsUrlTemplate,
    dates: apiDatesUrlTemplate,
  });

  // Get form elements (works in both contexts)
  const errorWrap = root.querySelector("#glsErrorMessage");
  const errorText = root.querySelector("#glsErrorText");
  const successMessage = root.querySelector("#glsSuccessMessage");

  const typeCours = root.querySelector("#glsTypeCours");
  const centreWrapper = root.querySelector("#glsCentreWrapper");
  const centreSelect = root.querySelector("#glsCentre");

  const groupSelect = root.querySelector("#glsGroupId");
  const niveauSelect = root.querySelector("#glsNiveau");

  const dateInput = root.querySelector("#glsDateStart");
  const horairePrefereInput = root.querySelector("#glsHorairePrefere");

  // Buttons (optional)
  const submitBtn = root.querySelector("#glsSubmitBtn");

  // Log element detection
  console.log('[GLS Form Page] Elements found:', {
    form: !!form,
    typeCours: !!typeCours,
    centreWrapper: !!centreWrapper,
    centreSelect: !!centreSelect,
    groupSelect: !!groupSelect,
    niveauSelect: !!niveauSelect,
    dateInput: !!dateInput,
    horairePrefereInput: !!horairePrefereInput,
    submitBtn: !!submitBtn,
  });

  // Utils
  function showError(message) {
    if (!errorWrap || !errorText) return;
    errorText.textContent = message;
    errorWrap.classList.add("active");
  }

  function clearError() {
    if (!errorWrap || !errorText) return;
    errorText.textContent = "";
    errorWrap.classList.remove("active");
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : "";
  }

  function disable(el, state = true) {
    if (!el) return;
    el.disabled = !!state;
  }

  // Initial state
  if (centreWrapper) centreWrapper.style.display = "none";
  if (centreSelect) centreSelect.removeAttribute("required");

  // Static levels
  const NIVEAUX = ["A1", "A2", "B1", "B2"];
  function loadStaticLevels() {
    if (!niveauSelect) return;
    niveauSelect.innerHTML = `<option value="">Sélectionner un niveau</option>`;
    NIVEAUX.forEach((lvl) => {
      const opt = document.createElement("option");
      opt.value = lvl;
      opt.textContent = lvl;
      niveauSelect.appendChild(opt);
    });
  }
  loadStaticLevels();

  // Flatpickr instance
  let flatpickrInstance = null;

  /* ============================== LOAD CENTERS ============================== */
  function loadCenters() {
    if (!centreSelect) {
      console.warn('[GLS Form Page] centreSelect not found in loadCenters');
      return;
    }

    console.log('[GLS Form Page] Fetching centers from API:', apiCentersUrl);
    centreSelect.innerHTML = `<option value="">Chargement...</option>`;
    disable(centreSelect, true);

    fetch(apiCentersUrl)
      .then((res) => {
        console.log('[GLS Form Page] API response status:', res.status);
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((data) => {
        console.log('[GLS Form Page] Centers loaded:', data);
        if (!data || !Array.isArray(data)) {
          console.error('[GLS Form Page] Invalid data format:', data);
          centreSelect.innerHTML = `<option value="">Aucun centre disponible</option>`;
          disable(centreSelect, false);
          return;
        }
        
        centreSelect.innerHTML = `<option value="">Sélectionner un centre</option>`;
        data.forEach((c) => {
          const opt = document.createElement("option");
          opt.value = c.id;
          opt.textContent = `${c.name} (${c.city})`;
          centreSelect.appendChild(opt);
          console.log('[GLS Form Page] Added centre option:', c.name);
        });
        disable(centreSelect, false);
      })
      .catch((err) => {
        console.error('[GLS Form Page] Error loading centers:', err);
        centreSelect.innerHTML = `<option value="">Erreur de chargement</option>`;
        disable(centreSelect, false);
      });
  }

  /* ============================== LOAD GROUPS ============================== */
  function loadGroups() {
    if (!centreSelect || !groupSelect) {
      console.warn('[GLS Form Page] centreSelect or groupSelect not found');
      return;
    }
    const siteId = centreSelect.value;
    
    console.log('[GLS Form Page] Loading groups for site:', siteId);

    groupSelect.innerHTML = `<option value="">Sélectionner un groupe</option>`;
    horairePrefereInput && (horairePrefereInput.value = "");
    dateInput && (dateInput.value = "");

    if (!siteId) {
      console.warn('[GLS Form Page] No site selected');
      return;
    }

    const groupsUrl = apiGroupsUrlTemplate.replace('__SITE__', siteId);
    console.log('[GLS Form Page] Fetching groups from:', groupsUrl);
    
    groupSelect.innerHTML = `<option value="">Chargement...</option>`;
    disable(groupSelect, true);

    fetch(groupsUrl)
      .then((res) => {
        console.log('[GLS Form Page] Groups API response status:', res.status);
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((groups) => {
        console.log('[GLS Form Page] Groups loaded:', groups);
        if (!groups || !Array.isArray(groups)) {
          console.error('[GLS Form Page] Invalid groups format:', groups);
          groupSelect.innerHTML = `<option value="">Aucun groupe disponible</option>`;
          disable(groupSelect, false);
          return;
        }

        groupSelect.innerHTML = `<option value="">Sélectionner un groupe</option>`;

        groups.forEach((g) => {
          const name = g.display_name || g.name || `Groupe #${g.id}`;
          const opt = document.createElement("option");
          opt.value = g.id;
          opt.textContent = `${name} (${g.time_range})`;
          opt.setAttribute("data-level", g.level || "");
          opt.setAttribute("data-time", g.time_range || "");
          groupSelect.appendChild(opt);
          console.log('[GLS Form Page] Added group option:', name);
        });

        disable(groupSelect, false);
      })
      .catch((err) => {
        console.error('[GLS Form Page] Error loading groups:', err);
        groupSelect.innerHTML = `<option value="">Erreur de chargement</option>`;
        disable(groupSelect, false);
      });
  }

  /* ============================== LOAD DATES ============================== */
  /* ============================== LOAD DATES ============================== */
  function loadDatesForGroup(groupId) {
    if (!dateInput) return;

    dateInput.value = "";
    dateInput.placeholder = "Chargement...";

    const datesUrl = apiDatesUrlTemplate.replace('__GROUP__', groupId);
    console.log('[GLS Form Page] Fetching dates from:', datesUrl);

    fetch(datesUrl)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return res.json();
      })
      .then((availableDates) => {
        console.log('[GLS Form Page] Dates loaded:', availableDates);
        const dates = Array.isArray(availableDates) ? availableDates : [];

        if (!dates.length) {
          if (flatpickrInstance) {
            flatpickrInstance.destroy();
            flatpickrInstance = null;
          }
          dateInput.placeholder = "Aucune date disponible";
          return;
        }

        if (flatpickrInstance) flatpickrInstance.destroy();

        // Important: bind to the real element (scoped), not a global selector
        flatpickrInstance = flatpickr(dateInput, {
          dateFormat: "Y-m-d",
          allowInput: true,
          disable: [
            (d) => !dates.includes(d.toISOString().split("T")[0]),
          ],
          onDayCreate(_, __, ___, dayElem) {
            const d = dayElem.dateObj.toISOString().split("T")[0];
            if (dates.includes(d)) dayElem.classList.add("available-date");
          },
        });

        dateInput.placeholder = "Sélectionner une date";
      })
      .catch((err) => {
        console.error('[GLS Form Page] Error loading dates:', err);
        dateInput.placeholder = "Erreur de chargement";
      });
  }

  /* ============================== EVENTS ============================== */
  if (typeCours) {
    typeCours.addEventListener("change", () => {
      console.log('[GLS Form Page] Type cours changed to:', typeCours.value);
      clearError();

      // Reset dependent fields
      if (centreSelect) {
        centreSelect.innerHTML = `<option value="">Sélectionner un centre</option>`;
        centreSelect.value = "";
      }
      if (groupSelect) {
        groupSelect.innerHTML = `<option value="">Sélectionner un groupe</option>`;
        groupSelect.value = "";
      }
      if (dateInput) {
        dateInput.value = "";
        dateInput.placeholder = "Sélectionner une date";
      }
      if (horairePrefereInput) horairePrefereInput.value = "";

      // Destroy flatpickr when changing mode
      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }

      if (typeCours.value === "presentiel") {
        console.log('[GLS Form Page] Loading centers for PRESENTIEL');
        if (centreWrapper) centreWrapper.style.display = "block";
        if (centreSelect) centreSelect.setAttribute("required", "required");
        loadCenters();
      } else {
        console.log('[GLS Form Page] EN LIGNE mode - hiding centers');
        // en_ligne
        if (centreWrapper) centreWrapper.style.display = "none";
        if (centreSelect) centreSelect.removeAttribute("required");
      }
    });
  } else {
    console.warn('[GLS Form Page] typeCours element not found');
  }

  if (centreSelect) {
    centreSelect.addEventListener("change", () => {
      clearError();
      loadGroups();
    });
  }

  if (groupSelect) {
    groupSelect.addEventListener("change", () => {
      clearError();
      const selected = groupSelect.options[groupSelect.selectedIndex];
      if (!selected || !selected.value) return;

      const groupLevel = selected.getAttribute("data-level") || "";
      const groupTime = selected.getAttribute("data-time") || "";

      if (niveauSelect && groupLevel) niveauSelect.value = groupLevel;
      if (horairePrefereInput) horairePrefereInput.value = groupTime;

      loadDatesForGroup(selected.value);
    });
  }

  /* ============================== VALIDATION (ONE PAGE) ============================== */
  function validateForm() {
    clearError();

    // All required inputs inside root
    const requiredFields = form.querySelectorAll("[required]");
    for (const field of requiredFields) {
      // Checkbox required
      if (field.type === "checkbox") {
        if (!field.checked) {
          showError("Veuillez accepter les conditions générales.");
          field.focus();
          return false;
        }
        continue;
      }

      const value = (field.value || "").trim();
      if (!value) {
        showError("Veuillez remplir les champs obligatoires.");
        field.focus();
        return false;
      }
    }
    return true;
  }

  /* ============================== SUBMIT ============================== */
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    disable(submitBtn, true);

    const formData = new FormData(form);
    const isModal = !!document.getElementById("glsInscriptionRoot");
    const source = isModal ? "modal" : "page";
    
    // Add tracking parameter
    formData.append("form_source", source);

    fetch("/fr/gls-inscription", {
      method: "POST",
      headers: { "X-CSRF-TOKEN": getCsrfToken() },
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data && data.status === "success") {
          // Track successful submission
          if (window.gtag) {
            gtag('event', 'gls_inscription_submitted', { form_source: source });
          }
          
          form.style.display = "none";
          if (successMessage) successMessage.classList.add("active");
          return;
        }

        if (data && data.status === "duplicate") {
          showError(data.message || "Vous avez déjà envoyé une demande.");
          return;
        }

        showError("Une erreur est survenue.");
      })
      .catch((err) => {
        console.error(err);
        showError("Impossible d'envoyer votre inscription.");
      })
      .finally(() => {
        disable(submitBtn, false);
      });
  });

  /* ============================== OPTIONAL: RESET WHEN MODAL CLOSE ============================== */
  const modal = document.getElementById("glsEnrollModal");
  if (modal) {
    modal.addEventListener("hidden.bs.modal", function () {
      form.reset();
      clearError();
      if (successMessage) successMessage.classList.remove("active");
      form.style.display = "";

      if (centreWrapper) centreWrapper.style.display = "none";
      if (centreSelect) {
        centreSelect.removeAttribute("required");
        centreSelect.innerHTML = `<option value="">Sélectionner un centre</option>`;
      }
      if (groupSelect) {
        groupSelect.innerHTML = `<option value="">Sélectionner un groupe</option>`;
      }
      if (horairePrefereInput) horairePrefereInput.value = "";
      if (dateInput) {
        dateInput.value = "";
        dateInput.placeholder = "Sélectionner une date";
      }

      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }

      loadStaticLevels();
    });
  }

  /* ============================== AUTO-LOAD PRESENTIEL ============================== */
  // If form loads with "presentiel" already selected, auto-load centers
  if (typeCours && typeCours.value === "presentiel") {
    console.log('[GLS Form Page] Auto-loading centers (presentiel already selected)');
    if (centreWrapper) centreWrapper.style.display = "block";
    if (centreSelect) centreSelect.setAttribute("required", "required");
    loadCenters();
  }

  // Track form impressions (when page loads)
  if (window.gtag) {
    const isModal = !!document.getElementById("glsInscriptionRoot");
    const source = isModal ? "modal" : "page";
    gtag('event', 'gls_inscription_form_viewed', { form_source: source });
  }
}

// Try to initialize immediately
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initGlsFormPage);
} else {
  // Already loaded
  initGlsFormPage();
}

} // End guard wrapper