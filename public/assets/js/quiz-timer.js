/**
 * Quiz Timer — Per-question countdown
 * Resets on each new question. Auto-advances on timeout.
 */
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
    const counterLabel = shell.querySelector('[data-quiz-counter]');

    let timerId = null;
    let deadline = 0;       // timestamp when time runs out
    let lastCounter = '';

    const format = s => {
        s = Math.max(0, s);
        return String(Math.floor(s / 60)).padStart(2, '0') +
            ':' +
            String(s % 60).padStart(2, '0');
    };

    function stop() {
        if (timerId) clearInterval(timerId);
        timerId = null;
    }

    function getRemainingSeconds() {
        return Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
    }

    function tick() {
        const remaining = getRemainingSeconds();
        if (timerLabel) timerLabel.textContent = '⏱ ' + format(remaining);

        if (remaining <= 0) {
            stop();
            if (cfg.autoNextOnTimeout && btnNext) {
                btnNext.disabled = false;
                btnNext.click();
            }
        }
    }

    function start() {
        stop();
        const seconds = cfg.secondsPerQuestion || 25;
        deadline = Date.now() + seconds * 1000;
        if (timerLabel) {
            timerLabel.hidden = false;
            timerLabel.textContent = '⏱ ' + format(seconds);
        }

        timerId = setInterval(tick, 1000);
    }

    // Recalculate immediately when tab becomes visible again
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && timerId) {
            tick();
        }
    });

    function hide() {
        stop();
        if (timerLabel) timerLabel.hidden = true;
    }

    function isQuestionActive() {
        return screenQuestion && screenQuestion.classList.contains('is-active');
    }

    // Detect question changes by watching the counter text (changes on every question)
    // This avoids the bug where MutationObserver on question text fires on answer selection
    function checkQuestionChange() {
        if (!counterLabel) return;
        const current = counterLabel.textContent.trim();
        if (current && current !== lastCounter && isQuestionActive()) {
            lastCounter = current;
            start();
        }
    }

    // Observe counter changes (only changes when navigating between questions)
    if (counterLabel) {
        const counterObserver = new MutationObserver(checkQuestionChange);
        counterObserver.observe(counterLabel, { childList: true, characterData: true, subtree: true });
    }

    // Observe screen changes (start/result hide timer)
    const screenObserver = new MutationObserver(() => {
        if (screenResult && screenResult.classList.contains('is-active')) hide();
        if (screenStart && screenStart.classList.contains('is-active')) hide();
    });

    if (screenStart) screenObserver.observe(screenStart, { attributes: true, attributeFilter: ['class'] });
    if (screenResult) screenObserver.observe(screenResult, { attributes: true, attributeFilter: ['class'] });

    // Handle restart
    const btnRestart = shell.querySelector('[data-quiz-restart]');
    if (btnRestart) {
        btnRestart.addEventListener('click', () => {
            lastCounter = '';
            hide();
        });
    }
});
