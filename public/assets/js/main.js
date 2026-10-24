/* =========================================================
   WarningInfo – Script giao diện chung (TV4)
   Navbar, hiệu ứng cuộn, đếm số, tooltip, thông báo toast (WI.toast).
   Các file khác dùng WI.toast(...) nên main.js phải nạp TRƯỚC.
   ========================================================= */
(function () {
  'use strict';

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  /* ---------- 1. Navbar đổ bóng khi cuộn + nút lên đầu trang ---------- */
  const navbar = $('.navbar-wi');
  const backTop = $('.back-to-top');
  const onScroll = () => {
    const y = window.scrollY;
    if (navbar) navbar.classList.toggle('scrolled', y > 10);
    if (backTop) backTop.classList.toggle('show', y > 400);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  if (backTop) backTop.addEventListener('click', () => window.scrollTo({ top: 0 }));

  /* ---------- 2. Tooltip Bootstrap ---------- */
  if (window.bootstrap) {
    $$('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
  }

  /* ---------- 3. Hiệu ứng xuất hiện khi cuộn (.reveal) ---------- */
  const revealEls = $$('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: 0.12 });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('in'));
  }

  /* ---------- 4. Đếm số tăng dần ([data-count]) ---------- */
  const fmt = (n) => n.toLocaleString('vi-VN');
  const counters = $$('[data-count]');
  const runCounter = (el) => {
    const target = parseInt(el.dataset.count, 10) || 0;
    const dur = 1200;
    const start = performance.now();
    const step = (t) => {
      const p = Math.min((t - start) / dur, 1);
      el.textContent = fmt(Math.round(target * (1 - Math.pow(1 - p, 3))));
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };
  if ('IntersectionObserver' in window && counters.length) {
    const co = new IntersectionObserver((entries) => {
      entries.forEach((e) => { if (e.isIntersecting) { runCounter(e.target); co.unobserve(e.target); } });
    }, { threshold: 0.5 });
    counters.forEach((el) => co.observe(el));
  } else {
    counters.forEach((el) => { el.textContent = fmt(parseInt(el.dataset.count, 10) || 0); });
  }

  /* ---------- 5. CSRF token (Milestone 3) ----------
     Token nằm ở <meta name="csrf-token"> trong header.
     - Form POST nào quên <?= csrf_field() ?> sẽ được tự gắn thêm ô ẩn (lớp dự phòng)
     - AJAX bằng jQuery tự gửi header X-CSRF-Token; fetch() thì dùng WI.csrfHeaders() */
  const csrfMeta = $('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
  const csrfHeaders = (extra = {}) => Object.assign({ 'X-CSRF-Token': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }, extra);

  if (csrfToken) {
    const attachToken = (form) => {
      if ((form.getAttribute('method') || '').toLowerCase() !== 'post') return;
      if (form.querySelector('input[name="csrf_token"]')) return;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'csrf_token';
      input.value = csrfToken;
      form.appendChild(input);
    };
    $$('form').forEach(attachToken);
    // Form được tạo động sau khi trang tải: gắn token ngay lúc bấm gửi
    document.addEventListener('submit', (e) => attachToken(e.target), true);
    if (window.jQuery) {
      window.jQuery.ajaxSetup({ headers: { 'X-CSRF-Token': csrfToken } });
    }
  }

  // Toast thông báo do máy chủ gửi (flash) tự ẩn sau 4 giây
  if (window.bootstrap) {
    $$('.toast.show[data-flash]').forEach((el) => setTimeout(() => bootstrap.Toast.getOrCreateInstance(el).hide(), 4000));
  }

  /* ---------- Tiện ích: Toast thông báo ----------
     Dùng chung: window.WI.toast('Nội dung', 'success' | 'danger' | 'warning') */
  function showToast(message, type = 'success') {
    let wrap = $('#toastWrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.id = 'toastWrap';
      wrap.className = 'toast-container position-fixed top-0 end-0 p-3';
      wrap.style.zIndex = 1090;
      document.body.appendChild(wrap);
    }
    const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill' };
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${type} border-0`;
    el.setAttribute('role', 'alert');
    el.innerHTML = `<div class="d-flex"><div class="toast-body"><i class="bi bi-${icons[type] || 'info-circle-fill'} me-2"></i>${message}</div>` +
      '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button></div>';
    wrap.appendChild(el);
    if (window.bootstrap) {
      const t = new bootstrap.Toast(el, { delay: 3000 });
      el.addEventListener('hidden.bs.toast', () => el.remove());
      t.show();
    }
  }

  window.WI = Object.assign(window.WI || {}, {
    toast: showToast,
    validators: (window.WI && window.WI.validators) || [],
    csrfToken: csrfToken,
    csrfHeaders: csrfHeaders,
  });
})();
