@extends('frontoffice.layouts.app')

@section('title', $quiz['title'] ?? 'Quiz')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/quiz/quiz.css') }}">

@section('content')

    @php
        $quiz = $quiz ?? ['title' => 'Quiz', 'subtitle' => null, 'questions' => []];
        $timeLimitSeconds = (int) ($quiz['time_limit_seconds'] ?? 0); // depuis DB
        $remainingSeconds = (int) ($quiz['remaining_seconds'] ?? $timeLimitSeconds); // depuis controller
    @endphp

    <section class="quiz-page">
        <div class="quiz-overlay" aria-hidden="true"></div>

        <div class="quiz-shell" data-quiz>
            <div class="quiz-topbar">
                <div class="quiz-brand">
                    <span class="quiz-brand_dot"></span>
                    <span class="quiz-brand_text">{{ $quiz['title'] ?? 'Quiz' }}</span>
                </div>

                {{-- TOOLS BAR --}}
                <div class="quiz-meta" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    {{-- ✅ GLOBAL TIMER (not per question) --}}
                    <span class="quiz-meta_label badge bg-danger" data-quiz-timer hidden aria-live="polite" style="font-size:0.95rem;padding:0.5rem 0.75rem;">
                        ⏱ {{ $remainingSeconds }}s
                    </span>

                    {{-- Fullscreen toggle --}}
                    <button type="button" class="quiz-btn" data-quiz-fullscreen aria-pressed="false" title="Plein écran">
                        Plein écran
                    </button>

                    {{-- Counter --}}
                    <span class="quiz-meta_label" data-quiz-counter></span>
                </div>
            </div>

            <div class="quiz-card" role="region" aria-label="Quiz">
                <div class="quiz-card_inner">

                    {{-- Start screen --}}
                    <div class="quiz-screen is-active" data-screen="start">

                        {{-- GLS HEADER (à cacher après start) --}}
                        <div class="quiz-hero" data-quiz-hero>
                            <img src="{{ asset('build/images/logo/gls-noir.png') }}" alt="GLS Sprachenzentrum"
                                class="quiz-hero_logo" loading="eager" />

                            <h1 class="quiz-hero_title">
                                Deutsch-Quiz {{ $quizLevel ?? 'A1' }}
                            </h1>

                            <p class="quiz-hero_subtitle">
                                Am Ende bekommst du ein Feedback zu deinen Antworten.<br>
                                <span>You will receive feedback on your answers.</span>
                            </p>
                        </div>

                        @if (!empty($quiz['subtitle']))
                            <p class="quiz-subtitle" data-quiz-subtitle>
                                {{ $quiz['subtitle'] }}
                            </p>
                        @endif

                        <div class="quiz-start_actions">
                            <button type="button" class="quiz-btn quiz-btn_primary" data-quiz-start>
                                Commencer
                            </button>
                        </div>
                    </div>

                    {{-- Question screen --}}
                    <div class="quiz-screen" data-screen="question">
                        <div class="quiz-q_head">
                            <div class="quiz-q_kicker" data-q-title></div>
                            <div class="quiz-q_prompt" data-q-prompt></div>
                        </div>

                        <div class="quiz-q_body">
                            <div class="quiz-q_text" data-q-question></div>

                            <div class="quiz-q_media" data-q-media hidden>
                                <img data-q-image alt="" loading="lazy" hidden>
                                <audio data-q-audio controls preload="none" hidden></audio>
                            </div>

                            <div class="quiz-answers" data-q-answers></div>
                        </div>
                    </div>

                    {{-- Result screen --}}
                    <div class="quiz-screen" data-screen="result">
                        <div class="quiz-result">
                            <img src="{{ asset('build/images/logo/gls-noir.png') }}" alt="GLS Sprachenzentrum"
                                class="quiz-result_logo" loading="lazy" />

                            <h2 class="quiz-result_title">
                                Herzlichen Glückwunsch!
                            </h2>

                            <p class="quiz-result_points">
                                Du hast <strong data-result-correct>0</strong> von <strong data-result-total>0</strong>
                                Fragen richtig beantwortet
                                (<strong data-result-percent>0</strong>%).
                            </p>

                            <p class="quiz-result_level">
                                <strong data-result-level>A1</strong> ist definitiv das richtige Level für dich!
                                <span class="quiz-result_level_sub">
                                    You can get started any day now. Our courses are only a click away.
                                </span>
                            </p>

                            <div class="quiz-result_actions">
                                <button type="button" class="quiz-btn" data-quiz-restart>
                                    Refaire le quiz
                                </button>

                                <a href="{{ route('front.pricing') }}" class="quiz-btn quiz-btn_primary">
                                    Voir les cours
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="quiz-nav">
                    <button type="button" class="quiz-nav_btn" data-quiz-prev disabled>
                        <span class="quiz-nav_arrow">←</span>
                        <span class="quiz-nav_text">PREVIOUS</span>
                    </button>

                    <button type="button" class="quiz-nav_btn is-right" data-quiz-next disabled>
                        <span class="quiz-nav_text">NEXT</span>
                        <span class="quiz-nav_arrow">→</span>
                    </button>
                </div>
            </div>

            <div class="quiz-progress" aria-label="Progression" data-quiz-progress></div>

            <form class="quiz-form" data-quiz-form hidden method="POST"
                action="{{ route('front.discover-your-level.answer') }}">
                @csrf
                <input type="hidden" name="quiz" value="{{ $quizLevel ?? 'A1' }}">
                <input type="hidden" name="answers_json" value="" data-answers-json>
            </form>

        </div>
    </section>

    {{-- ✅ variables globales (OK de rester ici) --}}
    <script>
        window.__QUIZ__ = @json($quiz);
        window.__QUIZ_TIMER__ = {
            enabled: true,
            secondsPerQuestion: {{ $timeLimitSeconds > 0 ? $timeLimitSeconds : 25 }},
            autoNextOnTimeout: true
        };
    </script>

    <script defer src="{{ asset('assets/js/quiz.js') }}"></script>
    <script defer src="{{ asset('assets/js/quiz-timer.js') }}"></script>

    {{-- ✅ NEW: moved script --}}
    <script defer src="{{ asset('assets/js/quiz-fullscreen.js') }}"></script>
    <style>
        /* ===============================
   FORCE TIMER CENTER (TOPBAR)
   (high specificity + !important)
================================ */

