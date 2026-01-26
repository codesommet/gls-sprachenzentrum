@extends('frontoffice.layouts.app')

@section('content')
    <main class="home-page">
        {{-- ===========================
     HERO SECTION
=========================== --}}
        <section class="hero reveal delay-1" aria-label="Intro">
            <div class="hero__bg reveal delay-2"
                style="background-image: url('{{ asset('assets/images/home/IMG_4462.webp') }}');"></div>

            {{-- Badges --}}
            <div class="badge b-blue b1 reveal delay-3">{{ __('home.hero.badge1') }}</div>
            <div class="badge b-green b2 reveal delay-1">{{ __('home.hero.badge2') }}</div>
            <div class="badge b-orange b3 reveal delay-2">{{ __('home.hero.badge3') }}</div>
            <div class="badge b-violet b4 reveal delay-3">{{ __('home.hero.badge4') }}</div>

            <div class="hero__inner text-center {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
                <h1 class="hero-title reveal fade-blur-title delay-1">
                    {{ __('home.hero.title') }}
                </h1>
            </div>
        </section>

        {{-- ===========================
 INTRO SECTION
=========================== --}}
        <section
            class="intro-section position-relative text-center {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-2">

            <div class="intro-gradient reveal delay-3"></div>

            <div class="container position-relative z-2 py-5 reveal delay-1">

                <div class="intro-card shadow rounded-4 mx-auto p-4 reveal delay-2" style="max-width: 1020px;">

                    {{-- Logo + Tagline --}}
                    <div class="text-center mb-4 reveal delay-3" style="margin-top: 20px !important;">
                        <img src="{{ asset('build/images/logo/gls-noir.png') }}" alt="GLS Logo"
                            class="intro-logo mb-3 reveal delay-1" style="width: 120px; height: auto;">

                        <p class="text-primary fw-medium small mb-0 letter-spacing-1 reveal delay-2">
                            {{ __('home.intro.tagline') }}
                        </p>
                    </div>

                    {{-- Heading --}}
                    <h1 class="fw-bold mb-3 intro-heading reveal fade-blur-title delay-1">
                        {{ __('home.intro.heading') }}
                    </h1>

                    {{-- Description --}}
                    <p class="lead text-muted mb-4 intro-desc reveal delay-2">
                        {{ __('home.intro.description') }}
                    </p>

                    {{-- Button --}}
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.intensive-courses')) }}"
                        class="btn btn-success px-4 py-2 rounded-pill fw-semibold reveal delay-3">
                        {{ __('home.intro.button') }}
                    </a>
                </div>
            </div>
        </section>

        <section class="section sites-maroc-section">
            <div class="container text-center mb-5">
                <h2 class="sites-title">{{ __('gls.sites.title') }}</h2>
                <p class="sites-subtitle">{{ __('gls.sites.subtitle') }}</p>
            </div>

            <div class="container sites-grid">

                <!-- 1. Rabat -->
                <div class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/rabat.jpg') }}" alt="GLS Rabat" class="site-image">

                        <iframe
                            src="https://www.youtube.com/embed/f-DKSeGa5zI?autoplay=1&mute=1&controls=0&loop=1&playlist=f-DKSeGa5zI&modestbranding=1&playsinline=1&start=5"
                            width="560" height="315" frameborder="0" allow="autoplay; encrypted-media">
                        </iframe>
                    </div>

                    <div class="site-video-play">
                        <a class="play-btn"
                            data-video="https://www.youtube.com/embed/f-DKSeGa5zI?autoplay=1&mute=0&controls=1&modestbranding=1&rel=0">
                            <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                            <svg viewBox="0 0 448 512">
                                <path
                                    d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
                            </svg>
                        </a>
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.rabat') }}</h3>
                    </div>
                </div>


                <!-- 2. Kénitra -->
                <div class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/kenitra.jpg') }}" alt="GLS Kénitra" class="site-image">

                        <iframe
                            src="https://www.youtube.com/embed/eNry-9YjhYI?autoplay=1&mute=1&controls=0&loop=1&playlist=eNry-9YjhYI&modestbranding=1&playsinline=1"
                            width="560" height="315" frameborder="0">
                        </iframe>
                    </div>

                    <div class="site-video-play">
                        <a class="play-btn"
                            data-video="https://www.youtube.com/embed/eNry-9YjhYI?autoplay=1&controls=1&modestbranding=1&rel=0">
                            <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                            <svg viewBox="0 0 448 512">
                                <path
                                    d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
                            </svg>
                        </a>
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.kenitra') }}</h3>
                    </div>
                </div>


                <!-- 3. Marrakech -->
                <div class="site-card wide">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/marrakech.webp') }}" class="site-image">

                        <video class="site-video" muted loop preload="metadata" playsinline>
                            <source src="https://raw.githubusercontent.com/Rochdi7/Gls-Videos/main/agadir-cinematic.mp4">
                        </video>
                    </div>

                    <div class="site-video-play">
                        <a class="play-btn"
                            data-video="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&controls=1&modestbranding=1&rel=0">
                            <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                            <svg viewBox="0 0 448 512">
                                <path
                                    d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
                            </svg>
                        </a>
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.marrakech') }}</h3>
                    </div>
                </div>


                <!-- 4. Salé -->
                <div class="site-card wide">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/sale.webp') }}" alt="GLS Salé" class="site-image">

                        <iframe
                            src="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&mute=1&controls=0&loop=1&playlist=GJx04L_KQLk&modestbranding=1&playsinline=1&start=1"
                            width="620" height="315" frameborder="0">
                        </iframe>
                    </div>

                    <div class="site-video-play">
                        <a class="play-btn"
                            data-video="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&controls=1&modestbranding=1&rel=0">
                            <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                            <svg viewBox="0 0 448 512">
                                <path
                                    d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
                            </svg>
                        </a>
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.sale') }}</h3>
                    </div>
                </div>


                <!-- 5. Agadir -->
                <div class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/agadir.avif') }}" alt="GLS Agadir" class="site-image">

                        <iframe
                            src="https://www.youtube.com/embed/MN6_-R2wvhY?autoplay=1&mute=1&controls=0&loop=1&playlist=MN6_-R2wvhY&modestbranding=1&playsinline=1&start=1"
                            width="560" height="315">
                        </iframe>
                    </div>

                    <div class="site-video-play">
                        <a class="play-btn"
                            data-video="https://www.youtube.com/embed/MN6_-R2wvhY?autoplay=1&controls=1&modestbranding=1&rel=0">
                            <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                            <svg viewBox="0 0 448 512">
                                <path
                                    d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
                            </svg>
                        </a>
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.agadir') }}</h3>
                    </div>
                </div>


                <!-- 6. Casablanca -->
                <div class="site-card small">
                    <div class="site-video-wrapper">
                        <img src="{{ asset('assets/images/sites/casablanca.jpg') }}" alt="GLS Casablanca"
                            class="site-image">

                        <iframe
                            src="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&mute=1&controls=0&loop=1&playlist=GJx04L_KQLk&modestbranding=1&playsinline=1&start=1"
                            width="560" height="315">
                        </iframe>
                    </div>

                    <div class="site-video-play">
                        <a class="play-btn"
                            data-video="https://www.youtube.com/embed/GJx04L_KQLk?autoplay=1&controls=1&modestbranding=1&rel=0">
                            <span class="ripple"></span><span class="ripple"></span><span class="ripple"></span>
                            <svg viewBox="0 0 448 512">
                                <path
                                    d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z" />
                            </svg>
                        </a>
                    </div>

                    <div class="site-overlay">
                        <h3>{{ __('gls.sites.casablanca') }}</h3>
                    </div>
                </div>

            </div>
        </section>

        {{-- ===========================
  REVIEWS SECTION
=========================== --}}
        <section class="reviews-carousel-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">

            <div class="container is-reviews-title-block reveal delay-2">

                {{-- Title --}}
                <h2 class="h-section-subtitle is-reviews reveal fade-blur-title delay-1">
                    {{ __('home.reviews.title') }}
                </h2>

                {{-- Rating block --}}
                <div class="div-block-29 w-inline-block reveal delay-3">

                    {{-- SVG Stars --}}
                    <div class="reveal delay-1">
                        @include('frontoffice.partials.svg-stars-big')
                    </div>

                    <div class="reveal delay-2"><strong>{{ __('home.reviews.rating_line') }}</strong></div>
                </div>
            </div>

            <div class="div-block-28 review-grid-layout reveal delay-3">

                {{-- Track 1 (Left) --}}
                <div class="review-carousel_track is-animating-left reveal delay-1">

                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-2">
                            <div class="review-stars reveal delay-3">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-1">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-2">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach

                    {{-- Duplicate --}}
                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-3">
                            <div class="review-stars reveal delay-1">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-2">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-3">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Track 2 (Right) --}}
                <div class="review-carousel_track is-alt is-animating-right reveal delay-1">

                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-2">
                            <div class="review-stars reveal delay-3">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-1">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-2">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach

                    @foreach (__('home.reviews.items') as $review)
                        <div class="review-block review-card-inspired reveal delay-3">
                            <div class="review-stars reveal delay-1">@include('frontoffice.partials.svg-stars')</div>
                            <div class="text-block-9 reveal delay-2">"{{ $review['text'] }}"</div>
                            <div class="text-block-10 reveal delay-3">– {{ $review['name'] }} ({{ $review['year'] }})
                            </div>
                        </div>
                    @endforeach

                </div>

            </div>
        </section>


        <script>
            document.addEventListener("DOMContentLoaded", () => {

                // When clicking any play button
                document.querySelectorAll(".play-btn").forEach(btn => {
                    btn.addEventListener("click", e => {
                        e.preventDefault();

                        const videoUrl = btn.getAttribute("data-video");
                        const autoplayUrl = videoUrl + "&autoplay=1";

                        const popup = document.getElementById("videoPopup");
                        const frame = document.getElementById("videoPopupFrame");

                        // Load video in iframe
                        frame.src = autoplayUrl;

                        // Show popup
                        popup.style.display = "flex";
                    });
                });

                // CLOSE BUTTON
                document.getElementById("videoPopupClose").addEventListener("click", () => {
                    closePopup();
                });

                // CLICK OUTSIDE TO CLOSE
                document.getElementById("videoPopup").addEventListener("click", e => {
                    if (e.target.id === "videoPopup") {
                        closePopup();
                    }
                });

                // FUNCTION: CLOSE POPUP
                function closePopup() {
                    const popup = document.getElementById("videoPopup");
                    const frame = document.getElementById("videoPopupFrame");

                    popup.style.display = "none";
                    frame.src = ""; // stop the video
                }
            });
        </script>


        <section class="home-courses-section section">

            {{-- 1. Banner Photo Block --}}
            <div class="container is-home-courses-photo">
                <h2 class="h-section-title">{{ __('home.courses.title') }}</h2>
            </div>

            {{-- 2. German Intensive Courses (A1-B2) --}}
            <div class="container is-h-courses">
                <h2 class="h-section-subtitle-courses">{{ __('home.courses.intensive.title') }}</h2>
                <div class="subtitle">{{ __('home.courses.intensive.subtitle') }}</div>
                <p class="paragraph-2">{{ __('home.courses.intensive.description') }}</p>

                <div class="courses-cards">

                    {{-- A1 --}}
                    <div class="course-card">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a1.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a1.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.a1.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.a1.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a1')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.a1.button') }}
                        </a>

                    </div>

                    {{-- A2 --}}
                    <div class="course-card is-green">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a2.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.a2.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.a2.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.a2.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a2')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.a2.button') }}
                        </a>

                    </div>

                    {{-- B1 --}}
                    <div class="course-card is-purple">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b1.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b1.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.b1.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.b1.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b1')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.b1.button') }}
                        </a>
                    </div>

                    {{-- B2 --}}
                    <div class="course-card is-yellow">
                        <div class="couse-card_level">
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b2.letter') }}</div>
                            <div class="course-card_level-circle">{{ __('home.courses.intensive.cards.b2.number') }}</div>
                        </div>
                        <h3 class="course-card_title">{!! __('home.courses.intensive.cards.b2.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.intensive.cards.b2.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b2')) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.intensive.cards.b2.button') }}
                        </a>
                    </div>

                </div>
            </div>

            {{-- 3. Online courses & exams --}}
            <div class="container is-h-courses">
                <h2 class="h-section-subtitle-courses">{{ __('home.courses.online.title') }}</h2>
                <div class="subtitle">{{ __('home.courses.online.subtitle') }}</div>

                <div class="courses-cards is-home-other-german-courses">

                    {{-- Online Courses --}}
                    <div class="course-card is-orange">
                        <h3 class="course-card_title is-others">{!! __('home.courses.online.cards.online.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.online.cards.online.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.online.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.online.button') }}
                        </a>
                    </div>

                    {{-- GLS Exam Preparation --}}
                    <div class="course-card is-green">
                        <h3 class="course-card_title is-others">{!! __('home.courses.online.cards.gls.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.online.cards.gls.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.gls.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.gls.button') }}
                        </a>
                    </div>

                    {{-- Goethe Exam Preparation --}}
                    <div class="course-card is-purple">
                        <h3 class="course-card_title is-others">{!! __('home.courses.online.cards.goethe.title') !!}</h3>
                        <div class="course-card_text">{!! __('home.courses.online.cards.goethe.text') !!}</div>
                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.goethe.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.goethe.button') }}
                        </a>
                    </div>

                    <div class="course-card is-purple is-others">
                        <h3 class="course-card_title is-others">
                            {!! __('home.courses.online.cards.goethe.title') !!}
                        </h3>

                        <div class="course-card_text">
                            {!! __('home.courses.online.cards.goethe.text') !!}
                        </div>

                        <a href="{{ LaravelLocalization::localizeUrl(route(__('home.courses.online.cards.goethe.route'))) }}"
                            class="button is-course-card w-button">
                            {{ __('home.courses.online.cards.goethe.button') }}
                        </a>
                    </div>

                </div>
            </div>

        </section>


        {{-- ===========================
 LEARN MORE SECTION
=========================== --}}
        <section class="learn-more-section py-5 text-light" style="background-color: var(--off-black);">
            <div class="container py-5">
                <div class="row align-items-center g-5">

                    {{-- Left Text Column --}}
                    <div class="col-lg-6">
                        <h2 class="fw-bold mb-4 learn-more-title">
                            {!! __('home.learn_more.title') !!}
                        </h2>

                        <p class="lead opacity-75 mb-0">
                            {!! __('home.learn_more.description') !!}
                        </p>
                    </div>

                    {{-- Right Cards Column --}}
                    <div class="col-lg-6">
                        <div class="row g-4">
                            @foreach (__('home.learn_more.cards') as $card)
                                <div class="col-md-6">
                                    <a href="{{ !empty($card['route']) ? route($card['route']) : $card['link'] ?? '#' }}"
                                        class="h-learn-more-card"
                                        @if (!empty($card['action'])) data-action="{{ $card['action'] }}"
                                    role="button"
                                    aria-haspopup="dialog" @endif>
                                        <div class="h-learn-more-card_icon">
                                            {!! $card['icon'] !!}
                                        </div>

                                        <div
                                            class="learn-card-bottom d-flex align-items-center justify-content-between w-100">
                                            <h3 class="fw-bold fs-4 mb-0">{!! $card['title'] !!}</h3>

                                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.84451 20L7.33722 17.5502L13.1778 11.799H0V8.20096H13.1778L7.33722 2.45933L9.84451 0L20 10L9.84451 20Z" />
                                            </svg>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            {{-- Site Selector Modal --}}

            <div class="modal fade gls-site-modal" id="groupsSiteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered gls-site-modal__dialog">
                    <div class="modal-content gls-site-modal__content">

                        <div class="gls-site-modal__header">
                            <div>
                                <div class="gls-site-modal__kicker">Nos centres</div>
                                <h5 class="gls-site-modal__title">Choisissez votre site</h5>
                            </div>

                            <button type="button" class="gls-site-modal__close" data-bs-dismiss="modal"
                                aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>

                        <div class="gls-site-modal__body">
                            <div class="gls-site-grid">
                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-marrakech') }}">Marrakech</a>

                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-casablanca') }}">Casablanca</a>

                                <a class="gls-site-pill" href="{{ route('front.sites.show', 'gls-rabat') }}">Rabat</a>

                                <a class="gls-site-pill"
                                    href="{{ route('front.sites.show', 'gls-kenitra') }}">Kénitra</a>

                                <a class="gls-site-pill" href="{{ route('front.sites.show', 'gls-sale') }}">Salé</a>

                                <a class="gls-site-pill" href="{{ route('front.sites.show', 'gls-agadir') }}">Agadir</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </section>


        {{-- ===========================
 ABOUT GLS MOROCCO – 9onsol’s Talks
=========================== --}}
        <section class="home-about-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
            <div class="container about-grid reveal delay-2">

                {{-- LEFT CARD --}}
                <div class="about-card text-light reveal delay-3">

                    <h2 class="h-section-subtitle mb-4 reveal fade-blur-title delay-1">
                        {!! __('home.9onsol.title') !!}
                    </h2>

                    <p class="lead mb-4 reveal delay-2">
                        {!! __('home.9onsol.description') !!}
                    </p>

                    <a href="https://www.youtube.com/@9onsolsTalks" target="_blank"
                        class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto reveal delay-3">
                        {{ __('home.9onsol.button') }}
                    </a>
                </div>

                {{-- VIDEO BLOCK --}}
                <div class="about-video reveal delay-1">
                    <iframe src="https://www.youtube.com/embed/wPYANoRURpU?si=p__Fgz2v7VuF_ubl"
                        title="9onsol’s Talks – GLS Morocco Podcast"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                        class="reveal delay-2"></iframe>
                </div>

            </div>
        </section>


        {{-- ===============================
 COOPERATION PARTNERS – Auto Marquee
================================ --}}
        <section class="partners-section text-center reveal delay-1" aria-label="Our Cooperation Partners">
            <div class="container reveal delay-2">

                <h2 class="partners-title reveal fade-blur-title delay-1">Our Cooperation Partners</h2>

                <div class="partners-marquee reveal delay-2">
                    <div class="partners-track reveal delay-3">

                        {{-- ——— Set A ——— --}}
                        <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut" loading="lazy">
                        <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC" loading="lazy">
                        <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam" loading="lazy">
                        <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                            alt="GIZ German Cooperation" loading="lazy">
                        <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification"
                            loading="lazy">
                        <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact"
                            loading="lazy">

                        {{-- ——— Set B ——— --}}
                        <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut" aria-hidden="true"
                            loading="lazy">
                        <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC" aria-hidden="true"
                            loading="lazy">
                        <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam" aria-hidden="true"
                            loading="lazy">
                        <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                            alt="GIZ German Cooperation" aria-hidden="true" loading="lazy">
                        <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification"
                            aria-hidden="true" loading="lazy">
                        <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact"
                            aria-hidden="true" loading="lazy">

                    </div>
                </div>

                <noscript>
                    <div class="partners-logos-noscript">
                        <img src="{{ asset('assets/images/home/goethe.png') }}" alt="Goethe-Institut">
                        <img src="{{ asset('assets/images/home/marokkofc.png') }}" alt="Marokko FC">
                        <img src="{{ asset('assets/images/home/osd.png') }}" alt="ÖSD Exam">
                        <img src="{{ asset('assets/images/home/gizlogo-unternehmen-de-rgb-300.webp') }}"
                            alt="GIZ German Cooperation">
                        <img src="{{ asset('assets/images/home/ECL_LOGO.png') }}" alt="ECL Language Certification">
                        <img src="{{ asset('assets/images/home/TLScontact_main.webp') }}" alt="TLScontact">
                    </div>
                </noscript>

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

      </main>
@endsection
