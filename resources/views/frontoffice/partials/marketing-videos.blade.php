<section class="gls-mv-section" aria-label="Vidéos GLS">
    <div class="gls-mv-wrap">
        <header class="gls-mv-header">
            <h2 class="gls-mv-title">
                @if (app()->getLocale() == 'fr')
                    NOS VIDÉOS
                @elseif (app()->getLocale() == 'ar')
                    فيديوهاتنا
                @else
                    OUR VIDEOS
                @endif
            </h2>
            <p class="gls-mv-subtitle">
                @if (app()->getLocale() == 'fr')
                    Découvrez GLS Sprachenzentrum en vidéo
                @elseif (app()->getLocale() == 'ar')
                    اكتشف GLS Sprachenzentrum عبر الفيديو
                @else
                    Discover GLS Sprachenzentrum in video
                @endif
            </p>
        </header>

        <div class="gls-mv-grid">

            {{-- Video 1 --}}
            <div class="gls-mv-card gls-mv-card--blue">
                <div class="gls-mv-video">
                    <iframe
                        src="https://player.vimeo.com/video/1172167791?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        loading="lazy"
                        title="arbeit"></iframe>
                </div>
                <div class="gls-mv-label">
                    <span class="gls-mv-dot"></span>
                    Arbeit
                </div>
            </div>

            {{-- Video 2 --}}
            <div class="gls-mv-card gls-mv-card--orange">
                <div class="gls-mv-video">
                    <iframe
                        src="https://player.vimeo.com/video/1172166709?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        loading="lazy"
                        title="1POD3"></iframe>
                </div>
                <div class="gls-mv-label">
                    <span class="gls-mv-dot"></span>
                    1POD3
                </div>
            </div>

            {{-- Video 3 --}}
            <div class="gls-mv-card gls-mv-card--green">
                <div class="gls-mv-video">
                    <iframe
                        src="https://player.vimeo.com/video/1172166445?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        loading="lazy"
                        title="Le Passeport GLS Témoignage"></iframe>
                </div>
                <div class="gls-mv-label">
                    <span class="gls-mv-dot"></span>
                    Le Passeport GLS
                </div>
            </div>

            {{-- Video 4 --}}
            <div class="gls-mv-card gls-mv-card--purple">
                <div class="gls-mv-video">
                    <iframe
                        src="https://player.vimeo.com/video/1172167181?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        loading="lazy"
                        title="final version 3"></iframe>
                </div>
                <div class="gls-mv-label">
                    <span class="gls-mv-dot"></span>
                    Final Version 3
                </div>
            </div>

            {{-- Video 5 --}}
            <div class="gls-mv-card gls-mv-card--blue">
                <div class="gls-mv-video">
                    <iframe
                        src="https://player.vimeo.com/video/1172171254?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        loading="lazy"
                        title="Final_hq_1"></iframe>
                </div>
                <div class="gls-mv-label">
                    <span class="gls-mv-dot"></span>
                    Final HQ
                </div>
            </div>

        </div>
    </div>
</section>

<link rel="stylesheet" href="{{ asset('assets/css/marketing-videos.css') }}">
