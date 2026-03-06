/**
 * Quiz Fullscreen — Toggle fullscreen mode + start animation
 * Fullscreens the entire quiz-shell so toolbar/navigator/timer stay visible
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var $shell = document.querySelector('[data-quiz]');
        if (!$shell) return;

        var $btnStart = $shell.querySelector('[data-quiz-start]');
        var $btnFullscreen = $shell.querySelector('[data-quiz-fullscreen]');

        // Zoom animation on start click
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
            });
        }

        function isFullscreen() {
            return !!document.fullscreenElement;
        }

        async function enterFullscreen() {
            // Fullscreen the entire shell so topbar + navigator + toolbar stay visible
            if (!$shell.requestFullscreen) return;
            await $shell.requestFullscreen();
        }

        async function exitFullscreen() {
            if (document.exitFullscreen) await document.exitFullscreen();
        }

        function syncFullscreenUI() {
            if (!$btnFullscreen) return;
            var fs = isFullscreen();
            $btnFullscreen.setAttribute('aria-pressed', fs ? 'true' : 'false');
            // Update icon: show exit icon when in fullscreen
            $btnFullscreen.innerHTML = fs
                ? '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2v4H2M10 2v4h4M2 10h4v4M14 10h-4v4"/></svg>'
                : '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 6V2h4M10 2h4v4M14 10v4h-4M6 14H2v-4"/></svg>';
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
    });
})();
