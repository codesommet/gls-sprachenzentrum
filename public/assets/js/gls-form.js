/**
 * GLS Inscription Multi-Step Form
 * Scoped to #glsInscriptionRoot to prevent conflicts with other modals
 */

(function() {
    // Get root container - all queries scoped within this element
    const root = document.getElementById('glsInscriptionRoot');
    if (!root) return;

    // Local state
    let currentStep = 1;
    const totalSteps = 4;

    // Scoped DOM queries (all within root)
    const formSteps = root.querySelectorAll('.form-step');
    const progressSteps = root.querySelectorAll('.progress-step');
    const progressFill = root.querySelector('#glsProgressFill');
    
    const nextBtn = root.querySelector('#glsNextBtn');
    const prevBtn = root.querySelector('#glsPrevBtn');
    const errorMessage = root.querySelector('#glsErrorMessage');
    const form = root.querySelector('#glsMultiStepForm');
    const successMessage = root.querySelector('#glsSuccessMessage');
    const progressContainer = root.querySelector('#glsProgressContainer');
    const formHeader = root.querySelector('#glsFormHeader');
    const buttonGroup = root.querySelector('.button-group');

    // Form inputs - all scoped within root
    const typeCours = root.querySelector('#glsTypeCours');
    const centreWrapper = root.querySelector('#glsCentreWrapper');
    const centreSelect = root.querySelector('#glsCentre');
    const groupSelect = root.querySelector('#glsGroupId');
    const niveauSelect = root.querySelector('#glsNiveau');
    const dateInput = root.querySelector('#glsDateStart');
    const horairePrefereInput = root.querySelector('#glsHorairePrefere');

    // Initialize centre wrapper visibility
    centreWrapper.style.display = "none";
    centreSelect.removeAttribute("required");

    // Static levels
    const NIVEAUX = ["A1", "A2", "B1", "B2"];
    function loadStaticLevels() {
        niveauSelect.innerHTML = '<option value="">Sélectionner un niveau</option>';
        NIVEAUX.forEach(level => {
            niveauSelect.innerHTML += `<option value="${level}">${level}</option>`;
        });
    }
    loadStaticLevels();

    let flatpickrInstance = null;

    /* ============================== LOAD CENTERS ============================== */
    function loadCenters() {
        centreSelect.innerHTML = "<option>Chargement...</option>";

        fetch("/api/centers")
            .then(res => res.json())
            .then(data => {
                centreSelect.innerHTML = '<option value="">Sélectionner un centre</option>';
                data.forEach(c =>
                    centreSelect.innerHTML += `<option value="${c.id}">${c.name} (${c.city})</option>`
                );
            })
            .catch(() => centreSelect.innerHTML = "<option>Erreur</option>");
    }

    /* ============================== LOAD GROUPS ============================== */
    function loadGroups() {
        const siteId = centreSelect.value;
        if (!siteId) return;

        groupSelect.innerHTML = "<option>Chargement...</option>";

        fetch(`/api/groups/${siteId}`)
            .then(res => res.json())
            .then(groups => {
                groupSelect.innerHTML = '<option value="">Sélectionner un groupe</option>';

                groups.forEach((g) => {
                    const name = g.display_name;

                    groupSelect.innerHTML += `
                        <option value="${g.id}"
                            data-level="${g.level}"
                            data-time="${g.time_range}">
                            ${name} (${g.time_range})
                        </option>`;
                });
            })
            .catch(() => groupSelect.innerHTML = "<option>Erreur</option>");
    }

    /* ============================== LOAD DATES ============================== */
    function loadDatesForGroup(groupId) {
        dateInput.value = "";
        dateInput.placeholder = "Chargement...";

        fetch(`/api/groups/dates/${groupId}`)
            .then(res => res.json())
            .then(availableDates => {

                if (!availableDates.length) {
                    dateInput.placeholder = "Aucune date disponible";
                    return;
                }

                if (flatpickrInstance) flatpickrInstance.destroy();

                // Scoped to #glsDateStart
                flatpickrInstance = flatpickr("#glsDateStart", {
                    dateFormat: "Y-m-d",
                    disable: [
                        d => !availableDates.includes(d.toISOString().split("T")[0])
                    ],
                    onDayCreate(_, __, ___, dayElem) {
                        const date = dayElem.dateObj.toISOString().split("T")[0];
                        if (availableDates.includes(date)) {
                            dayElem.classList.add("available-date");
                        }
                    }
                });

                dateInput.placeholder = "Sélectionner une date";
            });
    }

    /* ============================== GROUP SELECT EVENTS ============================== */
    groupSelect.addEventListener("change", () => {
        const selected = groupSelect.options[groupSelect.selectedIndex];
        if (!selected.value) return;

        const groupLevel = selected.getAttribute("data-level");
        niveauSelect.value = groupLevel;

        const groupTime = selected.getAttribute("data-time");
        horairePrefereInput.value = groupTime;

        loadDatesForGroup(selected.value);
    });

    /* ============================== TYPE COURS EVENTS ============================== */
    typeCours.addEventListener("change", () => {
        if (typeCours.value === "presentiel") {
            centreWrapper.style.display = "block";
            centreSelect.setAttribute("required", "required");
            loadCenters();
        } else {
            centreWrapper.style.display = "none";
            centreSelect.removeAttribute("required");
            centreSelect.innerHTML = "";

            groupSelect.innerHTML = "";
            dateInput.value = "";
            horairePrefereInput.value = "";
        }
    });

    centreSelect.addEventListener("change", loadGroups);

    /* ============================== PROGRESS SYSTEM ============================== */
    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressFill.style.width = progress + '%';

        // Update progress steps
        progressSteps.forEach((step, i) => {
            step.classList.remove("active", "completed");
            if (i + 1 < currentStep) step.classList.add("completed");
            if (i + 1 === currentStep) step.classList.add("active");
        });

        // Show/hide form steps
        formSteps.forEach(step => {
            step.classList.toggle("active", step.dataset.step == currentStep);
        });

        prevBtn.style.display = currentStep === 1 ? "none" : "block";
        nextBtn.textContent = currentStep === totalSteps ? "Envoyer" : "Continuer";
    }

    function validateStep() {
        // Scoped query within root
        const currentEl = root.querySelector(`.form-step[data-step="${currentStep}"]`);
        const requiredInputs = currentEl.querySelectorAll("[required]");

        for (let input of requiredInputs) {
            if (!input.value.trim()) {
                errorMessage.textContent = "Veuillez remplir les champs obligatoires.";
                errorMessage.classList.add("active");
                input.focus();
                return false;
            }
        }
        return true;
    }

    /* ============================== NEXT BUTTON ============================== */
    nextBtn.addEventListener("click", () => {
        if (!validateStep()) return;

        if (currentStep === totalSteps) {
            // Submit form
            const formData = new FormData(form);
            
            // Add tracking parameter for modal
            formData.append("form_source", "modal");

            fetch("/fr/gls-inscription", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    // Track successful submission for modal
                    if (window.gtag) {
                        gtag('event', 'gls_inscription_submitted', { form_source: 'modal' });
                    }
                    
                    // Hide form elements
                    form.style.display = "none";
                    progressContainer.style.display = "none";
                    buttonGroup.style.display = "none";
                    formHeader.style.display = "none";
                    successMessage.classList.add("active");
                }
                else if (data.status === "duplicate") {
                    errorMessage.textContent = data.message;
                    errorMessage.classList.add("active");
                }
                else {
                    errorMessage.textContent = "Une erreur est survenue.";
                    errorMessage.classList.add("active");
                }
            })
            .catch(err => {
                console.error(err);
                errorMessage.textContent = "Impossible d'envoyer votre inscription.";
                errorMessage.classList.add("active");
            });

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
    const modal = document.getElementById('glsEnrollModal');
    if (modal) {
        // Track modal open
        if (window.gtag) {
            gtag('event', 'gls_inscription_form_viewed', { form_source: 'modal' });
        }
        
        modal.addEventListener('show.bs.modal', function() {
            if (window.gtag) {
                gtag('event', 'gls_inscription_form_viewed', { form_source: 'modal' });
            }
        });
        
        // Listen for modal close to reset state
        modal.addEventListener('hidden.bs.modal', function() {
            // Reset form
            currentStep = 1;
            form.reset();
            errorMessage.textContent = '';
            errorMessage.classList.remove('active');
            successMessage.classList.remove('active');
            
            // Reset UI visibility
            form.style.display = '';
            progressContainer.style.display = '';
            buttonGroup.style.display = '';
            formHeader.style.display = '';
            
            updateProgress();
        });
    }
})();
