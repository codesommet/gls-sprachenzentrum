(function () {
    document.addEventListener('DOMContentLoaded', function () {
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

            timerId = window.setInterval(function () {
                if (!isQuestionActive()) return;

                remaining -= 1;
                renderTimer();

                if (remaining <= 0) {
                    stopTimer();
                    if (cfg.autoNextOnTimeout && $btnNext && !$btnNext.disabled) {
                        $btnNext.click();
                    }
                }
            }, 1000);
        }

        // Reset chrono quand la question change:
        const $qText = $shell.querySelector('[data-q-question]');
        const observer = new MutationObserver(function () {
            if (isQuestionActive()) startTimer();
        });

        if ($qText) observer.observe($qText, { childList: true, subtree: true, characterData: true });
        if ($counterLabel) observer.observe($counterLabel, { childList: true, subtree: true, characterData: true });

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
        if ($screenStart) screenObserver.observe($screenStart, { attributes: true, attributeFilter: ['class'] });
        if ($screenQuestion) screenObserver.observe($screenQuestion, { attributes: true, attributeFilter: ['class'] });
        if ($screenResult) screenObserver.observe($screenResult, { attributes: true, attributeFilter: ['class'] });

        // Zoom au clic “Commencer” (sans toucher quiz.css)
        if ($btnStart) {
            $btnStart.addEventListener('click', function () {
                $shell.classList.add('is-starting');
                $shell.style.transition = 'transform 420ms ease, filter 420ms ease';
                $shell.style.transformOrigin = '50% 50%';
                $shell.style.transform = 'scale(1.03)';
                $shell.style.filter = 'saturate(1.05)';

                window.setTimeout(function () {
                    $shell.style.transform = '';
                    $shell.style.filter = '';
                }, 520);

                window.setTimeout(syncByScreen, 50);
            });
        }

        // Fullscreen toggle
        function isFullscreen() {
            return !!document.fullscreenElement;
        }

        async function enterFullscreen() {
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
            $btnFullscreen.addEventListener('click', async function () {
                try {
                    if (isFullscreen()) await exitFullscreen();
                    else await enterFullscreen();
                } catch (e) {}
                syncFullscreenUI();
            });

            document.addEventListener('fullscreenchange', syncFullscreenUI);
            syncFullscreenUI();
        }

        // Stop timer sur restart
        if ($btnRestart) {
            $btnRestart.addEventListener('click', function () {
                stopTimer();
                showTimer(false);
            });
        }

        // NEXT/PREV => relance timer
        if ($btnNext) $btnNext.addEventListener('click', function () {
            window.setTimeout(function () {
                if (isQuestionActive()) startTimer();
            }, 0);
        });

        if ($btnPrev) $btnPrev.addEventListener('click', function () {
            window.setTimeout(function () {
                if (isQuestionActive()) startTimer();
            }, 0);
        });

        // init
        syncByScreen();
    });
})();
