document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('[data-quiz]');
    if (!shell) return;

    const cfg = window.__QUIZ_TIMER__ || {};
    if (!cfg.enabled) return;

    const timerLabel = shell.querySelector('[data-quiz-timer]');
    const btnNext = shell.querySelector('[data-quiz-next]');
    const screenQuestion = shell.querySelector('[data-screen="question"]');
    const screenResult = shell.querySelector('[data-screen="result"]');
    const screenStart = shell.querySelector('[data-screen="start"]');

    let timerId = null;
    let remaining = cfg.secondsPerQuestion || 25;

    const format = s =>
        String(Math.floor(s / 60)).padStart(2, '0') +
        ':' +
        String(s % 60).padStart(2, '0');

    function stop() {
        if (timerId) clearInterval(timerId);
        timerId = null;
    }

    function start() {
        stop();
        remaining = cfg.secondsPerQuestion || 25;
        timerLabel.hidden = false;
        timerLabel.textContent = '⏱ ' + format(remaining);

        timerId = setInterval(() => {
            remaining--;
            timerLabel.textContent = '⏱ ' + format(remaining);

            if (remaining <= 0) {
                stop();
                if (cfg.autoNextOnTimeout && btnNext && !btnNext.disabled) {
                    btnNext.click();
                }
            }
        }, 1000);
    }

    const observer = new MutationObserver(() => {
        if (screenQuestion.classList.contains('is-active')) {
            start();
        }
        if (
            screenResult.classList.contains('is-active') ||
            screenStart.classList.contains('is-active')
        ) {
            stop();
            timerLabel.hidden = true;
        }
    });

    observer.observe(shell, {
        attributes: true,
        subtree: true,
        attributeFilter: ['class'],
    });
});
