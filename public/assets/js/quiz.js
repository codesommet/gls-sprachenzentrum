(function () {
  const quiz = window.__QUIZ__ || { questions: [] };
  const questions = Array.isArray(quiz.questions) ? quiz.questions : [];

  const $shell = document.querySelector('[data-quiz]');
  if (!$shell) return;

  const $screens = {
    start: $shell.querySelector('[data-screen="start"]'),
    question: $shell.querySelector('[data-screen="question"]'),
    result: $shell.querySelector('[data-screen="result"]'),
  };

  const $counter = $shell.querySelector('[data-quiz-counter]');
  const $progress = $shell.querySelector('[data-quiz-progress]');

  const $btnStart = $shell.querySelector('[data-quiz-start]');
  const $btnRestart = $shell.querySelector('[data-quiz-restart]');
  const $btnSubmit = $shell.querySelector('[data-quiz-submit]'); // optional
  const $btnPrev = $shell.querySelector('[data-quiz-prev]');
  const $btnNext = $shell.querySelector('[data-quiz-next]');

  // Nav wrapper (if you add data-quiz-nav + hidden in blade, we will control it)
  const $nav = $shell.querySelector('[data-quiz-nav]') || $shell.querySelector('.quiz-nav');

  const $qTitle = $shell.querySelector('[data-q-title]');
  const $qPrompt = $shell.querySelector('[data-q-prompt]');
  const $qQuestion = $shell.querySelector('[data-q-question]');
  const $qAnswers = $shell.querySelector('[data-q-answers]');

  // ✅ Media elements (support image + audio)
  const $qMediaWrap = $shell.querySelector('[data-q-media]');
  const $qImage = $shell.querySelector('[data-q-image]');
  const $qAudio = $shell.querySelector('[data-q-audio]');

  const $resultCorrect = $shell.querySelector('[data-result-correct]');
  const $resultTotal = $shell.querySelector('[data-result-total]');
  const $resultPercent = $shell.querySelector('[data-result-percent]');
  const $resultLevel = $shell.querySelector('[data-result-level]');

  const $form = $shell.querySelector('[data-quiz-form]');
  const $answersJson = $shell.querySelector('[data-answers-json]');

  // Header elements to hide after start (from your blade)
  const $hero = $shell.querySelector('[data-quiz-hero]');
  const $startSubtitle = $shell.querySelector('[data-quiz-subtitle]');
  const $startTitle = $shell.querySelector('[data-quiz-title]'); // if you used it

  let state = {
    step: -1,          // -1 start, 0..n-1 questions, n result
    answers: {},       // { [questionId]: value } (value = string | string[])
  };

  function showScreen(name) {
    Object.values($screens).forEach(el => el && el.classList.remove('is-active'));
    if ($screens[name]) $screens[name].classList.add('is-active');
  }

  function buildProgress() {
    if (!$progress) return;
    $progress.innerHTML = '';
    for (let i = 0; i < questions.length; i++) {
      const dot = document.createElement('span');
      dot.className = 'quiz-dot';
      dot.setAttribute('data-dot', String(i));
      $progress.appendChild(dot);
    }
  }

  function updateProgress() {
    if (!$progress) return;
    const dots = $progress.querySelectorAll('.quiz-dot');
    dots.forEach((d, i) => {
      d.classList.remove('is-active', 'is-done');
      if (i < state.step) d.classList.add('is-done');
      if (i === state.step) d.classList.add('is-active');
    });
  }

  function updateCounter() {
    if (!$counter) return;

    if (state.step >= 0 && state.step < questions.length) {
      $counter.textContent = `${state.step + 1} / ${questions.length}`;
    } else {
      $counter.textContent = '';
    }
  }

  function setNavState() {
    const onQuestion = state.step >= 0 && state.step < questions.length;

    if ($btnPrev) $btnPrev.disabled = !(state.step > 0);
    if ($btnNext) $btnNext.disabled = true;

    if (!onQuestion) {
      if ($btnPrev) $btnPrev.disabled = true;
      if ($btnNext) $btnNext.disabled = true;
      return;
    }

    const q = questions[state.step];
    const qid = String(q.id);
    const ans = state.answers[qid];

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
      $qAudio.pause?.();
      $qAudio.removeAttribute('src');
      $qAudio.load?.();
    }
    if ($qMediaWrap) $qMediaWrap.hidden = true;
  }

  function renderMedia(q) {
    // Expecting: q.media = { type: 'image'|'audio'|'none', url: '...' }
    // NEW RULE: If options_type='image', ALWAYS hide question media
    const optionsType = String(q.options_type || 'text').toLowerCase();
    const media = q.media || { type: 'none', url: null };
    const type = String(media.type || 'none').toLowerCase();
    const url = media.url || null;

    resetMedia();

    // Hide question media if using image options
    if (optionsType === 'image') {
      return;
    }

    // Otherwise, show media based on type
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

  function renderQuestion() {
    const q = questions[state.step];
    if (!q) return;

    const qid = String(q.id);
    const current = state.answers[qid];
    const optionsType = String(q.options_type || 'text').toLowerCase();

    if ($qTitle) $qTitle.textContent = q.title || '';
    if ($qPrompt) $qPrompt.textContent = q.prompt || '';
    if ($qQuestion) $qQuestion.textContent = q.question || '';

    renderMedia(q);

    $qAnswers.innerHTML = '';

    // ===== TEXT MODE =====
    if (optionsType === 'text') {
      if (q.type === 'text') {
        const wrap = document.createElement('div');
        wrap.className = 'quiz-text-wrap';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'quiz-text';
        input.placeholder = 'Ta réponse...';
        input.value = typeof current === 'string' ? current : '';
        input.style.width = '100%';
        input.style.padding = '12px 14px';
        input.style.border = '1px solid #e5e7eb';
        input.style.borderRadius = '12px';
        input.style.fontWeight = '700';

        input.addEventListener('input', () => {
          state.answers[qid] = input.value;
          setNavState();
        });

        wrap.appendChild(input);
        $qAnswers.appendChild(wrap);
      } else {
        // Multi/single choice with text labels
        const isMulti = q.type === 'multi';
        const name = `q_${qid}`;

        (q.choices || []).forEach(choice => {
          const cid = String(choice.id);

          const label = document.createElement('label');
          label.className = 'quiz-choice';

          const input = document.createElement('input');
          input.type = isMulti ? 'checkbox' : 'radio';
          input.name = name;
          input.value = cid;

          const span = document.createElement('span');
          span.className = 'quiz-choice_label';
          span.textContent = choice.label || '';

          if (isMulti) {
            const arr = Array.isArray(current) ? current : [];
            input.checked = arr.includes(cid);
            if (input.checked) label.classList.add('is-selected');
          } else {
            input.checked = current === cid;
            if (input.checked) label.classList.add('is-selected');
          }

          label.appendChild(input);
          label.appendChild(span);

          label.addEventListener('click', () => {
            setTimeout(() => {
              if (isMulti) {
                const selected = Array.from($qAnswers.querySelectorAll('input[type="checkbox"]:checked'))
                  .map(i => i.value);
                state.answers[qid] = selected;
              } else {
                const checked = $qAnswers.querySelector('input[type="radio"]:checked');
                state.answers[qid] = checked ? checked.value : '';
              }

              $qAnswers.querySelectorAll('.quiz-choice').forEach(c => c.classList.remove('is-selected'));
              $qAnswers.querySelectorAll('input:checked').forEach(i => {
                const parent = i.closest('.quiz-choice');
                if (parent) parent.classList.add('is-selected');
              });

              setNavState();
            }, 0);
          });

          $qAnswers.appendChild(label);
        });
      }
    }

    // ===== IMAGE MODE =====
    else if (optionsType === 'image') {
      $qAnswers.className = 'quiz-answers quiz-answers-image';

      (q.choices || []).forEach(choice => {
        const cid = String(choice.id);
        const imageUrl = choice.image_url || null;
        const isSelected = current === cid;

        // Create image card container
        const card = document.createElement('div');
        card.className = 'quiz-image-option';
        if (isSelected) card.classList.add('is-selected');

        // Clickable wrapper
        const clickable = document.createElement('div');
        clickable.className = 'quiz-image-option_clickable';
        clickable.role = 'button';
        clickable.tabIndex = 0;

        // Hidden radio for form submission
        const input = document.createElement('input');
        input.type = 'radio';
        input.name = `q_${qid}`;
        input.value = cid;
        input.checked = isSelected;
        input.style.display = 'none';

        // Image or fallback
        const imgContainer = document.createElement('div');
        imgContainer.className = 'quiz-image-option_img';

        if (imageUrl) {
          const img = document.createElement('img');
          img.src = imageUrl;
          img.alt = `Option ${choice.id}`;
          img.loading = 'lazy';
          imgContainer.appendChild(img);
        } else {
          const fallback = document.createElement('div');
          fallback.className = 'quiz-image-option_missing';
          fallback.textContent = 'Image manquante';
          imgContainer.appendChild(fallback);
        }

        clickable.appendChild(imgContainer);
        card.appendChild(input);
        card.appendChild(clickable);

        // Click handler
        card.addEventListener('click', () => {
          input.checked = true;
          state.answers[qid] = cid;

          // Update visual selection
          $qAnswers.querySelectorAll('.quiz-image-option').forEach(c => {
            c.classList.remove('is-selected');
          });
          card.classList.add('is-selected');

          setNavState();
        });

        // Keyboard support
        clickable.addEventListener('keydown', (e) => {
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
    setNavState();
  }

  function countAnswered() {
    return Object.keys(state.answers).filter(k => {
      const v = state.answers[k];
      if (Array.isArray(v)) return v.length > 0;
      return typeof v === 'string' && v.trim().length > 0;
    }).length;
  }

  function getChosenChoice(q, stored) {
    if (stored == null) return null;

    // stored can be choice id (string) from new system
    return (q.choices || []).find(c => String(c.id) === String(stored)) || null;
  }

  function detectLevelFromPercent(percent) {
    if (percent >= 85) return 'B2';
    if (percent >= 70) return 'B1';
    if (percent >= 50) return 'A2';
    return 'A1';
  }

  function computeResult() {
    let answered = 0;
    const total = questions.length;

    questions.forEach(q => {
      const qid = String(q.id);
      const stored = state.answers[qid];

      if (stored == null) return;

      // multi not used now, but keep safe
      if (Array.isArray(stored)) {
        if (stored.length > 0) answered++;
        return;
      }

      // For single choice, just count as answered
      // Actual correctness will be verified server-side
      const chosen = getChosenChoice(q, stored);
      if (chosen) answered++;
    });

    const percent = total ? Math.round((answered / total) * 100) : 0;
    const level = detectLevelFromPercent(percent);

    // ⚠️ Scoring is now SERVER-SIDE
    // Frontend doesn't know which answers are correct (for security)
    return { answered, correct: 0, total, percent, level, serverSideScoring: true };
  }

  function showResultScreen() {
    showScreen('result');
    resetMedia();

    const result = computeResult();

    if ($resultCorrect) {
      $resultCorrect.textContent = String(result.correct);
    }

    if ($resultTotal) {
      $resultTotal.textContent = String(result.total);
    }

    if ($resultPercent) {
      $resultPercent.textContent = String(result.percent);
    }

    if ($resultLevel) {
      $resultLevel.textContent = result.level;
    }

    if ($answersJson) {
      $answersJson.value = JSON.stringify(state.answers);
    }

    // ✅ Submit form to server for server-side scoring
    if ($form && result.serverSideScoring) {
      // Small delay to ensure form field is updated
      setTimeout(() => {
        $form.submit();
      }, 100);
    }

    updateCounter();
    updateProgress();

    // Disable nav at result
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

      // hide nav on start
      if ($nav) $nav.style.display = 'none';

      // reset buttons state
      setNavState();
      return;
    }

    // QUESTIONS
    if (state.step >= 0 && state.step < questions.length) {
      showScreen('question');

      // show nav when quiz starts
      if ($nav) $nav.style.display = '';

      renderQuestion();
      return;
    }

    // RESULT
    showResultScreen();
  }

  // Events
  $btnStart?.addEventListener('click', () => {
    // hide header + subtitle on start click
    if ($hero) $hero.style.display = 'none';
    if ($startTitle) $startTitle.style.display = 'none';
    if ($startSubtitle) $startSubtitle.style.display = 'none';

    goToStep(0);
  });

  $btnPrev?.addEventListener('click', () => {
    if (state.step > 0) goToStep(state.step - 1);
  });

  $btnNext?.addEventListener('click', () => {
    if (state.step < questions.length - 1) {
      goToStep(state.step + 1);
    } else {
      // last question -> show result automatically
      goToStep(questions.length);
    }
  });

  $btnRestart?.addEventListener('click', () => {
    state = { step: -1, answers: {} };
    buildProgress();
    goToStep(-1);
  });

  // Submit is optional now (we don't need it)
  $btnSubmit?.addEventListener('click', () => {
    // You can still keep it for debug
    if ($answersJson) $answersJson.value = JSON.stringify(state.answers);
    alert('Résultat affiché automatiquement ✅ (Submit non nécessaire).');
  });

  // Init
  buildProgress();
  goToStep(-1);
})();
