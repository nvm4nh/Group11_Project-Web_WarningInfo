/* =========================================================
   WarningInfo – Hiệu ứng giao diện "tán rừng" (đi kèm assets/css/forest.css)
   Không dùng thư viện ngoài: chỉ requestAnimationFrame + biến CSS, nên trang vẫn chạy khi không có mạng CDN.

     1. Con trỏ chuột  → vệt nắng trên nền trang, bóng lá trượt nhẹ ngược hướng
     2. Cuộn trang     → cành cây trên thanh menu dài ra, lá nghiêng theo "gió" (tốc độ cuộn),
                         [data-scrub] nhận tiến độ --p (dây leo ở trang chủ)
     3. Hero trang chủ → mặt trời + tia nắng đi theo con trỏ, các lớp cây trượt lệch nhau, bụi phấn bay
     4. Nhấp chuột     → quầng sáng và vài chiếc lá bay ra từ điểm nhấp

   Tự tắt chuyển động khi người dùng bật "giảm chuyển động"; hiệu ứng theo chuột chỉ bật trên máy có chuột.
   ========================================================= */
(function () {
  'use strict';

  var root = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var pointerFx = finePointer && !reduce;
  root.classList.toggle('wi-pointer', pointerFx);

  var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };
  var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };

  var ambient = $('.wi-ambient');
  var growth = $('.wi-growth');
  var hero = $('[data-forest-hero]');
  var swayEls = $$('.sway');
  var scrubEls = $$('[data-scrub]');

  /* ---------- Trạng thái dùng chung ---------- */
  var vw = window.innerWidth, vh = window.innerHeight;
  var ptr = { x: vw * 0.7, y: vh * 0.18, tx: vw * 0.7, ty: vh * 0.18, seen: false };   // con trỏ (toạ độ màn hình), đã làm mượt
  var wind = 0, windTarget = 0;
  var lastY = window.scrollY, lastT = performance.now();
  var running = false;

  /* ---------- Hero ---------- */
  var heroState = null;
  if (hero) {
    heroState = {
      scene: $('.fh-scene', hero),
      layers: $$('.fh-layer', hero).map(function (el) { return { el: el, d: parseFloat(el.dataset.depth) || 0 }; }),
      canvas: $('.fh-motes', hero),
      rect: null, visible: true,
      // vị trí mặt trời trong hero (0–1); mở màn: mọc từ chân trời lên điểm neo 72% / 22%
      // (điểm neo đọc từ biến CSS --ax / --ay của .fh-scene: màn hình hẹp neo cao hơn để nắng không đè lên chữ)
      ax: 0.72, ay: 0.22, sx: 0.6, sy: 0.9, tx: 0.72, ty: 0.22, placed: false,
      motes: [], ctx: null, dpr: 1,
    };
  }

  function measure() {
    vw = window.innerWidth; vh = window.innerHeight;
    if (heroState) {
      var hs = heroState, cs = getComputedStyle(hs.scene);
      hs.rect = hero.getBoundingClientRect();
      hs.top = hs.rect.top + window.scrollY;
      hs.ax = parseFloat(cs.getPropertyValue('--ax')) || 0.72;
      hs.ay = parseFloat(cs.getPropertyValue('--ay')) || 0.22;
      if (!(pointerFx && ptr.seen)) { hs.tx = hs.ax; hs.ty = hs.ay; }
      if (!hs.placed) { hs.placed = true; hs.sx = reduce ? hs.ax : hs.ax - 0.12; hs.sy = reduce ? hs.ay : 0.9; }
      sizeCanvas();
    }
  }

  /* ---------- Bụi phấn / đom đóm trong hero (canvas) ---------- */
  function sizeCanvas() {
    var h = heroState, c = h.canvas;
    if (!c || reduce) return;
    h.dpr = Math.min(window.devicePixelRatio || 1, 2);
    var w = h.rect.width, ht = h.rect.height;
    c.width = Math.round(w * h.dpr); c.height = Math.round(ht * h.dpr);
    h.ctx = c.getContext('2d');
    var count = clamp(Math.round(w / 34), 14, 46);
    if (h.motes.length !== count) {
      h.motes = [];
      for (var i = 0; i < count; i++) h.motes.push(newMote(w, ht, true));
    }
  }
  function newMote(w, ht, anywhere) {
    return {
      x: Math.random() * w,
      y: anywhere ? Math.random() * ht : ht + 10,
      r: 0.8 + Math.random() * 1.9,
      vy: -(6 + Math.random() * 16),            // px / giây, bay lên
      drift: Math.random() * Math.PI * 2,
      speed: 0.4 + Math.random() * 0.9,
      vx: 0, vyExtra: 0,
    };
  }
  function drawMotes(dt, t) {
    var h = heroState, ctx = h.ctx;
    if (!ctx) return;
    var w = h.rect.width, ht = h.rect.height;
    var sunX = h.sx * w, sunY = h.sy * ht;
    ctx.setTransform(h.dpr, 0, 0, h.dpr, 0, 0);
    ctx.clearRect(0, 0, w, ht);
    for (var i = 0; i < h.motes.length; i++) {
      var m = h.motes[i];
      m.x += (Math.sin(t / 1000 * m.speed + m.drift) * 9 + m.vx) * dt;
      m.y += (m.vy + m.vyExtra) * dt;
      m.vx *= 0.94; m.vyExtra *= 0.94;
      if (m.y < -12 || m.x < -20 || m.x > w + 20) { h.motes[i] = newMote(w, ht, false); continue; }
      // càng gần mặt trời (con trỏ) càng sáng
      var d = Math.hypot(m.x - sunX, m.y - sunY);
      var glow = clamp(1 - d / 420, 0, 1);
      var a = 0.16 + glow * 0.7;
      ctx.beginPath();
      ctx.fillStyle = 'rgba(255, 236, 160, ' + (a * 0.22).toFixed(3) + ')';
      ctx.arc(m.x, m.y, m.r * (3 + glow * 3), 0, 6.283);
      ctx.fill();
      ctx.beginPath();
      ctx.fillStyle = 'rgba(255, 246, 205, ' + a.toFixed(3) + ')';
      ctx.arc(m.x, m.y, m.r, 0, 6.283);
      ctx.fill();
    }
  }
  /* Nhấp trong hero: bụi phấn gần đó bị thổi dạt ra */
  function pushMotes(clientX, clientY) {
    var h = heroState;
    if (!h || !h.motes.length) return;
    var x = clientX - h.rect.left, y = clientY - h.rect.top;
    h.motes.forEach(function (m) {
      var dx = m.x - x, dy = m.y - y, d = Math.hypot(dx, dy) || 1;
      if (d < 260) { var f = (1 - d / 260) * 260; m.vx += dx / d * f; m.vyExtra += dy / d * f; }
    });
  }

  /* ---------- Vòng lặp khung hình: chỉ chạy khi còn thứ đang chuyển động ---------- */
  function frame(now) {
    var dt = Math.min((now - lastT) / 1000, 0.05);
    lastT = now;
    var busy = false;

    // 1. Con trỏ (làm mượt)
    var k = 1 - Math.pow(0.001, dt);            // ~ bám theo trong khoảng 0,3 giây
    var dxp = ptr.tx - ptr.x, dyp = ptr.ty - ptr.y;
    if (Math.abs(dxp) > 0.3 || Math.abs(dyp) > 0.3) { ptr.x += dxp * k; ptr.y += dyp * k; busy = true; }
    if (ambient && pointerFx) {
      ambient.style.setProperty('--mx', ptr.x.toFixed(1) + 'px');
      ambient.style.setProperty('--my', ptr.y.toFixed(1) + 'px');
      ambient.style.setProperty('--px', ((ptr.x / vw) * 2 - 1).toFixed(3));
      ambient.style.setProperty('--py', ((ptr.y / vh) * 2 - 1).toFixed(3));
    }

    // 2. Gió theo tốc độ cuộn (tắt dần)
    wind += (windTarget - wind) * (1 - Math.pow(0.0005, dt));
    windTarget *= Math.pow(0.02, dt);
    if (Math.abs(wind) > 0.002 || Math.abs(windTarget) > 0.002) busy = true; else wind = 0;
    if (!reduce) {
      var w = wind.toFixed(3);
      for (var i = 0; i < swayEls.length; i++) swayEls[i].style.setProperty('--wind', w);
    }

    // 3. Hero
    var h = heroState;
    if (h && h.visible) {
      if (pointerFx && ptr.seen) {
        h.tx = clamp((ptr.tx - h.rect.left) / h.rect.width, 0.04, 0.96);
        h.ty = clamp((ptr.ty - (h.top - window.scrollY)) / h.rect.height, 0.05, 0.8);
      }
      var ks = 1 - Math.pow(0.02, dt);
      var ddx = h.tx - h.sx, ddy = h.ty - h.sy;
      if (Math.abs(ddx) > 0.0004 || Math.abs(ddy) > 0.0004) { h.sx += ddx * ks; h.sy += ddy * ks; busy = true; }
      h.scene.style.setProperty('--sx', ((h.sx - h.ax) * h.rect.width).toFixed(1) + 'px');
      h.scene.style.setProperty('--sy', ((h.sy - h.ay) * h.rect.height).toFixed(1) + 'px');
      if (!reduce) {
        var ox = pointerFx ? (h.sx - 0.5) : 0, oy = pointerFx ? (h.sy - 0.4) : 0;
        var sc = clamp(window.scrollY, 0, h.rect.height);
        for (var j = 0; j < h.layers.length; j++) {
          var L = h.layers[j];
          L.el.style.translate = (-ox * L.d * 150).toFixed(1) + 'px ' + (-oy * L.d * 70 + sc * L.d * 0.9).toFixed(1) + 'px';
        }
        drawMotes(dt, now);
        busy = true;                             // bụi phấn bay liên tục khi hero còn trong màn hình
      }
    }

    if (busy && !document.hidden) requestAnimationFrame(frame); else running = false;
  }
  function kick() {
    if (running) return;
    running = true; lastT = performance.now();
    requestAnimationFrame(frame);
  }

  /* ---------- Cuộn trang ---------- */
  function onScroll() {
    var y = window.scrollY, now = performance.now();
    var max = Math.max(1, document.documentElement.scrollHeight - vh);
    if (growth) growth.style.setProperty('--scroll', clamp(y / max, 0, 1).toFixed(4));

    // "gió": tốc độ cuộn (px/ms) → -1..1; cuộn xuống thì lá nghiêng một phía, cuộn lên nghiêng phía kia
    var v = (y - lastY) / Math.max(8, now - lastScrollT);
    lastY = y; lastScrollT = now;
    if (!reduce) windTarget = clamp(windTarget + v * 0.5, -1, 1);

    // tiến độ từng khối [data-scrub]: 0 khi mép trên chạm 88% chiều cao màn hình, 1 sau khi cuộn thêm 55% màn hình
    // (chỉ tăng, không lùi: dây leo đã mọc thì giữ nguyên khi cuộn ngược lên)
    for (var i = 0; i < scrubEls.length; i++) {
      var el = scrubEls[i];
      var r = el.getBoundingClientRect();
      var p = reduce ? 1 : clamp((vh * 0.88 - r.top) / (vh * 0.55), 0, 1);
      if (p > (el._wiP || 0) || el._wiP === undefined) {
        el._wiP = Math.max(p, el._wiP || 0);
        el.style.setProperty('--p', el._wiP.toFixed(4));
      }
    }
    kick();
  }
  var lastScrollT = performance.now();

  /* ---------- Nhấp chuột: quầng sáng + lá bay ---------- */
  var fxLayer = null;
  function burst(x, y) {
    if (!fxLayer) {
      fxLayer = document.createElement('div');
      fxLayer.className = 'wi-fx';
      fxLayer.setAttribute('aria-hidden', 'true');
      document.body.appendChild(fxLayer);
    }
    var b = document.createElement('span');
    b.className = 'wi-burst';
    b.style.left = x + 'px'; b.style.top = y + 'px';
    var html = '<i class="ring"></i>';
    var n = 6;
    for (var i = 0; i < n; i++) {
      var ang = (i / n) * Math.PI * 2 + Math.random() * 0.9;
      var dist = 46 + Math.random() * 54;
      html += '<i class="lf" style="--dx:' + (Math.cos(ang) * dist).toFixed(0) + 'px;--dy:' + (Math.sin(ang) * dist + 34).toFixed(0)
        + 'px;--r:' + (Math.random() * 540 - 270).toFixed(0) + 'deg;--s:' + (0.7 + Math.random() * 0.8).toFixed(2)
        + ';--t:' + (0.75 + Math.random() * 0.4).toFixed(2) + 's"></i>';
    }
    b.innerHTML = html;
    fxLayer.appendChild(b);
    setTimeout(function () { b.remove(); }, 1250);
  }

  /* ---------- Gắn sự kiện ---------- */
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', function () { measure(); onScroll(); }, { passive: true });
  document.addEventListener('visibilitychange', function () { if (!document.hidden) kick(); });

  if (pointerFx) {
    window.addEventListener('pointermove', function (e) {
      if (e.pointerType && e.pointerType !== 'mouse') return;
      ptr.tx = e.clientX; ptr.ty = e.clientY; ptr.seen = true;
      kick();
    }, { passive: true });
    document.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      if (e.target.closest && e.target.closest('.leaflet-container')) return;   // kéo bản đồ: không bắn hiệu ứng
      burst(e.clientX, e.clientY);
      if (heroState && heroState.visible) pushMotes(e.clientX, e.clientY);
    }, { passive: true });
  }

  // Chiều cao hero đổi sau khi phông chữ / ảnh tải xong → đo lại để canvas và toạ độ con trỏ khớp
  if (heroState && 'ResizeObserver' in window) {
    new ResizeObserver(function () { measure(); kick(); }).observe(hero);
  }
  if (heroState && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      heroState.visible = entries[0].isIntersecting;
      if (heroState.visible) { measure(); kick(); }
    }, { threshold: 0 }).observe(hero);
  }

  measure();
  onScroll();
  // Mở màn hero: đợi 2 khung hình để trình duyệt vẽ trạng thái đầu rồi mới chuyển sang trạng thái hoàn chỉnh
  if (hero) requestAnimationFrame(function () { requestAnimationFrame(function () { hero.classList.add('is-ready'); }); });
  kick();

  window.WIForest = { burst: burst, reducedMotion: reduce, pointerFx: pointerFx };
})();
