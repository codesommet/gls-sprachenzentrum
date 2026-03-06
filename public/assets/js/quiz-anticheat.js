/**
 * Quiz Anti-Cheat — Prevents cheating during exam
 * - Blocks tab switching (warns + auto-submits after max violations)
 * - Blocks right-click context menu
 * - Blocks copy/paste/cut
 * - Blocks DevTools shortcuts (F12, Ctrl+Shift+I/J/C, Ctrl+U)
 * - Blocks text selection on quiz content
 *
 * Only activates after quiz starts (listens for quiz-start click).
 */
(function () {
  var shell = document.querySelector('[data-quiz]');
  if (!shell) return;

  var btnStart = shell.querySelector('[data-quiz-start]');
  var btnNext = shell.querySelector('[data-quiz-next]');
  var active = false;
  var violations = 0;
  var MAX_VIOLATIONS = 3;

  // Overlay for tab-switch warning
  var overlay = document.createElement('div');
  overlay.className = 'quiz-cheat-overlay';
  overlay.innerHTML =
    '<div class="quiz-cheat-modal">' +
      '<div class="quiz-cheat-icon">&#9888;</div>' +
      '<h3 class="quiz-cheat-title">Attention!</h3>' +
      '<p class="quiz-cheat-text"></p>' +
      '<button type="button" class="quiz-btn quiz-btn_primary quiz-cheat-dismiss">OK</button>' +
    '</div>';
  overlay.style.cssText = 'display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;';
  document.body.appendChild(overlay);

  var dismissBtn = overlay.querySelector('.quiz-cheat-dismiss');
  var cheatText = overlay.querySelector('.quiz-cheat-text');

  function showWarning(msg) {
    cheatText.textContent = msg;
    overlay.style.display = 'flex';
  }

  function hideWarning() {
    overlay.style.display = 'none';
  }

  if (dismissBtn) {
    dismissBtn.addEventListener('click', hideWarning);
  }

  // Start monitoring when exam begins
  if (btnStart) {
    btnStart.addEventListener('click', function () {
      active = true;
      violations = 0;
      shell.classList.add('quiz-no-select');
    });
  }

  // Stop monitoring on result screen
  var screenResult = shell.querySelector('[data-screen="result"]');
  if (screenResult) {
    var obs = new MutationObserver(function () {
      if (screenResult.classList.contains('is-active')) {
        active = false;
        shell.classList.remove('quiz-no-select');
      }
    });
    obs.observe(screenResult, { attributes: true, attributeFilter: ['class'] });
  }

  // 1. Tab switch / visibility change
  document.addEventListener('visibilitychange', function () {
    if (!active) return;
    if (document.hidden) {
      violations++;
      if (violations >= MAX_VIOLATIONS) {
        // Auto-submit: force end exam
        active = false;
        showWarning('Vous avez quitté l\'examen trop de fois. Vos réponses ont été soumises automatiquement.');
        // Force submit by going to result
        if (window.__quizEngine) {
          window.__quizEngine.goToStep(window.__quizEngine.questions.length);
        }
      } else {
        showWarning(
          'Quitter l\'onglet est interdit pendant l\'examen. Avertissement ' +
          violations + '/' + MAX_VIOLATIONS + '. Au prochain, vos réponses seront soumises automatiquement.'
        );
      }
    }
  });

  // 2. Block right-click
  document.addEventListener('contextmenu', function (e) {
    if (!active) return;
    e.preventDefault();
  });

  // 3. Block copy/cut/paste
  ['copy', 'cut', 'paste'].forEach(function (evt) {
    document.addEventListener(evt, function (e) {
      if (!active) return;
      e.preventDefault();
    });
  });

  // 4. Block DevTools shortcuts
  document.addEventListener('keydown', function (e) {
    if (!active) return;

    // F12
    if (e.key === 'F12') {
      e.preventDefault();
      return;
    }

    // Ctrl+Shift+I / Ctrl+Shift+J / Ctrl+Shift+C (DevTools)
    if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) {
      e.preventDefault();
      return;
    }

    // Ctrl+U (View source)
    if (e.ctrlKey && (e.key === 'U' || e.key === 'u')) {
      e.preventDefault();
      return;
    }

    // Ctrl+S (Save page)
    if (e.ctrlKey && (e.key === 'S' || e.key === 's')) {
      e.preventDefault();
      return;
    }

    // Ctrl+A (Select all)
    if (e.ctrlKey && (e.key === 'A' || e.key === 'a')) {
      e.preventDefault();
      return;
    }
  });

  // 5. Block drag
  document.addEventListener('dragstart', function (e) {
    if (!active) return;
    e.preventDefault();
  });
})();
