/* =========================================================
   WarningInfo – Thư viện ảnh minh chứng (TV4 – Milestone 2)
   Bấm vào ảnh trong .report-gallery để phóng to:
   - Nút trước/sau, phím ← → để chuyển ảnh, Esc để đóng
   - Vuốt trái/phải trên điện thoại, bấm vào ảnh để phóng to x2
   - Không cần thư viện ngoài, không phụ thuộc Bootstrap JS
   ========================================================= */
(function () {
  'use strict';

  const galleries = document.querySelectorAll('[data-gallery]');
  if (!galleries.length) return;

  /* ---------- Tạo khung lightbox một lần ---------- */
  const box = document.createElement('div');
  box.className = 'wi-lightbox';
  box.setAttribute('role', 'dialog');
  box.setAttribute('aria-modal', 'true');
  box.setAttribute('aria-label', 'Xem ảnh minh chứng');
  box.setAttribute('aria-hidden', 'true');
  box.innerHTML =
    '<div class="lb-counter" aria-live="polite"></div>' +
    '<button type="button" class="lb-btn lb-close" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>' +
    '<button type="button" class="lb-btn lb-prev" aria-label="Ảnh trước"><i class="bi bi-chevron-left"></i></button>' +
    '<figure class="lb-stage"><div class="lb-holder"></div><figcaption class="lb-caption"></figcaption></figure>' +
    '<button type="button" class="lb-btn lb-next" aria-label="Ảnh sau"><i class="bi bi-chevron-right"></i></button>';
  document.body.appendChild(box);

  const holder = box.querySelector('.lb-holder');
  const caption = box.querySelector('.lb-caption');
  const counter = box.querySelector('.lb-counter');
  const btnClose = box.querySelector('.lb-close');
  const btnPrev = box.querySelector('.lb-prev');
  const btnNext = box.querySelector('.lb-next');

  let items = [];      // [{src, type, name}]
  let index = 0;
  let lastFocus = null;

  const isOpen = () => box.classList.contains('open');

  const show = (i) => {
    index = (i + items.length) % items.length;
    const it = items[index];
    const old = holder.querySelector('video');
    if (old) old.pause();
    holder.innerHTML = '';

    let media;
    if (it.type === 'video') {
      media = document.createElement('video');
      media.controls = true;
      media.playsInline = true;
      media.preload = 'metadata';
    } else {
      media = document.createElement('img');
      media.alt = it.name || `Ảnh minh chứng ${index + 1}`;
      media.draggable = false;
      media.addEventListener('click', (e) => {
        // Bấm vào ảnh để phóng to tại đúng điểm bấm, bấm lần nữa để thu nhỏ
        const r = media.getBoundingClientRect();
        media.style.transformOrigin = `${((e.clientX - r.left) / r.width) * 100}% ${((e.clientY - r.top) / r.height) * 100}%`;
        media.classList.toggle('zoomed');
      });
    }
    media.className = 'lb-media';
    media.src = it.src;
    holder.appendChild(media);

    caption.textContent = it.name || '';
    counter.textContent = `${index + 1} / ${items.length}`;

    // Tải trước ảnh kế tiếp cho mượt
    const next = items[(index + 1) % items.length];
    if (next && next.type !== 'video') { const pre = new Image(); pre.src = next.src; }
  };

  const open = (list, i, trigger) => {
    items = list;
    lastFocus = trigger || document.activeElement;
    box.classList.toggle('single', items.length < 2);
    show(i);
    box.classList.add('open');
    box.setAttribute('aria-hidden', 'false');
    document.body.classList.add('lb-lock');
    btnClose.focus();
  };

  const close = () => {
    if (!isOpen()) return;
    const v = holder.querySelector('video');
    if (v) v.pause();
    box.classList.remove('open');
    box.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('lb-lock');
    holder.innerHTML = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  };

  const zoomed = () => !!holder.querySelector('.zoomed');

  /* ---------- Gắn sự kiện cho từng thư viện ảnh ---------- */
  galleries.forEach((gallery) => {
    const links = Array.from(gallery.querySelectorAll('.gallery-item'));
    const list = links.map((a) => ({
      src: a.getAttribute('href'),
      type: a.dataset.type === 'video' ? 'video' : 'image',
      name: a.dataset.name || '',
    }));
    links.forEach((a, i) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        open(list, i, a);
      });
    });
  });

  btnClose.addEventListener('click', close);
  btnPrev.addEventListener('click', () => show(index - 1));
  btnNext.addEventListener('click', () => show(index + 1));

  // Bấm ra vùng tối bên ngoài ảnh để đóng
  box.addEventListener('click', (e) => {
    if (e.target === box || e.target.classList.contains('lb-stage') || e.target === holder) close();
  });

  document.addEventListener('keydown', (e) => {
    if (!isOpen()) return;
    if (e.key === 'Escape') { e.preventDefault(); close(); }
    else if (e.key === 'ArrowLeft' && items.length > 1) { e.preventDefault(); show(index - 1); }
    else if (e.key === 'ArrowRight' && items.length > 1) { e.preventDefault(); show(index + 1); }
    else if (e.key === 'Tab') {
      // Giữ phím Tab chỉ di chuyển trong lightbox
      const focusables = Array.from(box.querySelectorAll('button, video')).filter((el) => el.offsetParent !== null);
      if (!focusables.length) return;
      const first = focusables[0];
      const last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  // Vuốt trái/phải trên điện thoại
  let startX = null;
  let startY = null;
  box.addEventListener('touchstart', (e) => {
    if (e.touches.length !== 1 || zoomed()) { startX = null; return; }
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
  }, { passive: true });
  box.addEventListener('touchend', (e) => {
    if (startX === null || items.length < 2) return;
    const dx = e.changedTouches[0].clientX - startX;
    const dy = e.changedTouches[0].clientY - startY;
    if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) show(index + (dx < 0 ? 1 : -1));
    startX = null;
  });
})();
