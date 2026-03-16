/* ══════════════════════════════════════════════════════════════
   GLS Testimonial Videos — Carousel only when cards exceed viewport
   ══════════════════════════════════════════════════════════════ */
(function () {
  var viewport = document.querySelector('.gls-tmv-viewport');
  var track = document.querySelector('.gls-tmv-track');
  if (!track || !viewport) return;

  var cards = Array.from(track.children);
  var prevBtn = document.querySelector('.gls-tmv-nav--prev');
  var nextBtn = document.querySelector('.gls-tmv-nav--next');
  var dotsWrap = document.querySelector('.gls-tmv-dots');
  var currentPage = 0;

  function getVisible() {
    var w = window.innerWidth;
    if (w <= 480) return 1;
    if (w <= 768) return 2;
    if (w <= 1100) return 3;
    return 4;
  }

  function needsCarousel() {
    return cards.length > getVisible();
  }

  function getTotalPages() {
    return Math.ceil(cards.length / getVisible());
  }

  function toggleControls() {
    var show = needsCarousel();
    prevBtn.hidden = !show;
    nextBtn.hidden = !show;
    dotsWrap.style.display = show ? 'flex' : 'none';
  }

  function render() {
    var visible = getVisible();
    var gap = 18;
    var cardWidth = (viewport.offsetWidth - gap * (visible - 1)) / visible;

    cards.forEach(function (card) {
      card.style.flex = '0 0 ' + cardWidth + 'px';
    });

    if (!needsCarousel()) {
      track.style.transform = 'translateX(0)';
      currentPage = 0;
      toggleControls();
      return;
    }

    toggleControls();

    var offset = currentPage * visible * (cardWidth + gap);
    track.style.transform = 'translateX(-' + offset + 'px)';
    track.style.transition = 'transform 0.5s cubic-bezier(.4,0,.2,1)';

    prevBtn.disabled = currentPage <= 0;
    nextBtn.disabled = currentPage >= getTotalPages() - 1;

    if (dotsWrap) {
      var dots = dotsWrap.querySelectorAll('.gls-tmv-dot-btn');
      dots.forEach(function (d, i) {
        d.classList.toggle('active', i === currentPage);
      });
    }
  }

  function buildDots() {
    if (!dotsWrap) return;
    dotsWrap.innerHTML = '';
    if (!needsCarousel()) return;
    var total = getTotalPages();
    for (var i = 0; i < total; i++) {
      var btn = document.createElement('button');
      btn.className = 'gls-tmv-dot-btn';
      btn.type = 'button';
      btn.setAttribute('aria-label', 'Page ' + (i + 1));
      btn.dataset.index = i;
      dotsWrap.appendChild(btn);
    }
    dotsWrap.addEventListener('click', function (e) {
      var dot = e.target.closest('.gls-tmv-dot-btn');
      if (dot) {
        currentPage = parseInt(dot.dataset.index, 10);
        render();
      }
    });
  }

  prevBtn.addEventListener('click', function () {
    if (currentPage > 0) { currentPage--; render(); }
  });

  nextBtn.addEventListener('click', function () {
    if (currentPage < getTotalPages() - 1) { currentPage++; render(); }
  });

  // Touch swipe
  var startX = 0;
  var dragging = false;

  viewport.addEventListener('touchstart', function (e) {
    startX = e.touches[0].clientX;
    dragging = true;
  }, { passive: true });

  viewport.addEventListener('touchend', function (e) {
    if (!dragging) return;
    dragging = false;
    var diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) {
      if (diff > 0 && currentPage < getTotalPages() - 1) currentPage++;
      else if (diff < 0 && currentPage > 0) currentPage--;
      render();
    }
  }, { passive: true });

  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      var maxPage = getTotalPages() - 1;
      if (currentPage > maxPage) currentPage = Math.max(0, maxPage);
      buildDots();
      render();
    }, 150);
  });

  buildDots();
  render();
})();
