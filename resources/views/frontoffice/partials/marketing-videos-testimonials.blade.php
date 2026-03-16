<section class="gls-tmv-section reveal delay-1" aria-label="{{ __('home.testimonials_videos.aria_section') }}">
    <div class="gls-tmv-wrap reveal delay-2">
        <header class="gls-tmv-header reveal delay-3">
            <h2 class="gls-tmv-title reveal fade-blur-title delay-1">
                {{ __('home.testimonials_videos.title') }}
            </h2>
            <p class="gls-tmv-subtitle reveal delay-2">
                {{ __('home.testimonials_videos.subtitle') }}
            </p>
        </header>

        <div class="gls-tmv-carousel reveal delay-1">
            <button class="gls-tmv-nav gls-tmv-nav--prev" type="button" aria-label="Previous" hidden>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>

            <div class="gls-tmv-viewport">
                <div class="gls-tmv-track">

                    {{-- Testimonial 1 — Yassine Safine --}}
                    <div class="gls-tmv-card gls-tmv-card--blue reveal delay-1">
                        <div class="gls-tmv-video reveal delay-2">
                            <iframe
                                src="https://player.vimeo.com/video/1172183039?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                                allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                                title="Von GLS nach Deutschland — Yassine Safine Geschichte"></iframe>
                        </div>
                        <div class="gls-tmv-label reveal delay-3">
                            <span class="gls-tmv-dot"></span>
                            Yassine Safine
                        </div>
                    </div>

                    {{-- Testimonial 2 — Mohamed Amine --}}
                    <div class="gls-tmv-card gls-tmv-card--orange reveal delay-2">
                        <div class="gls-tmv-video reveal delay-3">
                            <iframe
                                src="https://player.vimeo.com/video/1172183086?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                                allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                                title="Von GLS nach Deutschland — Mohamed Amine Geschichte"></iframe>
                        </div>
                        <div class="gls-tmv-label reveal delay-1">
                            <span class="gls-tmv-dot"></span>
                            Mohamed Amine
                        </div>
                    </div>

                    {{-- Testimonial 3 — Oumaima --}}
                    <div class="gls-tmv-card gls-tmv-card--green reveal delay-3">
                        <div class="gls-tmv-video reveal delay-1">
                            <iframe
                                src="https://player.vimeo.com/video/1172182987?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                                allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                                title="Von GLS nach Deutschland — Oumaima Geschichte"></iframe>
                        </div>
                        <div class="gls-tmv-label reveal delay-2">
                            <span class="gls-tmv-dot"></span>
                            Oumaima
                        </div>
                    </div>

                    {{-- Testimonial 4 — Wiam --}}
                    <div class="gls-tmv-card gls-tmv-card--purple reveal delay-1">
                        <div class="gls-tmv-video reveal delay-2">
                            <iframe
                                src="https://player.vimeo.com/video/1172182943?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                                allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                                title="Von GLS nach Deutschland — Wiam Geschichte"></iframe>
                        </div>
                        <div class="gls-tmv-label reveal delay-3">
                            <span class="gls-tmv-dot"></span>
                            Wiam
                        </div>
                    </div>

                </div>
            </div>

            <button class="gls-tmv-nav gls-tmv-nav--next" type="button" aria-label="Next" hidden>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <div class="gls-tmv-dots reveal delay-3"></div>

    </div>
</section>

<link rel="stylesheet" href="{{ asset('assets/css/marketing-videos-testimonials.css') }}?v={{ filemtime(public_path('assets/css/marketing-videos-testimonials.css')) }}">
<script defer src="{{ asset('assets/js/marketing-videos-testimonials.js') }}?v={{ filemtime(public_path('assets/js/marketing-videos-testimonials.js')) }}"></script>
