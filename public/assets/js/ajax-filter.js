/* =========================================================
   WarningInfo – Lọc danh sách sự cố bằng AJAX (TV5 – Milestone 3, Issue #23)
   Trang: /report (app/views/reports/index.php)
   - Gõ từ khoá (debounce 300ms) hoặc đổi danh mục / mức độ / trạng thái → GET report/api_list → vẽ lại thẻ sự cố
   - Hiện khung xương (skeleton) trong lúc chờ; huỷ request cũ nếu người dùng đổi bộ lọc liên tục
   - Nút "Xem thêm" nạp trang kế tiếp; URL được cập nhật để F5 / chia sẻ link vẫn giữ bộ lọc
   Dữ liệu luôn gán bằng textContent → không chèn được HTML/JS độc hại (chống XSS).
   Khi tắt JS, form và nút "Xem thêm" vẫn hoạt động như link thường (ReportController::index).
   ========================================================= */
(function () {
  'use strict';

  const form = document.getElementById('reportFilter');
  const results = document.getElementById('reportResults');
  if (!form || !results || !window.fetch) return;

  const countEl = document.getElementById('reportCount');
  const moreBtn = document.getElementById('reportLoadMore');
  const API = form.dataset.api;
  const DETAIL_URL = form.dataset.detailUrl;
  const PAGE_SIZE = parseInt(form.dataset.pageSize, 10) || 9;
  const BADGES = JSON.parse(form.dataset.badges || '{}');
  const FIELDS = ['q', 'category_id', 'priority', 'status'];
  const DEBOUNCE_MS = 300;

  let page = parseInt(moreBtn ? moreBtn.dataset.page : '1', 10) || 1;
  let controller = null;
  let debounceTimer = null;

  /* ---------- Tiện ích dựng DOM an toàn ---------- */
  const el = (tag, cls, text) => {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  };
  const icon = (cls) => el('i', 'bi ' + cls);

  const badge = (map, code, fallback) => {
    const [label, cls] = (BADGES[map] && BADGES[map][code]) || [fallback || code, 'bg-secondary'];
    return el('span', 'badge ' + cls, label);
  };

  // "2026-10-01 08:30:00" → "01/10/2026" (không dùng new Date để tránh lệch múi giờ)
  const formatDate = (s) => {
    const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(s || '');
    return m ? `${m[3]}/${m[2]}/${m[1]}` : '';
  };

  /* ---------- Thẻ sự cố: cùng cấu trúc với app/views/reports/_card.php ---------- */
  function renderCard(r) {
    const col = el('div', 'col-md-6 col-lg-4 fade-in');
    const card = el('div', 'card h-100 border-0 shadow-sm hover-shadow transition');
    const body = el('div', 'card-body d-flex flex-column');

    const top = el('div', 'd-flex justify-content-between align-items-center mb-2');
    top.append(badge('priority', r.priority, 'Trung bình'), badge('status', r.status, r.status_label));

    const title = el('h5', 'card-title fw-bold mb-2');
    const link = el('a', 'text-decoration-none text-dark', r.title);
    link.href = r.detail_url || DETAIL_URL + r.id;
    title.appendChild(link);

    const desc = String(r.description || '');
    const chars = Array.from(desc); // cắt theo ký tự Unicode như mb_substr
    const text = el('p', 'card-text text-muted small flex-grow-1',
      chars.slice(0, 110).join('') + (chars.length > 110 ? '...' : ''));

    const addr = el('div', 'small text-muted mb-2');
    addr.append(icon('bi-geo-alt me-1 text-danger'), document.createTextNode(' ' + (r.address || 'TP. Hồ Chí Minh')));

    const foot = el('div', 'd-flex justify-content-between align-items-center pt-2 border-top small text-muted');
    const date = el('span');
    date.append(icon('bi-clock me-1'), document.createTextNode(formatDate(r.incident_time || r.created_at)));
    const stats = el('div', 'd-flex gap-3');
    const up = el('span');
    up.title = 'Lượt ủng hộ';
    up.append(icon('bi-hand-thumbs-up me-1'), document.createTextNode(String(r.upvote_count || 0)));
    const cm = el('span');
    cm.title = 'Bình luận';
    cm.append(icon('bi-chat-dots me-1'), document.createTextNode(String(r.comment_count || 0)));
    stats.append(up, cm);
    foot.append(date, stats);

    body.append(top, title, text, addr, foot);
    card.appendChild(body);
    col.appendChild(card);
    return col;
  }

  function renderEmpty() {
    const box = el('div', 'col-12 text-center py-5');
    box.append(
      icon('bi-inbox text-muted display-1'),
      el('h4', 'mt-3 text-muted', 'Không tìm thấy báo cáo nào'),
      el('p', 'text-muted', 'Thử thay đổi từ khoá hoặc bộ lọc.')
    );
    results.replaceChildren(box);
  }

  function renderError(append) {
    const box = el('div', 'col-12 text-center py-4');
    box.setAttribute('data-ajax-error', '');
    const retry = el('button', 'btn btn-sm btn-outline-primary ms-2', 'Thử lại');
    retry.type = 'button';
    retry.addEventListener('click', () => (append ? loadMore() : search()));
    const msg = el('p', 'text-danger mb-0');
    msg.append(icon('bi-wifi-off me-1'), document.createTextNode('Không tải được dữ liệu, vui lòng kiểm tra kết nối.'), retry);
    box.appendChild(msg);
    if (append) results.appendChild(box); else results.replaceChildren(box);
  }

  function showSkeleton(count, append) {
    const frag = document.createDocumentFragment();
    for (let i = 0; i < count; i++) {
      const col = el('div', 'col-md-6 col-lg-4');
      col.setAttribute('data-skeleton', '');
      const card = el('div', 'card h-100 border-0 shadow-sm skeleton-card');
      card.setAttribute('aria-hidden', 'true');
      const body = el('div', 'card-body');
      body.append(el('div', 'skeleton skeleton-badges'), el('div', 'skeleton skeleton-title'),
        el('div', 'skeleton skeleton-line'), el('div', 'skeleton skeleton-line short'));
      card.appendChild(body);
      col.appendChild(card);
      frag.appendChild(col);
    }
    if (append) results.appendChild(frag); else results.replaceChildren(frag);
    results.setAttribute('aria-busy', 'true');
  }

  const clearSkeleton = () => results.querySelectorAll('[data-skeleton], [data-ajax-error]').forEach((n) => n.remove());

  function setCount(total) {
    if (!countEl) return;
    countEl.replaceChildren();
    if (total > 0) {
      countEl.append('Tìm thấy ', el('strong', null, String(total)), ' sự cố');
    }
  }

  function setMore(pagination) {
    if (!moreBtn) return;
    const hasMore = pagination.page < pagination.total_pages;
    moreBtn.classList.toggle('d-none', !hasMore);
    moreBtn.dataset.page = String(pagination.page);
    moreBtn.href = location.pathname + '?' + buildQuery(pagination.page + 1).toString();
  }

  /* ---------- Bộ lọc → query string ---------- */
  function filterParams() {
    const fd = new FormData(form);
    const params = new URLSearchParams();
    FIELDS.forEach((k) => {
      const v = String(fd.get(k) || '').trim();
      if (v !== '') params.set(k, v);
    });
    return params;
  }

  function buildQuery(p) {
    const params = filterParams();
    if (p > 1) params.set('page', String(p));
    return params;
  }

  async function request(p) {
    if (controller) controller.abort();
    controller = new AbortController();
    const params = filterParams();
    params.set('page', String(p));
    params.set('limit', String(PAGE_SIZE));
    const res = await fetch(API + '?' + params.toString(), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      signal: controller.signal,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.status !== 'success') {
      throw new Error(data.message || 'HTTP ' + res.status);
    }
    return data;
  }

  /* Lọc mới: thay toàn bộ danh sách bằng trang 1 */
  async function search() {
    showSkeleton(Math.min(PAGE_SIZE, 6), false);
    if (moreBtn) moreBtn.classList.add('d-none');
    const qs = buildQuery(1).toString();
    history.replaceState(null, '', location.pathname + (qs ? '?' + qs : ''));
    try {
      const data = await request(1);
      page = 1;
      if (!data.data.length) {
        renderEmpty();
      } else {
        results.replaceChildren(...data.data.map(renderCard));
      }
      setCount(data.pagination.total);
      setMore(data.pagination);
    } catch (err) {
      if (err.name === 'AbortError') return; // đã có request mới hơn thay thế
      renderError(false);
    } finally {
      results.setAttribute('aria-busy', 'false');
    }
  }

  /* "Xem thêm": nối trang kế tiếp vào cuối danh sách */
  async function loadMore() {
    clearSkeleton();
    showSkeleton(3, true);
    moreBtn.classList.add('d-none');
    try {
      const data = await request(page + 1);
      clearSkeleton();
      page = data.pagination.page;
      results.append(...data.data.map(renderCard));
      setCount(data.pagination.total);
      setMore(data.pagination);
      history.replaceState(null, '', location.pathname + '?' + buildQuery(page).toString());
    } catch (err) {
      if (err.name === 'AbortError') return;
      clearSkeleton();
      renderError(true);
    } finally {
      results.setAttribute('aria-busy', 'false');
    }
  }

  /* ---------- Sự kiện ---------- */
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    clearTimeout(debounceTimer);
    search();
  });

  form.elements.q.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(search, DEBOUNCE_MS);
  });

  form.querySelectorAll('select').forEach((s) => s.addEventListener('change', search));

  const resetBtn = form.querySelector('[data-filter-reset]');
  if (resetBtn) {
    resetBtn.addEventListener('click', (e) => {
      e.preventDefault();
      form.elements.q.value = '';
      form.querySelectorAll('select').forEach((s) => { s.value = ''; });
      search();
    });
  }

  if (moreBtn) {
    moreBtn.addEventListener('click', (e) => {
      e.preventDefault();
      loadMore();
    });
  }
})();
