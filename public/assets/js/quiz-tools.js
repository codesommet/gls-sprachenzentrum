/**
 * Quiz Tools — Font size, Flag/Mark, Navigator, Progress bar
 * Depends on window.__quizEngine (set by quiz-core.js)
 */
(function () {
  var engine = window.__quizEngine;
  if (!engine) return;

  var $shell = engine.$shell;
  var state = engine.state;
  var questions = engine.questions;

  // DOM refs
  var $btnFontUp = $shell.querySelector('[data-quiz-font-up]');
  var $btnFontDown = $shell.querySelector('[data-quiz-font-down]');
  var $fontLabel = $shell.querySelector('[data-quiz-font-label]');
  var $btnFlag = $shell.querySelector('[data-quiz-flag]');
  var $navigatorGrid = $shell.querySelector('[data-quiz-navigator]');
  var $navigatorToggle = $shell.querySelector('[data-quiz-navigator-toggle]');
  var $progressBar = $shell.querySelector('[data-quiz-progressbar]');
  var $progressBarFill = $shell.querySelector('[data-quiz-progressbar-fill]');
  var $progressBarText = $shell.querySelector('[data-quiz-progressbar-text]');

  // ===== FONT SIZE =====
  var FONT_MIN = 80;
  var FONT_MAX = 140;
  var FONT_STEP = 10;

  function applyFontSize() {
    var card = $shell.querySelector('.quiz-card_inner');
    if (card) {
      card.style.zoom = state.fontSize / 100;
    }
    if ($fontLabel) $fontLabel.textContent = state.fontSize + '%';
  }

  if ($btnFontUp) {
    $btnFontUp.addEventListener('click', function () {
      if (state.fontSize < FONT_MAX) {
        state.fontSize += FONT_STEP;
        applyFontSize();
      }
    });
  }

  if ($btnFontDown) {
    $btnFontDown.addEventListener('click', function () {
      if (state.fontSize > FONT_MIN) {
        state.fontSize -= FONT_STEP;
        applyFontSize();
      }
    });
  }

  // ===== FLAG / MARK FOR REVIEW =====
  function updateFlagButton() {
    if (!$btnFlag) return;
    var isFlagged = !!state.flagged[state.step];
    $btnFlag.classList.toggle('is-flagged', isFlagged);
    $btnFlag.setAttribute('aria-pressed', String(isFlagged));
  }

  if ($btnFlag) {
    $btnFlag.addEventListener('click', function () {
      if (state.step < 0 || state.step >= questions.length) return;
      state.flagged[state.step] = !state.flagged[state.step];
      if (!state.flagged[state.step]) delete state.flagged[state.step];
      updateFlagButton();
      updateNavigator();
      engine.updateProgress();
    });
  }

  // ===== QUESTION NAVIGATOR =====
  function buildNavigator() {
    if (!$navigatorGrid) return;
    $navigatorGrid.innerHTML = '';
    for (var i = 0; i < questions.length; i++) {
      (function (idx) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'quiz-nav-cell';
        btn.setAttribute('data-nav-cell', String(idx));
        btn.textContent = String(idx + 1);
        btn.addEventListener('click', function () {
          if (state.step >= 0) engine.goToStep(idx);
        });
        $navigatorGrid.appendChild(btn);
      })(i);
    }
  }

  function updateNavigator() {
    if (!$navigatorGrid) return;
    var cells = $navigatorGrid.querySelectorAll('.quiz-nav-cell');
    cells.forEach(function (cell, i) {
      cell.classList.remove('is-active', 'is-answered', 'is-flagged', 'is-unanswered');

      if (i === state.step) cell.classList.add('is-active');

      var q = questions[i];
      var qid = q ? String(q.id) : null;
      var ans = qid ? state.answers[qid] : undefined;
      var hasAnswer = ans !== undefined && ans !== '' &&
        !(Array.isArray(ans) && ans.length === 0);

      if (hasAnswer) {
        cell.classList.add('is-answered');
      } else {
        cell.classList.add('is-unanswered');
      }

      if (state.flagged[i]) {
        cell.classList.add('is-flagged');
      }
    });
  }

  if ($navigatorToggle) {
    $navigatorToggle.addEventListener('click', function () {
      var panel = $shell.querySelector('[data-quiz-navigator-panel]');
      if (panel) {
        var isOpen = !panel.hidden;
        panel.hidden = isOpen;
        $navigatorToggle.setAttribute('aria-expanded', String(!isOpen));
      }
    });
  }

  // ===== PROGRESS BAR =====
  function updateProgressBar() {
    if (!$progressBar) return;
    var total = questions.length;
    if (total === 0) return;

    var answered = 0;
    questions.forEach(function (q) {
      var qid = String(q.id);
      var ans = state.answers[qid];
      if (ans !== undefined && ans !== '' && !(Array.isArray(ans) && ans.length === 0)) {
        answered++;
      }
    });

    var pct = Math.round((answered / total) * 100);
    if ($progressBarFill) {
      $progressBarFill.style.width = pct + '%';
    }
    if ($progressBarText) {
      $progressBarText.textContent = answered + ' / ' + total + ' (' + pct + '%)';
    }
  }

  // Expose tools to engine
  engine.tools = {
    applyFontSize: applyFontSize,
    updateFlagButton: updateFlagButton,
    buildNavigator: buildNavigator,
    updateNavigator: updateNavigator,
    updateProgressBar: updateProgressBar
  };

  // Init
  buildNavigator();
  applyFontSize();
})();
