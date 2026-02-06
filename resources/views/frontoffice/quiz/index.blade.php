@extends('frontoffice.layouts.app')

@section('title', $quiz['title'] ?? 'Quiz')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/quiz/quiz.css') }}">

@section('content')

    @php
        $quiz = $quiz ?? ['title' => 'Quiz', 'subtitle' => null, 'questions' => []];
        $timeLimitSeconds = (int) ($quiz['time_limit_seconds'] ?? 0); // depuis DB
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
                    {{-- Chrono --}}
                    <span class="quiz-meta_label" data-quiz-timer hidden aria-live="polite"></span>

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
    <script>
        window.__QUIZ__ = @json($quiz);

        // Durée par question depuis DB (fallback 25s)
        window.__QUIZ_TIMER__ = {
            enabled: true,
            secondsPerQuestion: {{ $timeLimitSeconds > 0 ? $timeLimitSeconds : 25 }},
            autoNextOnTimeout: true
        };

        document.addEventListener('DOMContentLoaded', function() {
            const $shell = document.querySelector('[data-quiz]');
            if (!$shell) return;

            const $screenStart = $shell.querySelector('[data-screen="start"]');
            const $screenQuestion = $shell.querySelector('[data-screen="question"]');
            const $screenResult = $shell.querySelector('[data-screen="result"]');

            const $btnStart = $shell.querySelector('[data-quiz-start]');
            const $btnNext = $shell.querySelector('[data-quiz-next]');
            const $btnPrev = $shell.querySelector('[data-quiz-prev]');
            const $btnRestart = $shell.querySelector('[data-quiz-restart]');

            const $timerLabel = $shell.querySelector('[data-quiz-timer]');
            const $counterLabel = $shell.querySelector('[data-quiz-counter]');

            const $btnFullscreen = $shell.querySelector('[data-quiz-fullscreen]');

            const cfg = window.__QUIZ_TIMER__ || {
                enabled: false,
                secondsPerQuestion: 25,
                autoNextOnTimeout: true
            };

            let timerId = null;
            let remaining = cfg.secondsPerQuestion || 25;

            function isQuestionActive() {
                return $screenQuestion && $screenQuestion.classList.contains('is-active');
            }

            function formatTime(s) {
                s = Math.max(0, parseInt(s, 10) || 0);
                const m = Math.floor(s / 60);
                const r = s % 60;
                return String(m).padStart(2, '0') + ':' + String(r).padStart(2, '0');
            }

            function renderTimer() {
                if (!$timerLabel) return;
                $timerLabel.textContent = '⏱ ' + formatTime(remaining);
            }

            function showTimer(show) {
                if (!$timerLabel) return;
                $timerLabel.hidden = !show;
            }

            function stopTimer() {
                if (timerId) window.clearInterval(timerId);
                timerId = null;
            }

            function startTimer() {
                if (!cfg.enabled) return;
                stopTimer();
                remaining = cfg.secondsPerQuestion || 25;
                showTimer(true);
                renderTimer();

                timerId = window.setInterval(function() {
                    if (!isQuestionActive()) return;

                    remaining -= 1;
                    renderTimer();

                    if (remaining <= 0) {
                        stopTimer();
                        // Timeout: passe NEXT si possible
                        if (cfg.autoNextOnTimeout && $btnNext && !$btnNext.disabled) {
                            $btnNext.click();
                        }
                    }
                }, 1000);
            }

            // Reset chrono quand la question change:
            // On observe le texte de la question + compteur
            const $qText = $shell.querySelector('[data-q-question]');
            const observer = new MutationObserver(function() {
                if (isQuestionActive()) startTimer();
            });

            if ($qText) observer.observe($qText, {
                childList: true,
                subtree: true,
                characterData: true
            });
            if ($counterLabel) observer.observe($counterLabel, {
                childList: true,
                subtree: true,
                characterData: true
            });

            // Quand on arrive sur result => stop/hide
            function syncByScreen() {
                if ($screenResult && $screenResult.classList.contains('is-active')) {
                    stopTimer();
                    showTimer(false);
                }
                if ($screenStart && $screenStart.classList.contains('is-active')) {
                    stopTimer();
                    showTimer(false);
                }
                if (isQuestionActive()) {
                    startTimer();
                }
            }

            // Observe changement d’écran (is-active)
            const screenObserver = new MutationObserver(syncByScreen);
            if ($screenStart) screenObserver.observe($screenStart, {
                attributes: true,
                attributeFilter: ['class']
            });
            if ($screenQuestion) screenObserver.observe($screenQuestion, {
                attributes: true,
                attributeFilter: ['class']
            });
            if ($screenResult) screenObserver.observe($screenResult, {
                attributes: true,
                attributeFilter: ['class']
            });

            // Zoom au clic “Commencer” (simple, sans toucher quiz.css)
            if ($btnStart) {
                $btnStart.addEventListener('click', function() {
                    $shell.classList.add('is-starting');
                    $shell.style.transition = 'transform 420ms ease, filter 420ms ease';
                    $shell.style.transformOrigin = '50% 50%';
                    $shell.style.transform = 'scale(1.03)';
                    $shell.style.filter = 'saturate(1.05)';

                    window.setTimeout(function() {
                        $shell.style.transform = '';
                        $shell.style.filter = '';
                    }, 520);

                    // Timer démarre quand l’écran question devient actif (observer), mais on déclenche aussi un sync
                    window.setTimeout(syncByScreen, 50);
                });
            }

            // Fullscreen toggle
            function isFullscreen() {
                return !!document.fullscreenElement;
            }

            async function enterFullscreen() {
                // le plus stable: fullscreen sur la carte (pas tout le body)
                const target = $shell.querySelector('.quiz-card') || $shell;
                if (!target.requestFullscreen) return;
                await target.requestFullscreen();
            }

            async function exitFullscreen() {
                if (document.exitFullscreen) await document.exitFullscreen();
            }

            function syncFullscreenUI() {
                if (!$btnFullscreen) return;
                const fs = isFullscreen();
                $btnFullscreen.setAttribute('aria-pressed', fs ? 'true' : 'false');
                $btnFullscreen.textContent = fs ? 'Quitter plein écran' : 'Plein écran';
            }

            if ($btnFullscreen) {
                $btnFullscreen.addEventListener('click', async function() {
                    try {
                        if (isFullscreen()) await exitFullscreen();
                        else await enterFullscreen();
                    } catch (e) {}
                    syncFullscreenUI();
                });

                document.addEventListener('fullscreenchange', syncFullscreenUI);
                syncFullscreenUI();
            }

            // Stop timer sur restart (au cas où)
            if ($btnRestart) {
                $btnRestart.addEventListener('click', function() {
                    stopTimer();
                    showTimer(false);
                });
            }

            // NEXT/PREV => relance timer (si question active)
            if ($btnNext) $btnNext.addEventListener('click', function() {
                window.setTimeout(function() {
                    if (isQuestionActive()) startTimer();
                }, 0);
            });

            if ($btnPrev) $btnPrev.addEventListener('click', function() {
                window.setTimeout(function() {
                    if (isQuestionActive()) startTimer();
                }, 0);
            });

            // init
            syncByScreen();
        });
    </script>

    <script defer src="{{ asset('assets/js/quiz.js') }}"></script>
    <script defer src="{{ asset('assets/js/quiz-tools.js') }}"></script>
@endsection
