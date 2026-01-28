(() => {
  const section = document.querySelector('[data-hero]');
  if (!section) return;

  const canvas = document.createElement('canvas');
  canvas.className = 'hero-canvas';
  section.prepend(canvas);
  const ctx = canvas.getContext('2d');

  let width = 0, height = 0, dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
  const blobs = [];

  const colors = [
    [99,102,241],  // indigo
    [236,72,153],  // pink
    [34,197,94],   // green
  ];

  function resize() {
    const rect = section.getBoundingClientRect();
    width = Math.floor(rect.width);
    height = Math.floor(rect.height);
    canvas.width = Math.floor(width * dpr);
    canvas.height = Math.floor(height * dpr);
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }

  function makeBlob(ix) {
    const [r, g, b] = colors[ix % colors.length];
    return {
      x: Math.random() * width,
      y: Math.random() * height,
      vx: (Math.random() * 0.6 - 0.3),
      vy: (Math.random() * 0.6 - 0.3),
      radius: Math.random() * 140 + 140,
      color: `rgba(${r},${g},${b},0.16)`
    };
  }

  function init() {
    blobs.length = 0;
    for (let i = 0; i < 3; i++) blobs.push(makeBlob(i));
  }

  const mouse = { x: 0, y: 0, has: false };
  section.addEventListener('mousemove', (e) => {
    const rect = section.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
    mouse.has = true;
  });
  section.addEventListener('mouseleave', () => { mouse.has = false; });

  function step() {
    ctx.clearRect(0, 0, width, height);
    for (const b of blobs) {
      // Move
      b.x += b.vx;
      b.y += b.vy;
      if (b.x < -200) b.x = width + 200; if (b.x > width + 200) b.x = -200;
      if (b.y < -200) b.y = height + 200; if (b.y > height + 200) b.y = -200;

      // Gentle attraction to cursor
      if (mouse.has) {
        const dx = mouse.x - b.x;
        const dy = mouse.y - b.y;
        b.vx += dx * 0.0005; b.vy += dy * 0.0005;
        b.vx *= 0.98; b.vy *= 0.98;
      } else {
        b.vx *= 0.995; b.vy *= 0.995;
      }

      const grd = ctx.createRadialGradient(b.x, b.y, 0, b.x, b.y, b.radius);
      grd.addColorStop(0, b.color);
      grd.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = grd;
      ctx.beginPath();
      ctx.arc(b.x, b.y, b.radius, 0, Math.PI * 2);
      ctx.fill();
    }
    requestAnimationFrame(step);
  }

  const ro = new ResizeObserver(() => { resize(); init(); });
  ro.observe(section);
  resize();
  init();
  step();
})();

// CTA wave: smooth random colors
(() => {
  const el = document.querySelector('[data-cta-wave]');
  if (!el) return;

  function rand(min, max) { return Math.random() * (max - min) + min; }
  function hsl(h, s, l) { return `hsl(${Math.round(h)} ${Math.round(s)}% ${Math.round(l)}%)`; }

  function pickColors() {
    // яркие, но не кислотные
    const h1 = rand(0, 360);
    const h2 = (h1 + rand(60, 140)) % 360;
    const h3 = (h1 + rand(160, 260)) % 360;
    const s = rand(80, 95);
    const l1 = rand(60, 72);
    const l2 = rand(58, 70);
    const l3 = rand(60, 74);

    el.style.setProperty('--cta-c1', hsl(h1, s, l1));
    el.style.setProperty('--cta-c2', hsl(h2, s, l2));
    el.style.setProperty('--cta-c3', hsl(h3, s, l3));
  }

  // стартовые цвета + периодическая смена
  pickColors();
  setInterval(pickColors, 9000);
})();


