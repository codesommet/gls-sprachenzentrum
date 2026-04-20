@extends('frontoffice.layouts.app')

@section('title', __('partners/fc_marokko.meta.title'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/partners/fc-marokko.css') }}">
@endpush

@section('content')

    {{-- ================== HERO ================== --}}
    <section class="fcm-hero">
        <div class="fcm-hero__bg"
            style="background-image:url('https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg');">
        </div>
        <div class="fcm-hero__overlay"></div>

        <div class="fcm-hero__stripes" aria-hidden="true">
            <span></span><span></span><span></span><span></span>
        </div>

        <div class="container fcm-hero__container">

            <div class="fcm-hero__badge reveal delay-1">
                <span class="fcm-dot"></span>
                {{ __('partners/fc_marokko.hero.badge') }}
            </div>

            <div class="fcm-hero__lockup reveal delay-2">
                <div class="fcm-lockup__logo">
                    <img src="{{ asset('assets/images/logo/gls-round.png') }}"
                         onerror="this.src='{{ asset('assets/images/gls-noir.png') }}'"
                         alt="GLS Sprachenzentrum">
                </div>
                <div class="fcm-lockup__x">×</div>
                <div class="fcm-lockup__logo fcm-lockup__logo--alt">
                    <img src="https://fc-marokko.de/storage/2022/10/fcm-logo.png"
                         alt="{{ __('partners/fc_marokko.hero.alt') }}">
                </div>
            </div>

            <h1 class="fcm-hero__title reveal delay-3">
                {!! __('partners/fc_marokko.hero.title') !!}
            </h1>

            <p class="fcm-hero__subtitle reveal delay-3">
                {{ __('partners/fc_marokko.hero.subtitle') }}
            </p>

            <div class="fcm-hero__meta reveal delay-1">
                {{ __('partners/fc_marokko.hero.meta') }}
            </div>

            <div class="fcm-hero__actions reveal delay-2">
                <a href="#fcm-story" class="fcm-btn fcm-btn--primary">
                    {{ __('partners/fc_marokko.hero.cta_primary') }}
                    <i class="bi bi-arrow-down-short"></i>
                </a>
                <a href="https://fc-marokko.de/" target="_blank" rel="noopener" class="fcm-btn fcm-btn--ghost">
                    {{ __('partners/fc_marokko.hero.cta_secondary') }}
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>

        </div>
    </section>

    {{-- ================== STATS STRIP ================== --}}
    <section class="fcm-stats">
        <div class="container">
            <div class="fcm-stats__grid">
                <div class="fcm-stat reveal delay-1">
                    <div class="fcm-stat__value">{{ __('partners/fc_marokko.stats.s1_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s1_label') }}</div>
                </div>
                <div class="fcm-stat reveal delay-2">
                    <div class="fcm-stat__value">{{ __('partners/fc_marokko.stats.s2_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s2_label') }}</div>
                </div>
                <div class="fcm-stat reveal delay-3">
                    <div class="fcm-stat__value">{{ __('partners/fc_marokko.stats.s3_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s3_label') }}</div>
                </div>
                <div class="fcm-stat reveal delay-1">
                    <div class="fcm-stat__value">{{ __('partners/fc_marokko.stats.s4_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s4_label') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================== STORY ================== --}}
    <section id="fcm-story" class="fcm-story">
        <div class="container">
            <div class="row g-5 align-items-center">

                <div class="col-12 col-lg-6 reveal delay-1">
                    <div class="fcm-story__kicker">
                        <span class="fcm-bar"></span>
                        {{ __('partners/fc_marokko.content.kicker') }}
                    </div>
                    <h2 class="fcm-story__heading">
                        {{ __('partners/fc_marokko.content.heading') }}
                    </h2>

                    <div class="fcm-story__text">
                        <p>{!! __('partners/fc_marokko.content.p1') !!}</p>
                        <p>{!! __('partners/fc_marokko.content.p2') !!}</p>
                        <p>{!! __('partners/fc_marokko.content.p3') !!}</p>
                    </div>

                    <a href="https://fc-marokko.de/" target="_blank" rel="noopener" class="fcm-btn fcm-btn--dark">
                        {{ __('partners/fc_marokko.section1.button') }}
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>

                <div class="col-12 col-lg-6 reveal delay-2">
                    <div class="fcm-story__visual">
                        <div class="fcm-story__card fcm-story__card--main">
                            <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                                 alt="FC Marokko" loading="lazy">
                            <div class="fcm-story__card-tag">
                                <i class="bi bi-trophy-fill"></i>
                                FC Marokko
                            </div>
                        </div>
                        <div class="fcm-story__card fcm-story__card--badge">
                            <img src="https://fc-marokko.de/storage/2022/10/fcm-logo.png" alt="FC Marokko logo" loading="lazy">
                        </div>
                        <div class="fcm-story__ball" aria-hidden="true">
                            <i class="bi bi-dribbble"></i>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ================== PILLARS ================== --}}
    <section class="fcm-pillars">
        <div class="container">

            <div class="fcm-section-head text-center reveal delay-1">
                <div class="fcm-section-head__kicker">
                    <span class="fcm-bar"></span>
                    {{ __('partners/fc_marokko.pillars.title') }}
                    <span class="fcm-bar"></span>
                </div>
                <h2 class="fcm-section-head__title">{{ __('partners/fc_marokko.pillars.subtitle') }}</h2>
            </div>

            <div class="row g-4 fcm-pillars__grid">

                <div class="col-12 col-md-4 reveal delay-1">
                    <article class="fcm-pillar fcm-pillar--green">
                        <div class="fcm-pillar__index">{{ __('partners/fc_marokko.pillars.c1_kicker') }}</div>
                        <div class="fcm-pillar__icon"><i class="bi bi-shield-check"></i></div>
                        <h3 class="fcm-pillar__title">{{ __('partners/fc_marokko.pillars.c1_title') }}</h3>
                        <p class="fcm-pillar__text">{!! __('partners/fc_marokko.pillars.c1_text') !!}</p>
                    </article>
                </div>

                <div class="col-12 col-md-4 reveal delay-2">
                    <article class="fcm-pillar fcm-pillar--orange">
                        <div class="fcm-pillar__index">{{ __('partners/fc_marokko.pillars.c2_kicker') }}</div>
                        <div class="fcm-pillar__icon"><i class="bi bi-bullseye"></i></div>
                        <h3 class="fcm-pillar__title">{{ __('partners/fc_marokko.pillars.c2_title') }}</h3>
                        <p class="fcm-pillar__text">{!! __('partners/fc_marokko.pillars.c2_text') !!}</p>
                    </article>
                </div>

                <div class="col-12 col-md-4 reveal delay-3">
                    <article class="fcm-pillar fcm-pillar--red">
                        <div class="fcm-pillar__index">{{ __('partners/fc_marokko.pillars.c3_kicker') }}</div>
                        <div class="fcm-pillar__icon"><i class="bi bi-people-fill"></i></div>
                        <h3 class="fcm-pillar__title">{{ __('partners/fc_marokko.pillars.c3_title') }}</h3>
                        <p class="fcm-pillar__text">{!! __('partners/fc_marokko.pillars.c3_text') !!}</p>
                    </article>
                </div>

            </div>

        </div>
    </section>

    {{-- ================== GALLERY ================== --}}
    <section class="fcm-gallery">
        <div class="container">

            <div class="fcm-section-head reveal delay-1">
                <div class="fcm-section-head__kicker">
                    <span class="fcm-bar"></span>
                    {{ __('partners/fc_marokko.gallery.kicker') }}
                </div>
                <h2 class="fcm-section-head__title fcm-section-head__title--left">
                    {{ __('partners/fc_marokko.gallery.title') }}
                </h2>
                <p class="fcm-section-head__sub">{!! __('partners/fc_marokko.gallery.subtitle') !!}</p>
            </div>

            <div class="fcm-gallery__grid">

                <a class="fcm-gallery__item fcm-gallery__item--big"
                   href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                   target="_blank" rel="noopener">
                    <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg" alt="FC Marokko" loading="lazy">
                    <div class="fcm-gallery__overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                </a>

                <a class="fcm-gallery__item"
                   href="https://fc-marokko.de/storage/2022/10/fcm-logo.png"
                   target="_blank" rel="noopener">
                    <img src="https://fc-marokko.de/storage/2022/10/fcm-logo.png" alt="FC Marokko" loading="lazy">
                    <div class="fcm-gallery__overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                </a>

                <a class="fcm-gallery__item"
                   href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                   target="_blank" rel="noopener">
                    <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg" alt="FC Marokko" loading="lazy">
                    <div class="fcm-gallery__overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                </a>

                <a class="fcm-gallery__item"
                   href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                   target="_blank" rel="noopener">
                    <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg" alt="FC Marokko" loading="lazy">
                    <div class="fcm-gallery__overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                </a>

                <a class="fcm-gallery__item"
                   href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                   target="_blank" rel="noopener">
                    <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg" alt="FC Marokko" loading="lazy">
                    <div class="fcm-gallery__overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                </a>

            </div>

        </div>
    </section>

    {{-- ================== CTA ================== --}}
    <section class="fcm-cta-wrap">
        <div class="container">
            <div class="fcm-cta reveal delay-1">
                <div class="fcm-cta__stripes" aria-hidden="true">
                    <span></span><span></span><span></span>
                </div>
                <div class="fcm-cta__content">
                    <div class="fcm-cta__kicker">{{ __('partners/fc_marokko.cta.kicker') }}</div>
                    <h2 class="fcm-cta__title">{{ __('partners/fc_marokko.cta.title') }}</h2>
                    <p class="fcm-cta__text">{!! __('partners/fc_marokko.cta.text') !!}</p>
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}" class="fcm-btn fcm-btn--primary fcm-btn--lg">
                        {{ __('partners/fc_marokko.cta.button') }}
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
