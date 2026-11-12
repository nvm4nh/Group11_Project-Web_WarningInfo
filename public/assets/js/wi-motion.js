/* =========================================================
   WarningInfo – Chuyển động 3D và nút "chất lỏng" (đi kèm assets/css/motion.css)
   Viết bằng JavaScript thuần (không React, không thư viện). Phong cách lấy cảm hứng từ các component
   trên 21st.dev (mã viết mới, không sao chép):
     - Tilt Card            – https://21st.dev/@sidiora-labs/components/tilt-card (MIT)
     - Coverflow Carousel   – https://21st.dev/@educalvolpz/components/coverflow-carousel (MIT)
     - Scroll Stacking Cards – https://21st.dev/@kedhareswer/components/tilt-stack-scroll (MIT)
     - Liquid Metal Button  – https://21st.dev/@johuniq/components/liquid-metal-button (MIT)

   1. Thẻ nghiêng 3D theo con trỏ + vệt sáng lướt (chỉ máy có chuột)
   2. Băng chuyền 3D "coverflow" cho khối Cảnh báo mới nhất ([data-coverflow])
   3. Các bước "Cách hoạt động" nghiêng vào rồi xếp chồng khi cuộn ([data-stack])
   4. Nút biểu tượng kiểu "gooey" (giọt nước tách ra / nhập lại khi rê chuột)
   5. Nút kính lỏng: vệt phản chiếu chạy theo con trỏ
   ========================================================= */
