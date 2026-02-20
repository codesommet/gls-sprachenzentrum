@extends('frontoffice.layouts.app')

@section('title', __('contact.meta.title'))

{{-- Bootstrap CSS --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/contact/contact.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/contact/sites.css') }}">
@section('content')

    {{-- ================================
     CONTACT HERO SECTION
================================ --}}
    <section class="hero-section section is-no-image reveal delay-1">
        <div class="container is-hero reveal delay-2">
            <h1 class="hero_title fade-blur-title reveal delay-1">{{ __('contact.hero.title') }}</h1>
            <div class="hero_subtitle reveal delay-2">
                {!! __('contact.hero.subtitle') !!}
            </div>
        </div>
    </section>

    {{-- ================================
     LOCATIONS + OPENING HOURS (GRID)
================================ --}}
    <section class="contact-locations-section py-5"
        style="opacity:1 !important; visibility:visible !important; display:block !important;">
        <div class="container py-4">

            <h2 class="locations-title" style="margin-bottom:.75rem;">
                {{ __('contact.locations.title') }}
            </h2>

            <p class="locations-subtitle" style="max-width:920px; margin-bottom:2.2rem; color:rgba(0,0,0,.65);">
                {{ __('contact.locations.subtitle') }}
            </p>

            @php
                // ✅ Tout est piloté par translations
                $emailGlobal = __('contact.locations.global.email');

                $hoursGlobal = __('contact.locations.global.hours');
                if (!is_array($hoursGlobal)) {
                    $hoursGlobal = [];
                }

                // ✅ Liste des centres (avec images en asset(...) EXACTEMENT comme ton ancien code)
                $locations = __('contact.locations.list');
                if (!is_array($locations)) {
                    $locations = [];
                }

                // fallback: email/hours si non définis dans un centre
                foreach ($locations as $i => $loc) {
                    if (empty($loc['email'])) {
                        $locations[$i]['email'] = $emailGlobal;
                    }
                    if (empty($loc['hours']) || !is_array($loc['hours'])) {
                        $locations[$i]['hours'] = $hoursGlobal;
                    }
                }
            @endphp

            <div class="locations-grid" style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:34px 40px;">
                @foreach ($locations as $loc)
                    @php
                        $collapseId = 'locCollapse_' . ($loc['key'] ?? uniqid());

                        $mapsQuery = $loc['maps_query'] ?? ($loc['address'] ?? '');
                        $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapsQuery);
                    @endphp

                    <article class="location-card" style="background:transparent;">
                        <div class="location-image"
                            style="border-radius:10px; overflow:hidden; background:#f3f3f3; aspect-ratio:16/7;">
                            <img src="{{ $loc['image'] }}" alt="{{ $loc['name'] }}"
                                style="width:100%; height:100%; object-fit:cover; display:block;">
                        </div>

                        <button class="location-toggle" type="button" data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}"
                            style="width:100%; border:0; background:transparent; padding:18px 0 14px; display:flex; align-items:center; justify-content:space-between; gap:18px; cursor:pointer;">
                            <span class="location-name"
                                style="font-size:1.15rem; font-weight:800;">{{ $loc['name'] }}</span>
                            <span class="location-plus" aria-hidden="true"></span>
                        </button>

                        <div id="{{ $collapseId }}" class="collapse location-collapse"
                            style="border-top:1px solid rgba(0,0,0,.08); padding-top:14px;">
                            <div class="location-content" style="padding-bottom:6px;">

                                <div class="location-block" style="margin-bottom:14px;">
                                    <div class="location-label"
                                        style="font-size:.85rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(0,0,0,.55); margin-bottom:6px;">
                                        {{ __('contact.locations.labels.address') }}
                                    </div>
                                    <div class="location-text" style="color:rgba(0,0,0,.75);">
                                        {{ $loc['address'] }}
                                    </div>
                                </div>

                                <div class="location-block" style="margin-bottom:14px;">
                                    <div class="location-label"
                                        style="font-size:.85rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(0,0,0,.55); margin-bottom:6px;">
                                        {{ __('contact.locations.labels.hours') }}
                                    </div>

                                    <ul class="hours-list" style="list-style:none; padding:0; margin:0;">
                                        @foreach ($loc['hours'] as $day => $time)
                                            <li
                                                style="display:flex; justify-content:space-between; gap:12px; padding:6px 0; border-bottom:1px dashed rgba(0,0,0,.08);">
                                                <span style="font-weight:700; color:rgba(0,0,0,.75);">
                                                    {{ $day }}
                                                </span>
                                                <span style="color:rgba(0,0,0,.75);">
                                                    {{ $time }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="location-block" style="margin-bottom:14px;">
                                    <div class="location-label"
                                        style="font-size:.85rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(0,0,0,.55); margin-bottom:6px;">
                                        {{ __('contact.locations.labels.contact') }}
                                    </div>

                                    <div class="location-links" style="display:grid; gap:8px; margin-bottom:10px;">
                                        @if (!empty($loc['phone']))
                                            <a href="tel:{{ preg_replace('/\s+/', '', $loc['phone']) }}"
                                                style="display:inline-flex; align-items:center; gap:10px; text-decoration:none; color:rgba(0,0,0,.78); font-weight:600;">
                                                <i class="bi bi-telephone"></i> {{ $loc['phone'] }}
                                            </a>
                                        @endif

                                        @if (!empty($loc['email']))
                                            <a href="mailto:{{ $loc['email'] }}"
                                                style="display:inline-flex; align-items:center; gap:10px; text-decoration:none; color:rgba(0,0,0,.78); font-weight:600;">
                                                <i class="bi bi-envelope"></i> {{ $loc['email'] }}
                                            </a>
                                        @endif
                                    </div>

                                    <a class="btn btn-outline-dark" target="_blank" rel="noopener"
                                        href="{{ $mapsUrl }}"
                                        style="border-radius:999px; font-weight:700; padding:10px 16px;">
                                        <i class="bi bi-geo-alt"></i> {{ __('contact.locations.buttons.maps') }}
                                    </a>
                                </div>

                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

        </div>
    </section>

    {{-- ================================
     CONTACT FORM SECTION
================================ --}}
    <section id="contact-form" class="contact-form-section section py-5 reveal delay-1">
        <div class="container py-4 reveal delay-2">

            <div class="text-center mb-5 reveal delay-1">
                <h2 class="fw-bold fade-blur-title reveal delay-1">{{ __('contact.form.title') }}</h2>
                <p class="text-muted reveal delay-2">{{ __('contact.form.subtitle') }}</p>
            </div>

            {{-- ALERTES --}}
            @if (session('success'))
                <div class="alert alert-success text-center fw-semibold rounded-3 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger text-center fw-semibold rounded-3 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-3 mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="fw-semibold">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ LaravelLocalization::localizeUrl(route('front.contact.post')) }}" method="POST"
                class="mx-auto reveal delay-3" style="max-width: 700px;">

                @csrf
                <div class="row g-4">

                    <div class="col-md-6 reveal delay-1">
                        <label class="form-label fw-semibold">{{ __('contact.form.name') }}</label>
                        <input type="text" name="name" class="form-control rounded-3 p-3" required>
                    </div>

                    <div class="col-md-6 reveal delay-2">
                        <label class="form-label fw-semibold">{{ __('contact.form.email') }}</label>
                        <input type="email" name="email" class="form-control rounded-3 p-3" required>
                    </div>

                    <div class="col-12 reveal delay-1">
                        <label class="form-label fw-semibold">{{ __('contact.form.subject') }}</label>
                        <input type="text" name="subject" class="form-control rounded-3 p-3" required>
                    </div>

                    <div class="col-12 reveal delay-2">
                        <label class="form-label fw-semibold">{{ __('contact.form.message') }}</label>
                        <textarea name="message" rows="5" class="form-control rounded-3 p-3" required></textarea>
                    </div>

                    <div class="col-12 text-center reveal delay-3">
                        <button type="submit" class="btn btn-success px-5 py-3 rounded-pill fw-semibold">
                            {{ __('contact.form.button') }}
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </section>


    {{-- ===============================
 CONTACT SECTION
================================ --}}
    <section class="contact-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            {{-- LEFT SIDE --}}
            <div class="div-block-5-copy reveal delay-3">

                <h2 class="contact-section-subtitle reveal fade-blur-title delay-1">
                    {!! __('home.contact.title') !!}
                </h2>


                <div class="div-block-21 reveal delay-2">

                    <a href="tel:+212669515019" class="link-block reveal delay-1">
                        <div class="text-block-3 reveal delay-2">
                            <span class="text-span reveal delay-3">{!! __('home.contact.call_label') !!}<br></span>
                            +212 6 69 51 50 19
                        </div>
                    </a>

                    <a href="mailto:info@glssprachenzentrum.ma" class="link-block-2 reveal delay-3">
                        <div class="text-block-3 reveal delay-1">
                            <span class="text-span reveal delay-2">{!! __('home.contact.email_label') !!}<br></span>
                            info@glssprachenzentrum.ma
                        </div>
                    </a>

                </div>

                <div class="text-block-3 visit-block reveal delay-3">
                    <span class="text-span reveal delay-1">{!! __('home.contact.visit_label') !!}</span><br>
                    {!! __('home.contact.addresses') !!}
                </div>

                <div class="footer-socials-block reveal delay-1">

                    <div class="text-block-3 reveal delay-2">
                        <span class="text-span reveal delay-3">{!! __('home.contact.follow_label') !!}</span>
                    </div>

                    <div class="div-block-20 reveal delay-1">
                        <a href="https://www.instagram.com/gls.sprachenzentrum/" class="footer-social-link ig"
                            target="_blank" rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>

                        <a href="https://www.facebook.com/gls.sale/" class="footer-social-link fb" target="_blank"
                            rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>

                        <a href="https://www.youtube.com/@9onsolsTalks" class="footer-social-link yt" target="_blank"
                            rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>

                        <a href="https://api.whatsapp.com/send/?phone=0669515019&text&type=phone_number&app_absent=0"
                            class="footer-social-link wa" target="_blank" rel="noopener noreferrer"
                            aria-label="Contacter GLS Sprachenzentrum sur WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>

                    </div>

                </div>

            </div>

            {{-- RIGHT SIDE: MAP CAROUSEL (AUTO-CYCLING) --}}
            <div>
                {{-- Map Container with Auto-Carousel --}}
                <a id="mapLink" href="https://www.google.com/maps/search/?api=1&query=GLS+Sprachenzentrum+Rabat"
                    target="_blank" class="div-block-7 reveal delay-3">
                    <iframe id="mapFrame"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3307.8001465016737!2d-6.8485901!3d33.9976668!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76dcf7a656da5%3A0xcaf46ae5e6e81d87!2sGLS%20Sprachenzentrum%20-%20Centre%20GLS%20de%20langue%20Allemande%20Rabat!5e0!3m2!1sen!2sma!4v1769193870895!5m2!1sen!2sma"
                        allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        class="reveal delay-1"></iframe>
                </a>
            </div>

        </div>
    </section>

@endsection
