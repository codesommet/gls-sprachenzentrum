/* =========================================================
   NEW WEBSITE CELEBRATION – Confetti burst from center
   Shows once per session
   ========================================================= */
(function () {
  // TODO: re-enable after testing
  // if (sessionStorage.getItem('gls_celebrated')) return;
  // sessionStorage.setItem('gls_celebrated', '1');

  var canvas = document.querySelector('.celebration-confetti');
  if (!canvas) return;
  var ctx = canvas.getContext('2d');

  function resize() {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  var colors = [
    '#2ea043', '#56d364', '#1a7f37',
    '#3b82f6', '#60a5fa',
    '#f59e0b', '#fbbf24',
    '#a855f7', '#c084fc',
    '#ef4444', '#f87171',
    '#ffffff'
  ];

  var PARTICLE_COUNT = 200;
  var GRAVITY = 0.12;
  var DRAG = 0.985;
  var particles = [];

  function rand(a, b) { return a + Math.random() * (b - a); }

  var cx = canvas.width / 2;
  var cy = canvas.height / 2;

  for (var i = 0; i < PARTICLE_COUNT; i++) {
    var angle = Math.random() * Math.PI * 2;
    var speed = rand(5, 16);
    particles.push({
      x: cx + rand(-20, 20),
      y: cy + rand(-20, 20),
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed - rand(2, 6),
      size: rand(4, 10),
      color: colors[Math.floor(Math.random() * colors.length)],
      shape: Math.floor(Math.random() * 3),
      rotation: rand(0, Math.PI * 2),
      rotationSpeed: rand(-0.15, 0.15),
      opacity: 1,
      decay: rand(0.004, 0.009)
    });
  }

  var running = true;

  function draw() {
    if (!running) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    var alive = 0;
    for (var i = 0; i < particles.length; i++) {
      var p = particles[i];
      p.vy += GRAVITY;
      p.vx *= DRAG;
      p.x += p.vx;
      p.y += p.vy;
      p.rotation += p.rotationSpeed;
      p.opacity -= p.decay;
      if (p.opacity <= 0) continue;
      alive++;

      ctx.save();
      ctx.globalAlpha = p.opacity;
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rotation);
      ctx.fillStyle = p.color;

      if (p.shape === 0) {
        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
      } else if (p.shape === 1) {
        ctx.beginPath();
        ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
        ctx.fill();
      } else {
        ctx.fillRect(-p.size / 2, -1, p.size, 2.5);
      }
      ctx.restore();
    }

    if (alive > 0) {
      requestAnimationFrame(draw);
    } else {
      cleanup();
    }
  }

  requestAnimationFrame(draw);

  function cleanup() {
    running = false;
    if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
  }
})();
