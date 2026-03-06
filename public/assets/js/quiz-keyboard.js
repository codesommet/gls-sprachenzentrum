/**
 * Quiz Keyboard — Shortcut keys for quiz navigation
 * Depends on window.__quizEngine (set by quiz-core.js)
 */
(function () {
  var engine = window.__quizEngine;
  if (!engine) return;

  var state = engine.state;
  var questions = engine.questions;
  var $btnNext = engine.$btnNext;
  var $btnPrev = engine.$btnPrev;
  var $btnFlag = engine.$shell.querySelector('[data-quiz-flag]');
  var $qAnswers = engine.$shell.querySelector('[data-q-answers]');

  document.addEventListener('keydown', function (e) {
    // Only when on question screen
    if (state.step < 0 || state.step >= questions.length) return;

    // Don't intercept if user is typing in a text input/textarea/select
    var tag = (e.target.tagName || '').toLowerCase();
    var inputType = (e.target.type || '').toLowerCase();
    if (tag === 'textarea' || tag === 'select') return;
    if (tag === 'input' && inputType !== 'radio' && inputType !== 'checkbox') return;

    var q = questions[state.step];
    if (!q) return;

    // Enter = Next
    if (e.key === 'Enter') {
      e.preventDefault();
      if ($btnNext && !$btnNext.disabled) $btnNext.click();
      return;
    }

    // R = Previous
    if (e.key === 'r' || e.key === 'R') {
      e.preventDefault();
      if ($btnPrev && !$btnPrev.disabled) $btnPrev.click();
      return;
    }

    // F = Flag
    if (e.key === 'f' || e.key === 'F') {
      e.preventDefault();
      if ($btnFlag) $btnFlag.click();
      return;
    }

    // Number keys 1-9 to select answers
    var num = parseInt(e.key);
    if (num >= 1 && num <= 9) {
      var choices = q.choices || [];
      var idx = num - 1;
      if (idx < choices.length) {
        e.preventDefault();
        var choiceLabel = $qAnswers.querySelector('[data-choice-index="' + idx + '"]');
        if (choiceLabel) {
          choiceLabel.click();
        } else {
          var cards = $qAnswers.querySelectorAll('.quiz-image-option');
          if (cards[idx]) cards[idx].click();
        }
      }
    }
  });
})();
