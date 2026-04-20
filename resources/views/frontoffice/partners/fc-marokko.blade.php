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

        {{-- Rolling football intro animation --}}
        <div class="fcm-rolling-ball" aria-hidden="true">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <radialGradient id="fcmBallShade" cx="35%" cy="30%" r="80%">
                        <stop offset="0%" stop-color="#ffe27a"/>
                        <stop offset="55%" stop-color="#ffb90e"/>
                        <stop offset="100%" stop-color="#b87d00"/>
                    </radialGradient>
                </defs>
                <circle cx="50" cy="50" r="48" fill="url(#fcmBallShade)" stroke="#121212" stroke-width="2"/>
                <polygon points="50,22 62,32 58,47 42,47 38,32"
                         fill="#121212"/>
                <polygon points="50,53 66,60 60,76 40,76 34,60"
                         fill="#121212" opacity=".85"/>
                <polygon points="22,40 34,36 38,50 30,60 20,54"
                         fill="#121212" opacity=".7"/>
                <polygon points="78,40 80,54 70,60 62,50 66,36"
                         fill="#121212" opacity=".7"/>
                <circle cx="50" cy="50" r="48" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="1"/>
            </svg>
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
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6" fill="#fff"/>
                                <path d="M12 4.5 14.8 7l-1 3.2h-3.6l-1-3.2L12 4.5Z" fill="currentColor"/>
                                <path d="M12 13.6 15.2 16l-1.2 3.3h-4l-1.2-3.3L12 13.6Z" fill="currentColor"/>
                                <path d="m4.4 8.8 2.9.9.8 3.2-2.6 2.2-2.3-1.7.2-4.6 1-.0Z" fill="currentColor"/>
                                <path d="m19.6 8.8 1 .0.2 4.6-2.3 1.7-2.6-2.2.8-3.2 2.9-.9Z" fill="currentColor"/>
                            </svg>
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

            @php
                $fcmGallery = [
                    'https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg',
                    'https://fc-marokko.de/storage/2022/10/fcm-logo.png',
                    'https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg',
                    'https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg',
                    'https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg',
                ];
            @endphp

            <div class="fcm-gallery__grid">
                @foreach ($fcmGallery as $i => $img)
                    <button type="button"
                            class="fcm-gallery__item{{ $i === 0 ? ' fcm-gallery__item--big' : '' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#fcmGalleryModal"
                            data-fcm-index="{{ $i }}">
                        <img src="{{ $img }}" alt="FC Marokko" loading="lazy">
                        <div class="fcm-gallery__overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                    </button>
                @endforeach
            </div>

        </div>
    </section>

    {{-- ================== GALLERY MODAL (CAROUSEL) ================== --}}
    <div class="modal fade fcm-modal" id="fcmGalleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content fcm-modal__content">
                <button type="button" class="fcm-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div id="fcmGalleryCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                    <div class="carousel-inner">
                        @foreach ($fcmGallery as $i => $img)
                            <div class="carousel-item @if ($i === 0) active @endif">
                                <img src="{{ $img }}" class="d-block w-100 fcm-modal__img" alt="FC Marokko">
                            </div>
                        @endforeach
                    </div>

                    <button class="carousel-control-prev fcm-modal__nav" type="button"
                            data-bs-target="#fcmGalleryCarousel" data-bs-slide="prev">
                        <span class="fcm-modal__nav-icon"><i class="bi bi-chevron-left"></i></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next fcm-modal__nav" type="button"
                            data-bs-target="#fcmGalleryCarousel" data-bs-slide="next">
                        <span class="fcm-modal__nav-icon"><i class="bi bi-chevron-right"></i></span>
                        <span class="visually-hidden">Next</span>
                    </button>

                    <div class="fcm-modal__counter">
                        <span class="fcm-current">1</span> / <span class="fcm-total">{{ count($fcmGallery) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const modalEl = document.getElementById('fcmGalleryModal');
                if (!modalEl) return;
                const carouselEl = document.getElementById('fcmGalleryCarousel');
                const counter = modalEl.querySelector('.fcm-current');

                modalEl.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;
                    const index = parseInt(trigger.getAttribute('data-fcm-index') || '0', 10);
                    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
                    carousel.to(index);
                    if (counter) counter.textContent = index + 1;
                });

                carouselEl.addEventListener('slid.bs.carousel', function (event) {
                    if (counter) counter.textContent = event.to + 1;
                });

                document.addEventListener('keydown', function (e) {
                    if (!modalEl.classList.contains('show')) return;
                    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
                    if (e.key === 'ArrowLeft')  carousel.prev();
                    if (e.key === 'ArrowRight') carousel.next();
                });
            })();
        </script>
    @endpush

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
