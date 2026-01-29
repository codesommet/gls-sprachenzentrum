document.addEventListener('DOMContentLoaded', () => {
  const STORAGE_KEY = 'studienkolleg_favorites';

  /* ===============================
     Helpers
  =============================== */
  const getFavorites = () => {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      const arr = JSON.parse(raw);
      return Array.isArray(arr) ? arr.map(String) : [];
    } catch (e) {
      return [];
    }
  };

  const saveFavorites = (favorites) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(favorites));
  };

  const setBtnState = (el, active) => {
    el.classList.toggle('active', active);

    // Accessibility (useful for <button> on show page)
    if (el.tagName === 'BUTTON' || el.getAttribute('role') === 'button') {
      el.setAttribute('aria-pressed', active ? 'true' : 'false');
    }

    // If the button has a label span (show page), update text
    const label = el.querySelector('[data-fav-label]');
    if (label) {
      const offText = label.dataset.text || 'Add to Favorites';
      const onText = label.dataset.activeText || 'Added';
      label.textContent = active ? onText : offText;
    }
  };

  /* ===============================
     Init favorites state
  =============================== */
  const initFavorites = () => {
    const favorites = getFavorites();

    document.querySelectorAll('.favorite-btn[data-id]').forEach((btn) => {
      const id = String(btn.dataset.id);
      setBtnState(btn, favorites.includes(id));
    });
  };

  /* ===============================
     Toggle favorite (state + storage)
  =============================== */
  const toggleFavorite = (id) => {
    const strId = String(id);
    let favorites = getFavorites();

    if (favorites.includes(strId)) {
      favorites = favorites.filter((item) => item !== strId);
      saveFavorites(favorites);
      return false; // now inactive
    }

    favorites.push(strId);
    saveFavorites(favorites);
    return true; // now active
  };

  /* ===============================
     Bind events (delegation: works on index + show)
  =============================== */
  const bindFavoriteEvents = () => {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.favorite-btn[data-id]');
      if (!btn) return;

      e.preventDefault();

      const id = btn.dataset.id;
      const active = toggleFavorite(id);

      // Update all instances of the same item on the page
      document
        .querySelectorAll(`.favorite-btn[data-id="${CSS.escape(String(id))}"]`)
        .forEach((el) => setBtnState(el, active));
    });
  };

  /* ===============================
     Init
  =============================== */
  initFavorites();
  bindFavoriteEvents();
});
