/**
 * GLS Inscription Multi-Step Form
 * Scoped to #glsInscriptionRoot to prevent conflicts with other modals
 *
 * FIXES:
 * - Prevent "Unexpected token '<' ... not valid JSON" by:
 *   1) Sending Accept: application/json + X-Requested-With
 *   2) Safely parsing response (JSON if possible, else text) and logging HTML errors
 * - Uses current locale prefix from <html lang="fr"> to post to /{locale}/gls-inscription
 * - Better error messages for 419/422/500/404
 */

(function () {
  // Get root container - all queries scoped within this element
  const root = document.getElementById("glsInscriptionRoot");
  if (!root) return;

  // Local state
  let currentStep = 1;
  const totalSteps = 3;

  // Scoped DOM queries (all within root)
  const formSteps = root.querySelectorAll(".form-step");
  const progressSteps = root.querySelectorAll(".progress-step");
  const progressFill = root.querySelector("#glsProgressFill");

  const nextBtn = root.querySelector("#glsNextBtn");
  const prevBtn = root.querySelector("#glsPrevBtn");
  const errorMessage = root.querySelector("#glsErrorMessage");
  const form = root.querySelector("#glsMultiStepForm");
  const successMessage = root.querySelector("#glsSuccessMessage");
  const progressContainer = root.querySelector("#glsProgressContainer");
  const formHeader = root.querySelector("#glsFormHeader");
  const buttonGroup = root.querySelector(".button-group");

  // Form inputs - all scoped within root
  const typeCours = root.querySelector("#glsTypeCours");
  const centreWrapper = root.querySelector("#glsCentreWrapper");
  const centreSelect = root.querySelector("#glsCentre");
  const groupSelect = root.querySelector("#glsGroupId");
  const niveauSelect = root.querySelector("#glsNiveau");
  const dateInput = root.querySelector("#glsDateStart");
  const horairePrefereInput = root.querySelector("#glsHorairePrefere");

  // ===== Helpers =====
  function getLocalePrefix() {
    const lang = (document.documentElement.lang || "fr").toLowerCase();
    // If your site uses "fr", "en", etc.
    const locale = lang.split("-")[0] || "fr";
    return `/${locale}`;
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : "";
  }

  function showError(msg) {
    errorMessage.textContent = msg;
    errorMessage.classList.add("active");
  }

  function clearError() {
    errorMessage.textContent = "";
    errorMessage.classList.remove("active");
  }

  async function parseResponse(res) {
    const contentType = (res.headers.get("content-type") || "").toLowerCase();

    // Try JSON if server says it's JSON
    if (contentType.includes("application/json")) {
      const data = await res.json();
      return { ok: res.ok, status: res.status, data, rawText: null };
    }

    // Otherwise read as text (often HTML error page)
    const text = await res.text();
    return { ok: res.ok, status: res.status, data: null, rawText: text };
  }

  function mapHttpErrorToMessage(status) {
    if (status === 419) return "Session expirée (419). Recharge la page et réessaie.";
    if (status === 422) return "Veuillez vérifier les champs du formulaire (422).";
    if (status === 404) return "Route introuvable (404). Vérifie l’URL du POST.";
    if (status === 500) return "Erreur serveur (500). Vérifie les logs Laravel.";
    return `Une erreur est survenue (HTTP ${status}).`;
  }

  // ===== Init: centre wrapper visibility =====
  centreWrapper.style.display = "none";
  centreSelect.removeAttribute("required");

  // Static levels with French and English descriptions
  const NIVEAUX_DATA = [
    { code: "A0", fr: "A0 – Aucune connaissance préalable", en: "A0 – No prior knowledge" },
    { code: "A1", fr: "A1 – Débutant", en: "A1 – Beginner" },
    { code: "A2", fr: "A2 – Élémentaire", en: "A2 – Elementary" },
    { code: "B1", fr: "B1 – Intermédiaire", en: "B1 – Intermediate" },
    { code: "B2", fr: "B2 – Intermédiaire Supérieur", en: "B2 – Upper Intermediate" }
  ];

  function loadStaticLevels() {
    const locale = document.documentElement.lang || "fr";
    const isFrench = locale.toLowerCase().startsWith("fr");

    niveauSelect.innerHTML = '<option value="">Sélectionner un niveau</option>';
    NIVEAUX_DATA.forEach((level) => {
      const text = isFrench ? level.fr : level.en;
      niveauSelect.innerHTML += `<option value="${level.code}">${text}</option>`;
    });
  }
  loadStaticLevels();

  let flatpickrInstance = null;

  /* ============================== LOAD CENTERS ============================== */
  function loadCenters() {
    centreSelect.innerHTML = "<option>Chargement...</option>";

    fetch("/api/centers", {
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      }
    })
      .then((res) => res.json())
      .then((data) => {
        centreSelect.innerHTML = '<option value="">Sélectionner un centre</option>';
        data.forEach((c) => {
          centreSelect.innerHTML += `<option value="${c.id}">${c.name} (${c.city})</option>`;
        });
      })
      .catch(() => {
        centreSelect.innerHTML = "<option>Erreur</option>";
      });
  }

  /* ============================== LOAD DATES ============================== */
  function loadDatesForGroup(groupId) {
    dateInput.value = "";

    // Destroy existing flatpickr instance
    if (flatpickrInstance) flatpickrInstance.destroy();

    // IMPORTANT: keep selector scoped? flatpickr needs actual input element
    flatpickrInstance = flatpickr(dateInput, {
      dateFormat: "Y-m-d",
      minDate: "today",
      placeholder: "Sélectionner une date"
    });

    console.log("[GLS Form] Date picker initialized for group:", groupId);
  }

  /* ============================== GROUP SELECT EVENTS ============================== */
  groupSelect.addEventListener("change", () => {
    const selected = groupSelect.options[groupSelect.selectedIndex];
    if (!selected || !selected.value) return;

    const groupText = selected.textContent || "";
    console.log("[GLS Form] Group selected:", groupText);

    const timeMatch = groupText.match(/(\d{1,2}:\d{2}\s*–\s*\d{1,2}:\d{2})/);
    const groupTime = timeMatch ? timeMatch[1] : "";

    horairePrefereInput.value = groupTime;
    console.log("[GLS Form] Set horaire_prefere to:", groupTime);

    loadDatesForGroup(selected.value);
  });

  /* ============================== UPDATE GROUP TIMES BASED ON CENTER ============================== */
  function updateGroupTimes() {
    const selectedCenter = centreSelect.options[centreSelect.selectedIndex];
    if (!selectedCenter || !selectedCenter.value) return;

    const centerText = (selectedCenter.textContent || "").toLowerCase();

    const groupsByCenter = {
      rabat: [
        { id: 1, name: "Groupe 10:00 – 12:00" },
        { id: 2, name: "Groupe 15:00 – 17:00" },
        { id: 3, name: "Groupe 17:00 – 19:00" },
        { id: 4, name: "Groupe 19:00 – 21:00" }
      ],
      casablanca: [
        { id: 5, name: "Groupe 10:00 – 12:00" },
        { id: 6, name: "Groupe 15:00 – 17:00" },
        { id: 7, name: "Groupe 17:00 – 19:00" },
        { id: 8, name: "Groupe 19:00 – 21:00" }
      ],
      casa: [
        { id: 5, name: "Groupe 10:00 – 12:00" },
        { id: 6, name: "Groupe 15:00 – 17:00" },
        { id: 7, name: "Groupe 17:00 – 19:00" },
        { id: 8, name: "Groupe 19:00 – 21:00" }
      ],
      marrakech: [
        { id: 9, name: "Groupe 10:00 – 12:30" },
        { id: 10, name: "Groupe 13:00 – 15:30" },
        { id: 11, name: "Groupe 16:30 – 18:00" },
        { id: 12, name: "Groupe 19:00 – 21:30" }
      ],
      sale: [
        { id: 13, name: "Groupe 10:00 – 12:00" },
        { id: 14, name: "Groupe 15:00 – 17:00" },
        { id: 15, name: "Groupe 17:00 – 19:00" },
        { id: 16, name: "Groupe 19:00 – 21:00" }
      ],
      kenitra: [
        { id: 17, name: "Groupe 10:00 – 12:30" },
        { id: 18, name: "Groupe 16:00 – 18:30" },
        { id: 19, name: "Groupe 18:30 – 21:00" }
      ],
      agadir: [
        { id: 21, name: "Groupe 10:00 – 12:30" },
        { id: 22, name: "Groupe 16:00 – 18:30" },
        { id: 23, name: "Groupe 18:30 – 21:00" }
      ],
      online: [{ id: 25, name: "Groupe Nuit 20:00 – 22:00" }]
    };

    let groups = [];
    for (const [city, cityGroups] of Object.entries(groupsByCenter)) {
      if (centerText.includes(city)) {
        groups = cityGroups;
        break;
      }
    }

    groupSelect.innerHTML = '<option value="">Sélectionner un groupe</option>';
    groups.forEach((group) => {
      groupSelect.innerHTML += `<option value="${group.id}">${group.name}</option>`;
    });

    // Reset dependent fields when center changes
    dateInput.value = "";
    horairePrefereInput.value = "";
    if (flatpickrInstance) {
      flatpickrInstance.destroy();
      flatpickrInstance = null;
    }
  }

  /* ============================== TYPE COURS EVENTS ============================== */
  typeCours.addEventListener("change", () => {
    clearError();

    if (typeCours.value === "presentiel") {
      centreWrapper.style.display = "block";
      centreSelect.setAttribute("required", "required");
      loadCenters();

      // Reset group/date/time when switching type
      groupSelect.innerHTML = '<option value="">Sélectionner un groupe</option>';
      dateInput.value = "";
      horairePrefereInput.value = "";
    } else if (typeCours.value === "en_ligne") {
      centreWrapper.style.display = "none";
      centreSelect.removeAttribute("required");
      centreSelect.innerHTML = "";

      groupSelect.innerHTML = '<option value="">Sélectionner un groupe</option>';
      groupSelect.innerHTML += '<option value="25">Groupe Nuit 20:00 – 22:00</option>';

      dateInput.value = "";
      horairePrefereInput.value = "20:00 – 22:00";

      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }
      // Optional: init date picker immediately for online group
      loadDatesForGroup(25);
    } else {
      centreWrapper.style.display = "none";
      centreSelect.removeAttribute("required");
      centreSelect.innerHTML = "";

      groupSelect.innerHTML = '<option value="">Sélectionner un groupe</option>';
      dateInput.value = "";
      horairePrefereInput.value = "";

      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }
    }
  });

  centreSelect.addEventListener("change", function () {
    clearError();
    updateGroupTimes();
  });

  /* ============================== PROGRESS SYSTEM ============================== */
  function updateProgress() {
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    progressFill.style.width = progress + "%";

    progressSteps.forEach((step, i) => {
      step.classList.remove("active", "completed");
      if (i + 1 < currentStep) step.classList.add("completed");
      if (i + 1 === currentStep) step.classList.add("active");
    });

    formSteps.forEach((step) => {
      step.classList.toggle("active", step.dataset.step == currentStep);
    });

    prevBtn.style.display = currentStep === 1 ? "none" : "block";
    nextBtn.textContent = currentStep === totalSteps ? "Envoyer" : "Continuer";
  }

  function validateStep() {
    clearError();

    const currentEl = root.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredInputs = currentEl.querySelectorAll("[required]");

    for (let input of requiredInputs) {
      const value = (input.value || "").trim();
      if (!value) {
        showError("Veuillez remplir les champs obligatoires.");
        input.focus();
        return false;
      }
    }
    return true;
  }

  /* ============================== SUBMIT (SAFE JSON) ============================== */
  async function submitForm() {
    const csrf = getCsrfToken();
    if (!csrf) {
      showError("CSRF token manquant. Vérifie le meta csrf-token dans le layout.");
      return;
    }

    const formData = new FormData(form);
    
    // Remove centre field for en_ligne courses (online courses don't need a center)
    if (typeCours.value === "en_ligne") {
      formData.delete("centre");
    }
    
    formData.append("form_source", "modal");

    // Use current locale instead of hardcoding /fr
    const endpoint = `${getLocalePrefix()}/gls-inscription`;

    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrf,
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
      });

      const parsed = await parseResponse(res);

      if (!parsed.ok) {
        // If HTML returned, log it (first chars) to find the real error (419/500/redirect page...)
        if (parsed.rawText) {
          console.error("[GLS Form] Non-JSON response:", parsed.status, parsed.rawText.slice(0, 500));
        } else {
          console.error("[GLS Form] JSON error:", parsed.status, parsed.data);
        }

        // If validation errors from Laravel (422 JSON)
        if (parsed.status === 422 && parsed.data && parsed.data.errors) {
          const firstKey = Object.keys(parsed.data.errors)[0];
          const firstMsg = firstKey ? parsed.data.errors[firstKey][0] : null;
          showError(firstMsg || mapHttpErrorToMessage(parsed.status));
          return;
        }

        showError(mapHttpErrorToMessage(parsed.status));
        return;
      }

      // OK
      const data = parsed.data || {};

      if (data.status === "success") {
        if (window.gtag) {
          gtag("event", "gls_inscription_submitted", { form_source: "modal" });
        }

        form.style.display = "none";
        progressContainer.style.display = "none";
        buttonGroup.style.display = "none";
        formHeader.style.display = "none";
        successMessage.classList.add("active");
        return;
      }

      if (data.status === "duplicate") {
        showError(data.message || "Vous êtes déjà inscrit.");
        return;
      }

      showError(data.message || "Une erreur est survenue.");
    } catch (err) {
      console.error(err);
      showError("Impossible d'envoyer votre inscription.");
    }
  }

  /* ============================== NEXT BUTTON ============================== */
  nextBtn.addEventListener("click", () => {
    if (!validateStep()) return;

    if (currentStep === totalSteps) {
      submitForm();
      return;
    }

    currentStep++;
    updateProgress();
  });

  /* ============================== PREV BUTTON ============================== */
  prevBtn.addEventListener("click", () => {
    if (currentStep > 1) {
      currentStep--;
      updateProgress();
    }
  });

  // Initial progress update
  updateProgress();

  // Track form impression when modal opens
  const modal = document.getElementById("glsEnrollModal");
  if (modal) {
    if (window.gtag) {
      gtag("event", "gls_inscription_form_viewed", { form_source: "modal" });
    }

    modal.addEventListener("show.bs.modal", function () {
      if (window.gtag) {
        gtag("event", "gls_inscription_form_viewed", { form_source: "modal" });
      }
    });

    // Listen for modal close to reset state
    modal.addEventListener("hidden.bs.modal", function () {
      currentStep = 1;
      form.reset();
      clearError();
      successMessage.classList.remove("active");

      form.style.display = "";
      progressContainer.style.display = "";
      buttonGroup.style.display = "";
      formHeader.style.display = "";

      // Reset date picker
      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }

      updateProgress();
    });
  }
})();