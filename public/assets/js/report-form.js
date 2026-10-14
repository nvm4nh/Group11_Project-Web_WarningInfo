/* =========================================================
   WarningInfo – Form gửi báo cáo (TV4 – Milestone 2)
   1. Chọn ảnh minh chứng + xem trước ảnh trước khi gửi
   Ràng buộc khớp phần upload của TV3: tối đa 3 ảnh, mỗi ảnh ≤ 5MB, JPG/PNG/WEBP
   ========================================================= */
(function () {
  'use strict';

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const toast = (msg, type) => (window.WI && WI.toast ? WI.toast(msg, type) : null);

  /* ---------- 1. Xem trước ảnh minh chứng ---------- */
  const input = $('#reportImages');
  const zone = $('#uploadZone');
  const grid = $('#previewGrid');
  const errorBox = $('#uploadError');
  const counter = $('#imageCount');

  if (input && zone && grid) {
    const MAX_FILES = parseInt(input.dataset.maxFiles, 10) || 3;
    const MAX_MB = parseInt(input.dataset.maxSize, 10) || 5;
    const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];
    // DataTransfer giúp cộng dồn ảnh qua nhiều lần chọn và xoá từng ảnh
    const canKeep = typeof DataTransfer !== 'undefined';
    let store = canKeep ? new DataTransfer() : null;

    const formatSize = (bytes) => (bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB');

    const showError = (msg) => {
      zone.classList.toggle('is-invalid', !!msg);
      if (errorBox) {
        errorBox.textContent = msg || '';
        errorBox.classList.toggle('d-none', !msg);
      }
    };

    const currentFiles = () => Array.from(canKeep ? store.files : input.files);

    const render = () => {
      grid.innerHTML = '';
      currentFiles().forEach((file, idx) => {
        const url = URL.createObjectURL(file);
        const item = document.createElement('div');
        item.className = 'preview-item';
        item.innerHTML =
          (idx === 0 ? '<span class="badge-first">Ảnh bìa</span>' : '') +
          '<img alt="">' +
          '<div class="meta"><b></b><span></span></div>' +
          (canKeep ? `<button type="button" class="btn-remove" data-remove="${idx}" aria-label="Xoá ảnh"><i class="bi bi-x-lg"></i></button>` : '');
        const img = item.querySelector('img');
        img.src = url;
        img.alt = 'Xem trước ' + file.name;
        img.onload = () => URL.revokeObjectURL(url);
        item.querySelector('.meta b').textContent = file.name;
        item.querySelector('.meta span').textContent = formatSize(file.size);
        grid.appendChild(item);
      });
      const n = currentFiles().length;
      if (counter) counter.textContent = `${n}/${MAX_FILES} ảnh`;
      zone.classList.toggle('is-full', canKeep && n >= MAX_FILES);
    };

    input.addEventListener('change', () => {
      const picked = Array.from(input.files);
      let msg = '';
      if (!canKeep) {
        // Trình duyệt cũ: chỉ xem trước lần chọn hiện tại
        if (picked.length > MAX_FILES) msg = `Chỉ được chọn tối đa ${MAX_FILES} ảnh.`;
        picked.forEach((f) => {
          if (!ALLOWED.includes(f.type)) msg = `"${f.name}" không đúng định dạng (JPG, PNG, WEBP).`;
          else if (f.size > MAX_MB * 1048576) msg = `"${f.name}" lớn hơn ${MAX_MB}MB.`;
        });
        if (msg) input.value = '';
        showError(msg);
        render();
        return;
      }
      picked.forEach((f) => {
        if (store.files.length >= MAX_FILES) { msg = `Chỉ được gửi tối đa ${MAX_FILES} ảnh.`; return; }
        if (!ALLOWED.includes(f.type)) { msg = `"${f.name}" không đúng định dạng (chỉ nhận JPG, PNG, WEBP).`; return; }
        if (f.size > MAX_MB * 1048576) { msg = `"${f.name}" nặng ${formatSize(f.size)}, vượt quá ${MAX_MB}MB.`; return; }
        const dup = Array.from(store.files).some((x) => x.name === f.name && x.size === f.size);
        if (!dup) store.items.add(f);
      });
      input.files = store.files;
      showError(msg);
      if (msg) toast(msg, 'warning');
      render();
    });

    grid.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-remove]');
      if (!btn || !canKeep) return;
      const idx = parseInt(btn.dataset.remove, 10);
      const next = new DataTransfer();
      Array.from(store.files).forEach((f, i) => { if (i !== idx) next.items.add(f); });
      store = next;
      input.files = store.files;
      showError('');
      render();
    });

    ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, () => zone.classList.add('dragover')));
    ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, () => zone.classList.remove('dragover')));
    render();
  }
})();
