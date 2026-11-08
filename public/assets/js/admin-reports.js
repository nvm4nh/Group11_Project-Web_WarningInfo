/* =========================================================
   WarningInfo – Trang Admin "Quản lý sự cố" (Milestone 2)
   1. Modal "Xem nhanh": đổ dữ liệu từ thuộc tính data-report (JSON) của nút bấm
   2. Modal "Phân công": gán action của form theo từng sự cố
   Mọi nội dung đều gán bằng textContent / thuộc tính => không chèn HTML từ dữ liệu người dùng.
   ========================================================= */
(function () {
  'use strict';

  /* ---------- 1. Xem nhanh chi tiết sự cố ---------- */
  const qv = document.getElementById('quickViewModal');
  if (qv) {
    qv.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      if (!btn || !btn.dataset.report) return;

      let r;
      try { r = JSON.parse(btn.dataset.report); } catch (e) { return; }

      const set = (key, value) => {
        const el = qv.querySelector('[data-qv="' + key + '"]');
        if (el) el.textContent = value == null ? '' : value;
      };
      ['code', 'title', 'category', 'status', 'address', 'coords', 'contact', 'time', 'description'].forEach((k) => set(k, r[k]));
      set('priority', r.priority);
      set('officer', r.officer || 'Chưa phân công');
      qv.querySelector('[data-qv="priority"]').className = 'badge ' + (r.priorityCls || 'bg-secondary');
      qv.querySelectorAll('.qv-coords').forEach((el) => el.classList.toggle('d-none', !r.coords));
      document.getElementById('qvDetailLink').href = r.detailUrl || '#';

      // Lưới ảnh minh chứng
      const grid = document.getElementById('qvImages');
      grid.replaceChildren();
      const images = Array.isArray(r.images) ? r.images : [];
      set('imageCount', images.length ? '(' + images.length + ')' : '');

      if (!images.length) {
        const empty = document.createElement('div');
        empty.className = 'col-12 text-muted small';
        empty.textContent = 'Người gửi chưa đính kèm ảnh minh chứng.';
        grid.appendChild(empty);
        return;
      }
      images.forEach((img, i) => {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-4';
        const a = document.createElement('a');
        a.href = img.src;
        a.target = '_blank';
        a.rel = 'noopener';
        a.title = img.name || ('Ảnh ' + (i + 1));
        const el = document.createElement('img');
        el.src = img.src;
        el.alt = 'Ảnh minh chứng ' + (i + 1);
        el.loading = 'lazy';
        el.className = 'img-fluid rounded border w-100';
        el.style.aspectRatio = '4 / 3';
        el.style.objectFit = 'cover';
        a.appendChild(el);
        col.appendChild(a);
        grid.appendChild(col);
      });
    });
  }

  /* ---------- 2. Phân công cán bộ ---------- */
  const am = document.getElementById('assignModal');
  if (am) {
    am.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      if (!btn) return;
      document.getElementById('assignForm').action = btn.dataset.action || '';
      document.getElementById('assignCode').textContent = btn.dataset.code || '';
      document.getElementById('assignReportTitle').textContent = btn.dataset.title || '';
      const select = document.getElementById('assignOfficer');
      if (select) select.value = btn.dataset.officer || '';
      const note = document.getElementById('assignNote');
      if (note) note.value = '';
    });
  }
})();