/* topbar devient le repère */
.quiz-shell .quiz-topbar {
  position: relative !important;
}

/* le timer est centré par rapport à la topbar */
.quiz-shell .quiz-topbar .quiz-meta [data-quiz-timer]{
  position: absolute !important;
  left: 50% !important;
  top: 50% !important;
  transform: translate(-50%, -50%) !important;

  z-index: 20 !important;

  /* look propre */
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;

  height: 38px !important;
  min-width: 96px !important;

  padding: 8px 14px !important;
  border-radius: 999px !important;

  background: #dc3545 !important;
  color: #fff !important;

  font-weight: 900 !important;
  letter-spacing: 0.03em !important;

  box-shadow: 0 12px 28px rgba(0,0,0,0.35) !important;
  border: 1px solid rgba(255,255,255,0.18) !important;
}

/* empêche le wrap de la meta de pousser visuellement le timer */
.quiz-shell .quiz-topbar .quiz-meta{
  flex-wrap: nowrap !important;
}

/* mobile: topbar en colonne => on repasse le timer en "normal" */
@media (max-width: 640px){
  .quiz-shell .quiz-topbar{
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .quiz-shell .quiz-topbar .quiz-meta [data-quiz-timer]{
    position: static !important;
    transform: none !important;
    margin: 0 auto !important;
  }

  .quiz-shell .quiz-topbar .quiz-meta{
    width: 100%;
    justify-content: space-between;
  }
}

    </style>
@endsection
