@php
    $centerName = $centerName ?? 'GLS Sprachenzentrum';
    $centerPhoneE164 = $centerPhoneE164 ?? '212600000000';
    $waType = $waType ?? 'center';
    $whatsAppText = "Bonjour, je souhaite des informations sur les cours d'allemand à {$centerName}.";
    $waLink = "https://wa.me/{$centerPhoneE164}?text=" . urlencode($whatsAppText);

    // Build chat step arrays from translations (inject :center placeholder)
    $chatCenter = collect(__('bookwhatsapp.chat.center'))
        ->map(function ($s) use ($centerName) {
            $s['html'] = str_replace(':center', $centerName, $s['html']);
            return $s;
        })
        ->values()
        ->toArray();

    $chatOnline = collect(__('bookwhatsapp.chat.online'))
        ->map(function ($s) use ($centerName) {
            $s['html'] = str_replace(':center', $centerName, $s['html']);
            return $s;
        })
        ->values()
        ->toArray();
@endphp

<section class="gls-wa-support" data-gls-wa data-wa-type="{{ $waType }}" data-center-name="{{ $centerName }}"
    data-chat-center='@json($chatCenter)' data-chat-online='@json($chatOnline)'
    data-end-label="{{ __('bookwhatsapp.end') }}">
    <div class="gls-wa-container">

        <!-- LEFT -->
        <div class="gls-wa-left">
            <div class="gls-wa-badge">
                <span class="gls-wa-dot" aria-hidden="true"></span>
                <span>{{ __('bookwhatsapp.badge') }}</span>
            </div>

            <h2 class="gls-wa-title">{{ __('bookwhatsapp.title') }}</h2>

            <p class="gls-wa-sub">
                {{ __('bookwhatsapp.subtitle') }}
            </p>

            <div class="gls-wa-tabs" aria-label="Catégories support">
                <button type="button" class="gls-wa-chip is-active">{{ __('bookwhatsapp.tabs.agent') }}</button>
                <button type="button" class="gls-wa-chip">{{ __('bookwhatsapp.tabs.registration') }}</button>
                <button type="button" class="gls-wa-chip">{{ __('bookwhatsapp.tabs.levels') }}</button>
                <button type="button" class="gls-wa-chip">{{ __('bookwhatsapp.tabs.tech') }}</button>
            </div>

            <ul class="gls-wa-list">
                <li><span class="gls-wa-bullet" aria-hidden="true"></span>{{ __('bookwhatsapp.list.item1') }}</li>
                <li><span class="gls-wa-bullet" aria-hidden="true"></span>{{ __('bookwhatsapp.list.item2') }}</li>
                <li><span class="gls-wa-bullet" aria-hidden="true"></span>{{ __('bookwhatsapp.list.item3') }}</li>
            </ul>

            <a class="gls-wa-btn" href="{{ $waLink }}" target="_blank" rel="noopener">
                <span class="gls-wa-btn-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor"
                            d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.33 4.97L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2Zm5.77 14.2c-.24.68-1.4 1.3-1.92 1.37-.48.07-1.09.1-1.76-.11-.41-.13-.93-.3-1.6-.59-2.81-1.21-4.64-4.2-4.78-4.39-.14-.19-1.14-1.52-1.14-2.89 0-1.37.72-2.04.97-2.32.25-.28.54-.35.72-.35h.52c.17 0 .4-.06.62.47.24.56.82 2.01.89 2.16.07.15.12.33.02.52-.1.2-.15.33-.3.5-.15.17-.32.38-.45.51-.15.15-.31.31-.13.6.18.29.82 1.35 1.76 2.18 1.21 1.08 2.24 1.41 2.53 1.58.29.17.46.15.63-.09.17-.24.72-.84.91-1.13.19-.29.38-.24.64-.15.26.09 1.66.78 1.94.92.28.14.47.2.54.31.07.11.07.65-.17 1.33Z" />
                    </svg>
                </span>
                <span>{{ __('bookwhatsapp.cta', ['center' => $centerName]) }}</span>
            </a>

            {{-- <p class="gls-wa-tip">{{ __('bookwhatsapp.tip') }}</p> --}}
        </div>

        <!-- RIGHT PHONE -->
        <div class="gls-wa-phone" aria-hidden="true">
            <div class="gls-wa-phone-inner">
                <div class="gls-wa-topdots" aria-hidden="true">
                    <span></span><span></span><span></span>
                </div>
                <div class="gls-wa-time" data-time>21:54</div>
                <div class="gls-wa-chat" data-chat></div>
            </div>
        </div>

        <!-- SVG WAVE -->
        <svg class="gls-wa-wave" viewBox="0 0 1440 320" preserveAspectRatio="none" aria-hidden="true">
            <path fill="none" stroke="rgba(255,254,232,.22)" stroke-width="2"
                d="M0,160 C240,200 480,120 720,160 C960,200 1200,120 1440,160">
                <animate attributeName="d" dur="8s" repeatCount="indefinite"
                    values="
          M0,160 C240,200 480,120 720,160 C960,200 1200,120 1440,160;
          M0,180 C240,140 480,200 720,150 C960,120 1200,200 1440,150;
          M0,160 C240,200 480,120 720,160 C960,200 1200,120 1440,160" />
            </path>
        </svg>

        <!-- BLOBS -->
        <span class="gls-blob gls-blob-a" aria-hidden="true"></span>
        <span class="gls-blob gls-blob-b" aria-hidden="true"></span>
        <span class="gls-blob gls-blob-c" aria-hidden="true"></span>

        <!-- mouse glow + bubbles layer -->
        <span class="gls-mouse-glow" aria-hidden="true"></span>
        <div class="gls-mouse-bubbles" aria-hidden="true"></div>

    </div>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/sites/bookonwhatsapp.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/bookonwhatsapp.js') }}" defer></script>
@endpush
