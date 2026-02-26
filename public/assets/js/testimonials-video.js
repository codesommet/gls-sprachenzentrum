(() => {
  const root = document.querySelector('[data-gls-tv]');
  if (!root) return;

  // Items
  const itemsRaw = root.getAttribute('data-items') || '[]';
  let items = [];
  try {
    items = JSON.parse(itemsRaw);
  } catch (e) {
    console.error('[GLS TV] Invalid data-items JSON', e);
    return;
  }
  if (!Array.isArray(items) || items.length === 0) return;

  // Elements
  const deck = root.querySelector('.gls-tv-deck');
  const cards = deck ? Array.from(deck.querySelectorAll('.gls-tv-card')) : [];
  const phoneSlide = root.querySelector('[data-phone-slide]');
  const btnPrev = root.querySelector('[data-prev]');
  const btnNext = root.querySelector('[data-next]');

  const modal = root.querySelector('[data-modal]');
  const modalDialog = modal?.querySelector('.gls-tv-modal-dialog');
  const modalCloseEls = modal ? Array.from(modal.querySelectorAll('[data-close]')) : [];

  // State
  let activeIndex = 0;

  // Order used by the 5 placeholders in HTML
  const posOrder = ['farLeft', 'left', 'center', 'right', 'farRight'];
  const bgClasses = ['gls-tv-card--bg1', 'gls-tv-card--bg2', 'gls-tv-card--bg3', 'gls-tv-card--bg4'];

  const clampIndex = (i) => {
    const n = items.length;
    return ((i % n) + n) % n;
  };

  function indexForPos(pos, centerIndex) {
    // Map position -> relative index around active
    const map = {
      farLeft: centerIndex - 2,
      left: centerIndex - 1,
      center: centerIndex,
      right: centerIndex + 1,
      farRight: centerIndex + 2,
    };
    return clampIndex(map[pos]);
  }

  function toEmbedUrl(url) {
    // Accepts:
    // - https://www.youtube.com/embed/ID
    // - https://youtu.be/ID
    // - https://www.youtube.com/watch?v=ID
    // Returns https://www.youtube.com/embed/ID
    if (!url) return '';

    try {
      const u = new URL(url);
      if (u.pathname.startsWith('/embed/')) return `https://www.youtube.com${u.pathname}`;
      if (u.hostname.includes('youtu.be')) return `https://www.youtube.com/embed/${u.pathname.replace('/', '')}`;
      if (u.searchParams.get('v')) return `https://www.youtube.com/embed/${u.searchParams.get('v')}`;
      // fallback
      return url;
    } catch {
      return url;
    }
  }

  function ytSrcForPhone(item) {
    const base = toEmbedUrl(item.youtube);
    // autoplay + mute + loop-like feel (playlist=id is common trick)
    const id = base.split('/embed/')[1]?.split('?')[0] || '';
    const params = new URLSearchParams({
      autoplay: '1',
      mute: '1',
      controls: '0',
      rel: '0',
      playsinline: '1',
      modestbranding: '1',
      iv_load_policy: '3',
      // loop trick
      loop: '1',
      playlist: id,
    });
    return `${base}?${params.toString()}`;
  }

  function ytSrcForModal(item) {
    const base = toEmbedUrl(item.youtube);
    const params = new URLSearchParams({
      autoplay: '1',
      mute: '0',
      controls: '1',
      rel: '0',
      playsinline: '1',
      modestbranding: '1',
    });
    return `${base}?${params.toString()}`;
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function cardInnerHTML(item) {
    const name = escapeHtml(item.name || '');
    const role = escapeHtml(item.role || '');
    const group = escapeHtml(item.group || '');
    const age = item.age ? `, ${escapeHtml(item.age)}` : '';

    return `
      <div class="gls-tv-card-top">
        <div class="gls-tv-avatar">
          <img src="${escapeHtml(item.poster)}" alt="${name}" loading="lazy" draggable="false">
          <div class="gls-tv-play" aria-hidden="true">
            <button class="gls-tv-play-btn" type="button" data-open aria-label="Lire la vidéo">
              <span class="gls-tv-play-tri" aria-hidden="true"></span>
            </button>
          </div>
        </div>
      </div>

      <div class="gls-tv-card-bottom">
        <p class="gls-tv-name">${name}${age}</p>
        <p class="gls-tv-role">${role}</p>
        <span class="gls-tv-pill">${group}</span>
      </div>
    `;
  }

  function renderDeck() {
    if (!cards.length) return;

    cards.forEach((cardEl, slotIndex) => {
      const pos = posOrder[slotIndex] || 'center';
      const itemIndex = indexForPos(pos, activeIndex);
      const item = items[itemIndex];

      // Position attribute for CSS layout
      cardEl.dataset.pos = pos;
      // Keep a link to which item this card represents
      cardEl.dataset.index = String(itemIndex);

      // Apply one of the 4 background classes in a stable way per itemIndex
      cardEl.classList.remove(...bgClasses);
      cardEl.classList.add(bgClasses[itemIndex % bgClasses.length]);

      // Fill content
      cardEl.innerHTML = cardInnerHTML(item);

      // Make sure it can be clicked (your CSS already sets pointer-events)
      cardEl.setAttribute('role', 'button');
      cardEl.setAttribute('tabindex', '0');
      cardEl.setAttribute('aria-label', `Voir le témoignage de ${item?.name || ''}`);
    });
  }

  function renderPhone() {
    if (!phoneSlide) return;
    const item = items[activeIndex];

    // Replace the phone video with autoplay muted iframe
    phoneSlide.innerHTML = `
      <div class="gls-tv-reel" data-open aria-label="Ouvrir la vidéo">
        <iframe
          class="gls-tv-yt--reel"
          src="${escapeHtml(ytSrcForPhone(item))}"
          title="${escapeHtml(item?.name || 'Témoignage')}"
          allow="autoplay; encrypted-media; picture-in-picture"
          allowfullscreen
          loading="lazy"
        ></iframe>
      </div>
    `;
  }

  function renderAll() {
    renderDeck();
    renderPhone();
  }

  // ---- Modal ----
  function openModalForIndex(index) {
    if (!modal || !modalDialog) return;
    const item = items[clampIndex(index)];

    // Remove old iframe if any
    const old = modalDialog.querySelector('iframe.gls-tv-yt--modal');
    if (old) old.remove();

    const iframe = document.createElement('iframe');
    iframe.className = 'gls-tv-yt--modal';
    iframe.src = ytSrcForModal(item);
    iframe.title = item?.name ? `Témoignage — ${item.name}` : 'Témoignage';
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
    iframe.allowFullscreen = true;

    modalDialog.appendChild(iframe);
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    // Lock scroll
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    if (!modal || !modalDialog) return;

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');

    const iframe = modalDialog.querySelector('iframe.gls-tv-yt--modal');
    if (iframe) {
      iframe.src = 'about:blank'; // stop playback
      iframe.remove();
    }

    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
  }

  // ---- Navigation ----
  function goNext() {
    activeIndex = clampIndex(activeIndex + 1);
    renderAll();
  }

  function goPrev() {
    activeIndex = clampIndex(activeIndex - 1);
    renderAll();
  }

  // Buttons
  btnNext?.addEventListener('click', (e) => {
    e.preventDefault();
    goNext();
  });

  btnPrev?.addEventListener('click', (e) => {
    e.preventDefault();
    goPrev();
  });

  // Keyboard on buttons (optional)
  btnNext?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      goNext();
    }
  });

  btnPrev?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      goPrev();
    }
  });

  // Click on cards: set activeIndex and re-render (SYNC FIX)
  if (deck) {
    deck.addEventListener('click', (e) => {
      const target = e.target;

      // If click on play button or avatar area => open modal for that card item
      const openBtn = target.closest('[data-open]');
      const cardEl = target.closest('.gls-tv-card');

      if (cardEl) {
        const idx = Number(cardEl.dataset.index || '0');

        // If it was a "play" click, open modal
        if (openBtn) {
          openModalForIndex(idx);
          return;
        }

        // Otherwise set active and sync deck + phone
        activeIndex = clampIndex(idx);
        renderAll();
      }
    });

    // Keyboard: Enter/Space on focused card
    deck.addEventListener('keydown', (e) => {
      const cardEl = e.target.closest('.gls-tv-card');
      if (!cardEl) return;

      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const idx = Number(cardEl.dataset.index || '0');
        activeIndex = clampIndex(idx);
        renderAll();
      }
    });
  }

  // Click on phone => open modal for active
  root.addEventListener('click', (e) => {
    const openFromPhone = e.target.closest('.gls-tv-phone [data-open]');
    if (openFromPhone) {
      openModalForIndex(activeIndex);
    }
  });

  // Modal close handlers
  modalCloseEls.forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      closeModal();
    });
  });

  // ESC closes modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal?.classList.contains('is-open')) {
      closeModal();
    }
  });

  // Initial render
  renderAll();

  // ══════════════════════════════════════════════════════════════
  // iOS Status Bar - Dynamic Time
  // ══════════════════════════════════════════════════════════════
  const sbTimeEl = root.querySelector('[data-sb-time]');
  if (sbTimeEl) {
    const updateTime = () => {
      const now = new Date();
      const h = String(now.getHours()).padStart(2, '0');
      const m = String(now.getMinutes()).padStart(2, '0');
      sbTimeEl.textContent = `${h}:${m}`;
    };
    updateTime();
    setInterval(updateTime, 30000);
  }
})();