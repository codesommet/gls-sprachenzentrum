@extends('frontoffice.layouts.app')

@section('title', __('partners/fc_marokko.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/partners/fc-marokko.css') }}">

@section('content')

    <section class="hero-section section blog-hero-section blog-hero-margin reveal delay-1 fc-marokko-hero">
        <div class="fc-marokko-hero-bg"
            style="background-image:url('https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg');">
        </div>
        <div class="fc-marokko-hero-overlay"></div>

        <div class="container reveal delay-2">
            <div class="blog-hero-inner reveal delay-3">

                <div class="blog-hero-badge reveal delay-1">{{ __('partners/fc_marokko.hero.badge') }}</div>

                <h1 class="blog-hero-title fade-blur-title reveal delay-2">
                    {{ __('partners/fc_marokko.hero.title') }}
                </h1>

                <p class="blog-hero-subtitle reveal delay-3">
                    {{ __('partners/fc_marokko.hero.subtitle') }}
                </p>

                <div class="blog-hero-meta reveal delay-1">
                    <span>{{ __('partners/fc_marokko.hero.meta') }}</span>
                </div>

            </div>
        </div>
    </section>

    <section class="section reveal delay-1">
        <div class="container reveal delay-2">

            <div class="row g-4 align-items-center">

                <div class="col-12 col-lg-5 reveal delay-1">
                    <div class="card p-4 border-0 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://fc-marokko.de/storage/2022/10/fcm-logo.png"
                                alt="{{ __('partners/fc_marokko.hero.alt') }}"
                                style="width: 84px; height: 84px; object-fit: contain;" loading="lazy">
                            <div>
                                <div class="small text-muted">{{ __('partners/fc_marokko.section1.kicker') }}</div>
                                <h2 class="h4 mb-1">{{ __('partners/fc_marokko.section1.title') }}</h2>
                                <div class="text-muted">{{ __('partners/fc_marokko.section1.subtitle') }}</div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="https://fc-marokko.de/" target="_blank" rel="noopener" class="btn btn-white">
                                {{ __('partners/fc_marokko.section1.button') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7 reveal delay-2">
                    <div class="rich-text w-richtext">
                        <p class="reveal delay-1">{!! __('partners/fc_marokko.content.p1') !!}</p>
                        <p class="reveal delay-2">{!! __('partners/fc_marokko.content.p2') !!}</p>
                        <p class="reveal delay-3">{!! __('partners/fc_marokko.content.p3') !!}</p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <section class="section reveal delay-1">
        <div class="container reveal delay-2">

            <div class="row g-4">

                <div class="col-12 col-md-4 reveal delay-1">
                    <div class="card h-100 p-4 border-0 shadow-sm fc-info-card">
                        <div class="fc-card-head">
                            <div class="fc-card-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <div class="small text-muted mb-2">{{ __('partners/fc_marokko.cards.c1_kicker') }}</div>
                                <h3 class="h5 mb-2">{{ __('partners/fc_marokko.cards.c1_title') }}</h3>
                            </div>
                        </div>
                        <p class="mb-0 text-muted">{!! __('partners/fc_marokko.cards.c1_text') !!}</p>
                    </div>
                </div>

                <div class="col-12 col-md-4 reveal delay-2">
                    <div class="card h-100 p-4 border-0 shadow-sm fc-info-card">
                        <div class="fc-card-head">
                            <div class="fc-card-icon">
                                <i class="bi bi-bullseye"></i>
                            </div>
                            <div>
                                <div class="small text-muted mb-2">{{ __('partners/fc_marokko.cards.c2_kicker') }}</div>
                                <h3 class="h5 mb-2">{{ __('partners/fc_marokko.cards.c2_title') }}</h3>
                            </div>
                        </div>
                        <p class="mb-0 text-muted">{!! __('partners/fc_marokko.cards.c2_text') !!}</p>
                    </div>
                </div>

                <div class="col-12 col-md-4 reveal delay-3">
                    <div class="card h-100 p-4 border-0 shadow-sm fc-info-card">
                        <div class="fc-card-head">
                            <div class="fc-card-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <div class="small text-muted mb-2">{{ __('partners/fc_marokko.cards.c3_kicker') }}</div>
                                <h3 class="h5 mb-2">{{ __('partners/fc_marokko.cards.c3_title') }}</h3>
                            </div>
                        </div>
                        <p class="mb-0 text-muted">{!! __('partners/fc_marokko.cards.c3_text') !!}</p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <section class="section reveal delay-1">
        <div class="container reveal delay-2">

            <div class="row align-items-end mb-4">
                <div class="col-12 col-lg-8">
                    <h2 class="h3 mb-2">{{ __('partners/fc_marokko.gallery.title') }}</h2>
                    <p class="text-muted mb-0">{!! __('partners/fc_marokko.gallery.subtitle') !!}</p>
                </div>
            </div>

            <div class="row g-3 fc-gallery">

                <div class="col-12 col-md-7">
                    <a class="fc-gallery-item"
                        href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                        target="_blank" rel="noopener">
                        <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                            alt="FC Marokko" loading="lazy">
                    </a>
                </div>

                <div class="col-12 col-md-5">
                    <div class="row g-3">
                        <div class="col-6 col-md-12">
                            <a class="fc-gallery-item" href="https://fc-marokko.de/storage/2022/10/fcm-logo.png"
                                target="_blank" rel="noopener">
                                <img src="https://fc-marokko.de/storage/2022/10/fcm-logo.png" alt="FC Marokko"
                                    loading="lazy">
                            </a>
                        </div>

                        <div class="col-6 col-md-12">
                            <a class="fc-gallery-item"
                                href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                                target="_blank" rel="noopener">
                                <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                                    alt="FC Marokko" loading="lazy">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <a class="fc-gallery-item"
                        href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                        target="_blank" rel="noopener">
                        <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                            alt="FC Marokko" loading="lazy">
                    </a>
                </div>

                <div class="col-6 col-md-4">
                    <a class="fc-gallery-item"
                        href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                        target="_blank" rel="noopener">
                        <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                            alt="FC Marokko" loading="lazy">
                    </a>
                </div>

                <div class="col-12 col-md-4">
                    <a class="fc-gallery-item"
                        href="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                        target="_blank" rel="noopener">
                        <img src="https://fc-marokko.de/storage/2025/02/475873194_1137954098118088_822180009608469424_n.jpg"
                            alt="FC Marokko" loading="lazy">
                    </a>
                </div>

            </div>

        </div>
    </section>

    <section class="section reveal delay-1">
    <div class="container reveal delay-2">

        <div class="card p-4 p-md-5 border-0 shadow-sm fc-cta">
            <div class="fc-cta-content text-center mx-auto">

                <h2 class="fc-cta-title mb-3">
                    {{ __('partners/fc_marokko.cta.title') }}
                </h2>

                <p class="fc-cta-text mb-4">
                    {!! __('partners/fc_marokko.cta.text') !!}
                </p>

                <div class="fc-cta-action justify-content-center">
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}"
                       class="btn btn-dark fc-cta-btn">
                        {{ __('partners/fc_marokko.cta.button') }}
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>


@endsection