(function () {
  'use strict';

  var root = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var pointerFx = fine && !reduce;
  var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

  /* ---------- 1. Thẻ nghiêng 3D ----------
     Dùng một bộ lắng nghe chung cho cả trang (event delegation) nên thẻ do AJAX vẽ lại (/report) vẫn nghiêng được. */
  var TILT = '.alert-card, .category-card, .hover-shadow, .task-card, .officer-stat, .card.border-start, .hero-panel';
  if (pointerFx) {
    var tiltEl = null, tiltEvt = null, tiltQueued = false;
    var resetTilt = function (el) {
      el.classList.remove('wi-tilt-on');
      el.style.removeProperty('--rx'); el.style.removeProperty('--ry');
    };
    var applyTilt = function () {
      tiltQueued = false;
      var e = tiltEvt, el = tiltEl;
      if (!el || !e) return;
      var r = el.getBoundingClientRect();
      var x = clamp((e.clientX - r.left) / r.width, 0, 1), y = clamp((e.clientY - r.top) / r.height, 0, 1);
      // thẻ lớn nghiêng ít hơn để chữ vẫn dễ đọc
      var max = r.width > 520 ? 3 : el.classList.contains('hero-panel') ? 7 : 9;
      el.style.setProperty('--ry', ((x - 0.5) * 2 * max).toFixed(2) + 'deg');
      el.style.setProperty('--rx', ((0.5 - y) * 2 * max).toFixed(2) + 'deg');
      el.style.setProperty('--gx', (x * 100).toFixed(1) + '%');
      el.style.setProperty('--gy', (y * 100).toFixed(1) + '%');
    };
    document.addEventListener('pointermove', function (e) {
      if (e.pointerType !== 'mouse') return;
      var el = e.target.closest ? e.target.closest(TILT) : null;
      if (el && el.closest('.cf-slide:not(.is-active)')) el = null;   // trong coverflow chỉ thẻ ở giữa mới nghiêng
      if (tiltEl && tiltEl !== el) resetTilt(tiltEl);
      tiltEl = el; tiltEvt = e;
      if (!el) return;
      el.classList.add('wi-tilt-on');
      if (!tiltQueued) { tiltQueued = true; requestAnimationFrame(applyTilt); }
    }, { passive: true });
    document.addEventListener('pointerleave', function () { if (tiltEl) { resetTilt(tiltEl); tiltEl = null; } });
    window.addEventListener('blur', function () { if (tiltEl) { resetTilt(tiltEl); tiltEl = null; } });
  }

  /* ---------- 2. Coverflow ----------
     Nền tảng là một dải cuộn ngang có scroll-snap (vuốt, cuộn bằng touchpad / bàn phím đều chạy sẵn);
     JS chỉ tính góc xoay / độ sâu của từng thẻ theo khoảng cách tới giữa, thêm kéo bằng chuột, nút và chấm. */
  $$('[data-coverflow]').forEach(function (cf) {
    var track = cf.querySelector('.cf-track');
    var slides = $$('.cf-slide', track);
    if (!track || slides.length < 2) { cf.classList.add('is-static'); return; }
    var prev = cf.querySelector('.cf-prev'), next = cf.querySelector('.cf-next'), dotsBox = cf.querySelector('.cf-dots');
    var active = -1;

    var dots = slides.map(function (s, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'cf-dot';
      b.setAttribute('aria-label', 'Xem cảnh báo ' + (i + 1) + ' / ' + slides.length);
      b.addEventListener('click', function () { go(i); });
      if (dotsBox) dotsBox.appendChild(b);
      return b;
    });

    function center(i) { var s = slides[i]; return s.offsetLeft + s.offsetWidth / 2 - track.clientWidth / 2; }
    function go(i) {
      i = clamp(i, 0, slides.length - 1);
      track.scrollTo({ left: center(i), behavior: reduce ? 'auto' : 'smooth' });
    }

    var queued = false;
    function layout() {
      queued = false;
      var mid = track.scrollLeft + track.clientWidth / 2, best = 0, bestD = Infinity;
      var narrow = track.clientWidth < 600;
      slides.forEach(function (s, i) {
        var c = s.offsetLeft + s.offsetWidth / 2;
        var o = (c - mid) / s.offsetWidth;            // -1 = thẻ bên trái liền kề, 0 = giữa, 1 = bên phải
        var a = Math.abs(o);
        if (a < bestD) { bestD = a; best = i; }
        var oc = clamp(o, -2.2, 2.2);
        s.style.setProperty('--cf-ry', (clamp(o, -1, 1) * (narrow ? -26 : -42)).toFixed(2) + 'deg');
        s.style.setProperty('--cf-tz', (-Math.min(a, 2.2) * (narrow ? 70 : 150)).toFixed(1) + 'px');
        s.style.setProperty('--cf-tx', (-oc * (narrow ? 8 : 18)).toFixed(1) + '%');
        s.style.setProperty('--cf-dim', Math.min(a, 1.6).toFixed(3));
        s.style.zIndex = String(100 - Math.round(a * 10));
      });
      if (best !== active) {
        active = best;
        slides.forEach(function (s, i) {
          s.classList.toggle('is-active', i === active);
          s.setAttribute('aria-hidden', 'false');
        });
        dots.forEach(function (d, i) { d.toggleAttribute('aria-current', i === active); });
        if (prev) prev.disabled = active === 0;
        if (next) next.disabled = active === slides.length - 1;
      }
    }
    function queue() { if (!queued) { queued = true; requestAnimationFrame(layout); } }

    track.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue, { passive: true });
    if (prev) prev.addEventListener('click', function () { go(active - 1); });
    if (next) next.addEventListener('click', function () { go(active + 1); });
    track.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); go(active + 1); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); go(active - 1); }
    });
    // Dùng phím Tab tới một thẻ ở rìa → đưa thẻ đó vào giữa
    track.addEventListener('focusin', function (e) {
      var s = e.target.closest('.cf-slide');
      if (s) go(slides.indexOf(s));
    });
    // Bấm vào thẻ ở rìa: chỉ đưa nó vào giữa (không mở trang chi tiết)
    track.addEventListener('click', function (e) {
      var s = e.target.closest('.cf-slide');
      if (dragMoved) { e.preventDefault(); e.stopPropagation(); return; }
      if (s && !s.classList.contains('is-active')) { e.preventDefault(); go(slides.indexOf(s)); }
    }, true);

    // Kéo bằng chuột (vuốt trên cảm ứng do trình duyệt tự xử lý)
    var dragging = false, dragMoved = false, startX = 0, startLeft = 0;
    track.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      dragging = true; dragMoved = false; startX = e.clientX; startLeft = track.scrollLeft;
    });
    window.addEventListener('pointermove', function (e) {
      if (!dragging) return;
      var dx = e.clientX - startX;
      if (!dragMoved && Math.abs(dx) > 6) { dragMoved = true; cf.classList.add('is-dragging'); }
      if (dragMoved) track.scrollLeft = startLeft - dx;
    });
    window.addEventListener('pointerup', function () {
      if (!dragging) return;
      dragging = false;
      if (dragMoved) {
        cf.classList.remove('is-dragging');
        go(active);
        setTimeout(function () { dragMoved = false; }, 0);   // chặn cú click ngay sau khi thả chuột
      }
    });
    track.addEventListener('dragstart', function (e) { e.preventDefault(); });

    cf.classList.add('is-ready');
    layout();
    // Bắt đầu ở thẻ thứ 2 (nếu có) để hai bên đều có thẻ, nhìn rõ hiệu ứng 3D
    track.scrollLeft = center(Math.min(1, slides.length - 1));
    layout();
  });

  /* ---------- 3. Các bước xếp chồng khi cuộn ----------
     Thẻ dính (position: sticky) nằm chồng lên nhau; JS ghi 2 biến cho từng thẻ:
       --in    (1 → 0): thẻ đang trồi lên từ dưới, nghiêng ngửa rồi nằm phẳng
       --cover (0 → 1): thẻ đang bị thẻ sau che dần, thu nhỏ và tối đi */
  $$('[data-stack]').forEach(function (list) {
    var cards = $$('.stack-card', list);
    if (!cards.length || reduce) return;
    var queued = false;
    function update() {
      queued = false;
      var vh = window.innerHeight;
      var rects = cards.map(function (c) { return c.getBoundingClientRect(); });
      cards.forEach(function (c, i) {
        var r = rects[i];
        var inn = clamp((r.top - vh * 0.42) / (vh * 0.5), 0, 1);
        var cover = 0;
        if (i < cards.length - 1) {
          var gap = rects[i + 1].top - r.top;
          cover = clamp(1 - (gap - 24) / Math.max(1, r.height * 0.9), 0, 1);
        }
        c.style.setProperty('--in', inn.toFixed(3));
        c.style.setProperty('--cover', cover.toFixed(3));
      });
    }
    function queue() { if (!queued) { queued = true; requestAnimationFrame(update); } }
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue, { passive: true });
    list.classList.add('is-live');
    update();
  });

  /* ---------- 4. Nút biểu tượng "gooey" ----------
     Chèn lớp nền gồm 1 hình tròn + 3 giọt nhỏ; bộ lọc SVG #wi-goo (layouts/_forest.php) làm các giọt dính vào nhau */
  $$('.nav-search-btn, .notif-bell, .back-to-top').forEach(function (btn) {
    if (btn.querySelector('.goo')) return;
    var g = document.createElement('span');
    g.className = 'goo';
    g.setAttribute('aria-hidden', 'true');
    g.innerHTML = '<span class="goo-core"></span><span class="goo-drop d1"></span><span class="goo-drop d2"></span><span class="goo-drop d3"></span>';
    btn.insertBefore(g, btn.firstChild);
    btn.classList.add('has-goo');
  });

  /* ---------- 5. Nút kính lỏng: vệt phản chiếu theo con trỏ ---------- */
  if (pointerFx) {
    document.addEventListener('pointermove', function (e) {
      var b = e.target.closest ? e.target.closest('.btn-outline-light') : null;
      if (!b) return;
      var r = b.getBoundingClientRect();
      b.style.setProperty('--gx', ((e.clientX - r.left) / r.width * 100).toFixed(1) + '%');
      b.style.setProperty('--gy', ((e.clientY - r.top) / r.height * 100).toFixed(1) + '%');
    }, { passive: true });
  }
  // Khúc xạ nền phía sau (backdrop-filter dùng bộ lọc SVG) hiện chỉ chạy đúng trên trình duyệt nhân Chromium
  var brands = (navigator.userAgentData && navigator.userAgentData.brands) || [];
  if (brands.some(function (b) { return /Chromium/.test(b.brand); })) root.classList.add('wi-refract');
})();
