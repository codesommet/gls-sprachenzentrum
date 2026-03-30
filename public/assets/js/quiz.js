/**
 * Quiz Core — State, rendering, navigation, results
 * Exposes window.__quizEngine for quiz-tools.js and quiz-keyboard.js
 */
(function () {
  var quiz = window.__QUIZ__ || { questions: [] };
  var questions = Array.isArray(quiz.questions) ? quiz.questions : [];

  var $shell = document.querySelector('[data-quiz]');
  if (!$shell) return;

  var $screens = {
    start: $shell.querySelector('[data-screen="start"]'),
    question: $shell.querySelector('[data-screen="question"]'),
    result: $shell.querySelector('[data-screen="result"]'),
  };

  var $counter = $shell.querySelector('[data-quiz-counter]');
  var $progress = $shell.querySelector('[data-quiz-progress]');

  var $btnStart = $shell.querySelector('[data-quiz-start]');
  var $btnRestart = $shell.querySelector('[data-quiz-restart]');
  var $btnSubmit = $shell.querySelector('[data-quiz-submit]');
  var $btnPrev = $shell.querySelector('[data-quiz-prev]');
  var $btnNext = $shell.querySelector('[data-quiz-next]');

  var $nav = $shell.querySelector('[data-quiz-nav]') || $shell.querySelector('.quiz-nav');

  var $qTitle = $shell.querySelector('[data-q-title]');
  var $qPrompt = $shell.querySelector('[data-q-prompt]');
  var $qQuestion = $shell.querySelector('[data-q-question]');
  var $qAnswers = $shell.querySelector('[data-q-answers]');

  var $qMediaWrap = $shell.querySelector('[data-q-media]');
  var $qImage = $shell.querySelector('[data-q-image]');
  var $qAudio = $shell.querySelector('[data-q-audio]');

  var $resultCorrect = $shell.querySelector('[data-result-correct]');
  var $resultTotal = $shell.querySelector('[data-result-total]');
  var $resultPercent = $shell.querySelector('[data-result-percent]');

  var $form = $shell.querySelector('[data-quiz-form]');
  var $answersJson = $shell.querySelector('[data-answers-json]');

  var $hero = $shell.querySelector('[data-quiz-hero]');
  var $startSubtitle = $shell.querySelector('[data-quiz-subtitle]');
  var $startTitle = $shell.querySelector('[data-quiz-title]');
  var $toolbar = $shell.querySelector('[data-quiz-toolbar]');

  var state = {
    step: -1,
    answers: {},
    flagged: {},
    fontSize: 100,
  };

  // Helper: get tools (set by quiz-tools.js after load)
  function tools() {
    return (window.__quizEngine && window.__quizEngine.tools) || {};
  }

  // ===== CORE FUNCTIONS =====
  function showScreen(name) {
    Object.values($screens).forEach(function (el) { if (el) el.classList.remove('is-active'); });
    if ($screens[name]) $screens[name].classList.add('is-active');
  }

  function buildProgress() {
    if (!$progress) return;
    $progress.innerHTML = '';
    for (var i = 0; i < questions.length; i++) {
      var dot = document.createElement('span');
      dot.className = 'quiz-dot';
      dot.setAttribute('data-dot', String(i));
      $progress.appendChild(dot);
    }
  }

  function updateProgress() {
    if (!$progress) return;
    var dots = $progress.querySelectorAll('.quiz-dot');
    dots.forEach(function (d, i) {
      d.classList.remove('is-active', 'is-done', 'is-flagged');
      if (i < state.step) d.classList.add('is-done');
      if (i === state.step) d.classList.add('is-active');
      if (state.flagged[i]) d.classList.add('is-flagged');
    });
  }

  function updateCounter() {
    if (!$counter) return;
    if (state.step >= 0 && state.step < questions.length) {
      $counter.textContent = (state.step + 1) + ' / ' + questions.length;
    } else {
      $counter.textContent = '';
    }
  }

  function setNavState() {
    var onQuestion = state.step >= 0 && state.step < questions.length;

    if ($btnPrev) $btnPrev.disabled = !(state.step > 0);
    if ($btnNext) $btnNext.disabled = true;

    if (!onQuestion) {
      if ($btnPrev) $btnPrev.disabled = true;
      if ($btnNext) $btnNext.disabled = true;
      return;
    }

    var q = questions[state.step];
    var qid = String(q.id);
    var ans = state.answers[qid];

    if (q.type === 'multi') {
      if ($btnNext) $btnNext.disabled = !(Array.isArray(ans) && ans.length > 0);
    } else if (q.type === 'text') {
      if ($btnNext) $btnNext.disabled = !(typeof ans === 'string' && ans.trim().length > 0);
    } else {
      if ($btnNext) $btnNext.disabled = !(typeof ans === 'string' && ans.length > 0);
    }
  }

  function resetMedia() {
    if ($qImage) {
      $qImage.hidden = true;
      $qImage.removeAttribute('src');
      $qImage.removeAttribute('alt');
    }
    if ($qAudio) {
      $qAudio.hidden = true;
      if ($qAudio.pause) $qAudio.pause();
      $qAudio.removeAttribute('src');
      if ($qAudio.load) $qAudio.load();
    }
    if ($qMediaWrap) $qMediaWrap.hidden = true;
  }

  function renderMedia(q) {
    var optionsType = String(q.options_type || 'text').toLowerCase();
    var media = q.media || { type: 'none', url: null };
    var type = String(media.type || 'none').toLowerCase();
    var url = media.url || null;

    resetMedia();

    if (optionsType === 'image') return;
    if (!url || type === 'none') return;

    if ($qMediaWrap) $qMediaWrap.hidden = false;

    if (type === 'image' && $qImage) {
      $qImage.hidden = false;
      $qImage.src = url;
      $qImage.alt = q.title || 'Question';
      return;
    }

    if (type === 'audio' && $qAudio) {
      $qAudio.hidden = false;
      $qAudio.src = url;
      $qAudio.load();
      return;
    }

    resetMedia();
  }

  function onAnswerChange(qid) {
    setNavState();
    if (tools().updateNavigator) tools().updateNavigator();
    if (tools().updateProgressBar) tools().updateProgressBar();
  }

  function renderQuestion() {
    var q = questions[state.step];
    if (!q) return;

    var qid = String(q.id);
    var current = state.answers[qid];
    var optionsType = String(q.options_type || 'text').toLowerCase();

    if ($qTitle) $qTitle.textContent = q.title || '';
    if ($qPrompt) $qPrompt.textContent = q.prompt || '';
    if ($qQuestion) $qQuestion.textContent = q.question || '';

    renderMedia(q);

    $qAnswers.innerHTML = '';
    $qAnswers.className = 'quiz-answers';

    // ===== TEXT MODE =====
    if (optionsType === 'text') {
      if (q.type === 'text') {
        var wrap = document.createElement('div');
        wrap.className = 'quiz-text-wrap';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'quiz-text';
        input.placeholder = (window.__QUIZ_I18N__ || {}).placeholder || '...';
        input.value = typeof current === 'string' ? current : '';
        input.style.width = '100%';
        input.style.padding = '12px 14px';
        input.style.border = '1px solid #e5e7eb';
        input.style.borderRadius = '12px';
        input.style.fontWeight = '700';

        input.addEventListener('input', function () {
          state.answers[qid] = input.value;
          onAnswerChange(qid);
        });

        wrap.appendChild(input);
        $qAnswers.appendChild(wrap);
      } else {
        var isMulti = q.type === 'multi';
        var name = 'q_' + qid;

        (q.choices || []).forEach(function (choice, idx) {
          var cid = String(choice.id);

          var label = document.createElement('label');
          label.className = 'quiz-choice';
          label.setAttribute('data-choice-index', String(idx));

          var inp = document.createElement('input');
          inp.type = isMulti ? 'checkbox' : 'radio';
          inp.name = name;
          inp.value = cid;

          var span = document.createElement('span');
          span.className = 'quiz-choice_label';
          span.textContent = choice.label || '';

          // Keyboard shortcut hint
          if (!isMulti && idx < 9) {
            var hint = document.createElement('span');
            hint.className = 'quiz-choice_key';
            hint.textContent = String(idx + 1);
            label.appendChild(hint);
          }

          if (isMulti) {
            var arr = Array.isArray(current) ? current : [];
            inp.checked = arr.includes(cid);
            if (inp.checked) label.classList.add('is-selected');
          } else {
            inp.checked = current === cid;
            if (inp.checked) label.classList.add('is-selected');
          }

          label.appendChild(inp);
          label.appendChild(span);

          label.addEventListener('click', function () {
            setTimeout(function () {
              if (isMulti) {
                var selected = Array.from($qAnswers.querySelectorAll('input[type="checkbox"]:checked'))
                  .map(function (i) { return i.value; });
                state.answers[qid] = selected;
              } else {
                var checked = $qAnswers.querySelector('input[type="radio"]:checked');
                state.answers[qid] = checked ? checked.value : '';
              }

              $qAnswers.querySelectorAll('.quiz-choice').forEach(function (c) { c.classList.remove('is-selected'); });
              $qAnswers.querySelectorAll('input:checked').forEach(function (i) {
                var parent = i.closest('.quiz-choice');
                if (parent) parent.classList.add('is-selected');
              });

              onAnswerChange(qid);
            }, 0);
          });

          $qAnswers.appendChild(label);
        });
      }
    }

    // ===== IMAGE MODE =====
    else if (optionsType === 'image') {
      $qAnswers.className = 'quiz-answers quiz-answers-image';

      (q.choices || []).forEach(function (choice) {
        var cid = String(choice.id);
        var imageUrl = choice.image_url || null;
        var isSelected = current === cid;

        var card = document.createElement('div');
        card.className = 'quiz-image-option';
        if (isSelected) card.classList.add('is-selected');

        var clickable = document.createElement('div');
        clickable.className = 'quiz-image-option_clickable';
        clickable.role = 'button';
        clickable.tabIndex = 0;

        var inp = document.createElement('input');
        inp.type = 'radio';
        inp.name = 'q_' + qid;
        inp.value = cid;
        inp.checked = isSelected;
        inp.style.display = 'none';

        var imgContainer = document.createElement('div');
        imgContainer.className = 'quiz-image-option_img';

        if (imageUrl) {
          var img = document.createElement('img');
          img.src = imageUrl;
          img.alt = 'Option ' + choice.id;
          img.loading = 'lazy';
          imgContainer.appendChild(img);
        } else {
          var fallback = document.createElement('div');
          fallback.className = 'quiz-image-option_missing';
          fallback.textContent = 'Image manquante';
          imgContainer.appendChild(fallback);
        }

        clickable.appendChild(imgContainer);
        card.appendChild(inp);
        card.appendChild(clickable);

        card.addEventListener('click', function () {
          inp.checked = true;
          state.answers[qid] = cid;

          $qAnswers.querySelectorAll('.quiz-image-option').forEach(function (c) {
            c.classList.remove('is-selected');
          });
          card.classList.add('is-selected');

          onAnswerChange(qid);
        });

        clickable.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            card.click();
          }
        });

        $qAnswers.appendChild(card);
      });
    }

    updateCounter();
    updateProgress();
    if (tools().updateNavigator) tools().updateNavigator();
    if (tools().updateProgressBar) tools().updateProgressBar();
    if (tools().updateFlagButton) tools().updateFlagButton();
    setNavState();
  }

  function showResultScreen() {
    showScreen('result');
    resetMedia();

    // Show loading state — real scoring is done server-side
    if ($resultCorrect) $resultCorrect.textContent = '...';
    if ($resultTotal) $resultTotal.textContent = String(questions.length);
    if ($resultPercent) $resultPercent.textContent = '...';

    if ($answersJson) $answersJson.value = JSON.stringify(state.answers);

    if ($form) {
      setTimeout(function () { $form.submit(); }, 100);
    }

    updateCounter();
    updateProgress();
    if (tools().updateNavigator) tools().updateNavigator();
    if (tools().updateProgressBar) tools().updateProgressBar();

    if ($btnPrev) $btnPrev.disabled = true;
    if ($btnNext) $btnNext.disabled = true;
  }

  function goToStep(step) {
    state.step = step;

    // START
    if (state.step === -1) {
      showScreen('start');
      resetMedia();
      updateCounter();
      updateProgress();

      if ($nav) $nav.style.display = 'none';
      if ($toolbar) $toolbar.hidden = true;

      var navPanel = $shell.querySelector('[data-quiz-navigator-panel]');
      if (navPanel) navPanel.hidden = true;

      setNavState();
      return;
    }

    // QUESTIONS
    if (state.step >= 0 && state.step < questions.length) {
      showScreen('question');

      if ($nav) $nav.style.display = '';
      if ($toolbar) $toolbar.hidden = false;

      renderQuestion();
      return;
    }

    // RESULT
    if ($toolbar) $toolbar.hidden = true;
    showResultScreen();
  }

  // ===== EVENT LISTENERS =====
  if ($btnStart) {
    $btnStart.addEventListener('click', function () {
      if ($hero) $hero.style.display = 'none';
      if ($startTitle) $startTitle.style.display = 'none';
      if ($startSubtitle) $startSubtitle.style.display = 'none';
      // Track quiz start via gtag
      if (window.gtag && window.__QUIZ_LEVEL__) {
        gtag('event', 'niveau_test_started', {
          quiz_level: window.__QUIZ_LEVEL__,
        });
      }
      goToStep(0);
    });
  }

  if ($btnPrev) {
    $btnPrev.addEventListener('click', function () {
      if (state.step > 0) goToStep(state.step - 1);
    });
  }

  if ($btnNext) {
    $btnNext.addEventListener('click', function () {
      if (state.step < questions.length - 1) {
        goToStep(state.step + 1);
      } else {
        goToStep(questions.length);
      }
    });
  }

  if ($btnRestart) {
    $btnRestart.addEventListener('click', function () {
      window.location.reload();
    });
  }

  if ($btnSubmit) {
    $btnSubmit.addEventListener('click', function () {
      if ($answersJson) $answersJson.value = JSON.stringify(state.answers);
    });
  }

  // ===== EXPOSE ENGINE =====
  window.__quizEngine = {
    $shell: $shell,
    $btnNext: $btnNext,
    $btnPrev: $btnPrev,
    state: state,
    questions: questions,
    goToStep: goToStep,
    updateProgress: updateProgress,
    tools: {} // filled by quiz-tools.js
  };

  // ===== INIT =====
  if (window.__QUIZ_HAS_RESULT__) {
    // Result already rendered server-side, don't reset to start screen
    state.step = questions.length;
  } else {
    buildProgress();
    goToStep(-1);
  }
})();
