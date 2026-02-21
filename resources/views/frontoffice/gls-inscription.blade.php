{{-- resources/views/frontoffice/pages/gls-inscription.blade.php --}}
@extends('frontoffice.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/gls-form-page.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

{{-- ✅ URLs dynamiques --}}
@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-centers-url" content="{{ url('/api/centers') }}">
    <meta name="api-groups-url-template" content="{{ url('/api/groups') }}/__SITE__">
    <meta name="api-dates-url-template" content="{{ url('/api/groups/dates') }}/__GROUP__">
    <meta name="gls-store-url" content="{{ LaravelLocalization::localizeUrl(route('gls.inscription')) }}">
@endpush

@section('content')
    @php
        $tr = function (string $key, string $fallback) {
            $val = __($key);
            return $val === $key ? $fallback : $val;
        };
    @endphp

    <main class="gls-inscription-page gls-inscription-scope">
        <section class="gls-inscription-section section py-5">
            <div class="container reveal delay-1">
                <div class="row justify-content-center">
                    <div class="col-lg-9">

                        <div class="form-card reveal delay-2">
                            <div class="decorative-element" aria-hidden="true"></div>

                            <div class="form-content">

                                <div class="form-header">
                                    <h1 class="form-title mb-2">
                                        {{ $tr('templates/gls-form.header.title', 'Inscription GLS') }}
                                    </h1>
                                    <p class="form-subtitle mb-0">
                                        {{ $tr('templates/gls-form.header.subtitle', 'Complétez le formulaire pour envoyer votre demande') }}
                                    </p>
                                </div>

                                <div class="error-message" id="glsErrorMessage">
                                    <span id="glsErrorText"></span>
                                </div>

                                <form id="glsMultiStepForm">
                                    @csrf

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step1', 'Informations') }}
                                    </h4>

                                    <div class="form-group">
                                        <label for="glsName">
                                            {{ $tr('templates/gls-form.fields.name.label', 'Nom complet') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="glsName" name="name"
                                            placeholder="{{ $tr('templates/gls-form.fields.name.placeholder', 'Votre nom complet') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsEmail">
                                            {{ $tr('templates/gls-form.fields.email.label', 'Email') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="email" id="glsEmail" name="email"
                                            placeholder="{{ $tr('templates/gls-form.fields.email.placeholder', 'email@example.com') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsPhone">
                                            {{ $tr('templates/gls-form.fields.phone.label', 'Téléphone') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="tel" id="glsPhone" name="phone"
                                            placeholder="{{ $tr('templates/gls-form.fields.phone.placeholder', '+212 6XX-XXXXXX') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsAdresse">
                                            {{ $tr('templates/gls-form.fields.adresse.label', 'Adresse') }}
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="glsAdresse" name="adresse"
                                            placeholder="{{ $tr('templates/gls-form.fields.adresse.placeholder', 'Votre adresse complète') }}"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="divider"></div>

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step2', 'Centre GLS') }}
                                    </h4>

                                    <div class="form-group">
                                        <label for="glsTypeCours">
                                            {{ $tr('templates/gls-form.fields.type_cours.label', 'Type de cours') }}
                                            <span class="required">*</span>
                                        </label>
                                        <select id="glsTypeCours" name="type_cours" required>
                                            <option value="">
                                                {{ $tr('templates/gls-form.fields.type_cours.placeholder', 'Choisissez un type') }}
                                            </option>
                                            <option value="presentiel">
                                                {{ $tr('templates/gls-form.fields.type_cours_options.presentiel', 'Présentiel') }}
                                            </option>
                                            <option value="en_ligne">
                                                {{ $tr('templates/gls-form.fields.type_cours_options.en_ligne', 'En ligne') }}
                                            </option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group" id="glsCentreWrapper">
                                        <label for="glsCentre">
                                            {{ $tr('templates/gls-form.fields.centre.label', 'Centre GLS préféré') }}
                                            <span class="required">*</span>
                                        </label>
                                        <select id="glsCentre" name="centre">
                                            <option value="">
                                                {{ $tr('templates/gls-form.fields.centre.placeholder', 'Sélectionner un centre') }}
                                            </option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="divider"></div>

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step3', 'Groupe') }}
                                    </h4>

                                    <div class="form-group">
                                        <label for="glsGroupId">
                                            {{ $tr('templates/gls-form.fields.group_id.label', 'Groupe') }}
                                            <span class="required">*</span>
                                        </label>
                                        <select id="glsGroupId" name="group_id" required>
                                            <option value="">
                                                {{ $tr('templates/gls-form.fields.group_id.placeholder', 'Sélectionner un groupe') }}
                                            </option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="glsNiveau">
                                            {{ $tr('templates/gls-form.fields.niveau.label', 'Niveau d’Allemand') }}
                                            <span class="required">*</span>
                                        </label>
                                        <select id="glsNiveau" name="niveau" required>
                                            <option value="">
                                                {{ $tr('templates/gls-form.fields.niveau.placeholder', 'Sélectionner un niveau') }}
                                            </option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="divider"></div>

                                    <h4 class="section-title">
                                        {{ $tr('templates/gls-form.progress.steps.step4', 'Préférences') }}
                                    </h4>

                                    <div class="form-group">
                                        <label for="glsHorairePrefere">
                                            {{ $tr('templates/gls-form.fields.horaire_prefere.label', 'Horaire de cours') }}
                                        </label>
                                        <input type="text" id="glsHorairePrefere" name="horaire_prefere" readonly
                                            placeholder="{{ $tr('templates/gls-form.fields.horaire_prefere.placeholder', 'Auto rempli') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="glsDateStart">
                                            {{ $tr('templates/gls-form.fields.date_start.label', 'À partir de…') }}
                                        </label>
                                        <input type="text" id="glsDateStart" name="date_start"
                                            placeholder="{{ $tr('templates/gls-form.fields.date_start.placeholder', 'Sélectionner une date') }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="checkbox-row" for="glsAcceptTerms">
                                            <input type="checkbox" id="glsAcceptTerms" name="accept_terms"
                                                value="1" required>
                                            <span class="checkbox-text">
                                                {{ $tr('templates/gls-form.fields.accept_terms.label', 'J’accepte les') }}
                                                <a href="{{ LaravelLocalization::localizeUrl(route('front.terms')) }}"
                                                    target="_blank" class="link">
                                                    {{ $tr('templates/gls-form.fields.accept_terms.link', 'conditions générales') }}
                                                </a>
                                                <span class="required">*</span>
                                            </span>
                                        </label>
                                        <div class="invalid-feedback d-block"></div>
                                    </div>

                                    <div class="button-group">
                                        <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}"
                                            class="button button-outline">
                                            &larr; {{ $tr('templates/gls-form.buttons.cancel', 'Annuler') }}
                                        </a>

                                        <button type="submit" class="button" id="glsSubmitBtn">
                                            {{ $tr('templates/gls-form.buttons.submit', 'Envoyer') }}
                                        </button>
                                    </div>
                                </form>

                                <div class="success-message" id="glsSuccessMessage">
                                    <h3 class="mb-2">
                                        {{ $tr('templates/gls-form.messages.success_title', 'Demande envoyée !') }}</h3>
                                    <p class="mb-4">
                                        {{ $tr('templates/gls-form.messages.success_text', 'Merci. Nous vous contacterons très vite.') }}
                                    </p>
                                    <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="button">
                                        {{ $tr('templates/gls-form.buttons.back_home', 'Retour à l’accueil') }}
                                    </a>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('assets/js/gls-form-page.js') }}"></script>
@endpush
