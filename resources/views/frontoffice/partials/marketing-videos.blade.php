<section class="gls-mv-section reveal delay-1" aria-label="Vidéos GLS">
    <div class="gls-mv-wrap reveal delay-2">
        <header class="gls-mv-header reveal delay-3">
            <h2 class="gls-mv-title reveal fade-blur-title delay-1">
                @if (app()->getLocale() == 'fr')
                    NOS VIDÉOS
                @elseif (app()->getLocale() == 'ar')
                    فيديوهاتنا
                @else
                    OUR VIDEOS
                @endif
            </h2>
            <p class="gls-mv-subtitle reveal delay-2">
                @if (app()->getLocale() == 'fr')
                    Découvrez GLS Sprachenzentrum en vidéo
                @elseif (app()->getLocale() == 'ar')
                    اكتشف GLS Sprachenzentrum عبر الفيديو
                @else
                    Discover GLS Sprachenzentrum in video
                @endif
            </p>
        </header>

        <div class="gls-mv-grid reveal delay-1">

            {{-- Video 1 --}}
            <div class="gls-mv-card gls-mv-card--blue reveal delay-1">
                <div class="gls-mv-video reveal delay-2">
                    <iframe
                        src="https://player.vimeo.com/video/1172167791?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                        title="arbeit"></iframe>
                </div>
                <div class="gls-mv-label reveal delay-3">
                    <span class="gls-mv-dot"></span>
                    Arbeit
                </div>
            </div>

            {{-- Video 2 --}}
            <div class="gls-mv-card gls-mv-card--orange reveal delay-2">
                <div class="gls-mv-video reveal delay-3">
                    <iframe
                        src="https://player.vimeo.com/video/1172166709?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                        title="1POD3"></iframe>
                </div>
                <div class="gls-mv-label reveal delay-1">
                    <span class="gls-mv-dot"></span>
                    1POD3
                </div>
            </div>

            {{-- Video 3 --}}
            <div class="gls-mv-card gls-mv-card--green reveal delay-3">
                <div class="gls-mv-video reveal delay-1">
                    <iframe
                        src="https://player.vimeo.com/video/1172166445?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                        title="Le Passeport GLS Témoignage"></iframe>
                </div>
                <div class="gls-mv-label reveal delay-2">
                    <span class="gls-mv-dot"></span>
                    Le Passeport GLS
                </div>
            </div>

            {{-- Video 4 --}}
            <div class="gls-mv-card gls-mv-card--purple reveal delay-1">
                <div class="gls-mv-video reveal delay-2">
                    <iframe
                        src="https://player.vimeo.com/video/1172167181?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                        title="final version 3"></iframe>
                </div>
                <div class="gls-mv-label reveal delay-3">
                    <span class="gls-mv-dot"></span>
                    Final Version 3
                </div>
            </div>

            {{-- Video 5 --}}
            <div class="gls-mv-card gls-mv-card--blue reveal delay-2">
                <div class="gls-mv-video reveal delay-3">
                    <iframe
                        src="https://player.vimeo.com/video/1172171254?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                        allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"
                        title="Final_hq_1"></iframe>
                </div>
                <div class="gls-mv-label reveal delay-1">
                    <span class="gls-mv-dot"></span>
                    Final HQ
                </div>
            </div>

        </div>
    </div>
</section>

<link rel="stylesheet" href="{{ asset('assets/css/marketing-videos.css') }}">
