<!-- CONSULTATION POPUP -->
<div class="modal fade" id="consultationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">

            <div class="container" id="consultationRoot">
                <div class="form-card">

                    <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">✕</button>

                    <div class="decorative-element"></div>

                    <div class="form-content">

                        <div class="form-header" id="consultationFormHeader">
                            <h1 class="form-title">Consultation gratuite</h1>
                            <p class="form-subtitle">
                                Laissez-nous vos informations et nous vous contacterons rapidement
                            </p>
                        </div>

                        <div class="error-message" id="consultationErrorMessage"></div>

                        <form id="consultationForm" method="POST" action="{{ route('front.consultation.store') }}">
                            @csrf

                            <div class="form-step active" data-step="1">

                                <div class="form-group">
                                    <label for="consultationName">Nom complet <span class="required">*</span></label>
                                    <input type="text" id="consultationName" name="name"
                                        placeholder="Votre nom complet" required>
                                </div>

                                <div class="form-group">
                                    <label for="consultationCity">Ville <span class="required">*</span></label>
                                    <input type="text" id="consultationCity" name="city" placeholder="Votre ville"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="consultationPhone">Téléphone <span class="required">*</span></label>
                                    <input type="tel" id="consultationPhone" name="phone"
                                        placeholder="+212 6XX XXX XXX" required>
                                </div>

                                <div class="form-group">
                                    <label for="consultationEmail">Email <span class="required">*</span></label>
                                    <input type="email" id="consultationEmail" name="email"
                                        placeholder="email@example.com" required>
                                </div>

                            </div>

                            <div class="button-group">
                                <button type="submit" class="button" id="consultationSubmitBtn">
                                    Envoyer la demande
                                </button>
                            </div>
                        </form>

                        <!-- SUCCESS -->
                        <div class="success-message" id="consultationSuccessMessage">
                            <div class="success-icon"></div>
                            <h3>Merci !</h3>
                            <p>
                                Votre demande de consultation a bien été envoyée.
                                Nous vous contacterons très bientôt.
                            </p>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .error-message.error {
        color: #d93025;
        margin-bottom: 20px;
    }
</style>


<script src="{{ asset('assets/js/consultation-form.js') }}"></script>
