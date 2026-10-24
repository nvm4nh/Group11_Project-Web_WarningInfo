/* =========================================================
   WarningInfo – Chuông thông báo trên thanh menu (TV4 – Milestone 3)
   - Gọi AJAX GET notification/latest → { unread, items[] } để hiện số chưa đọc + 6 thông báo mới nhất
   - Tự cập nhật mỗi 60 giây (chỉ khi tab đang mở) và mỗi lần bấm mở chuông
   - "Đánh dấu đã đọc hết": AJAX POST notification/readAll kèm header X-CSRF-Token
   Nội dung thông báo luôn gán bằng textContent → không chèn được HTML/JS độc hại.
   ========================================================= */
(function () {
  'use strict';

  const bell = document.getElementById('notifBell');
  if (!bell || !window.fetch) return;

  const badge = document.getElementById('notifBadge');
  const list = document.getElementById('notifList');
  const btnReadAll = document.getElementById('notifReadAll');
  const countEls = document.querySelectorAll('[data-notif-count]');
  const ENDPOINT = bell.dataset.endpoint;
  const READ_ALL = bell.dataset.readAll;
  const ICONS = {
    report_status: ['bi-arrow-repeat', 'notif-ic-status'],
    assignment: ['bi-person-check', 'notif-ic-assign'],
    officer_update: ['bi-clipboard-check', 'notif-ic-assign'],
    general: ['bi-bell', 'notif-ic-general'],
  };
  const jsonHeaders = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
  let loading = false;

  const el = (tag, cls, text) => {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  };

  function setCount(unread) {
    const label = unread > 99 ? '99+' : String(unread);
    badge.textContent = label;
    badge.classList.toggle('d-none', unread <= 0);
    bell.setAttribute('aria-label', unread > 0 ? `Thông báo (${unread} chưa đọc)` : 'Thông báo');
    countEls.forEach((c) => { c.textContent = label; c.classList.toggle('d-none', unread <= 0); });
    if (btnReadAll) btnReadAll.classList.toggle('d-none', unread <= 0);
  }

  function renderList(items) {
    list.replaceChildren();
    if (!items.length) {
      const empty = el('div', 'notif-menu-empty');
      empty.append(el('i', 'bi bi-bell-slash me-2'), document.createTextNode('Bạn chưa có thông báo nào.'));
      list.appendChild(empty);
      return;
    }
    items.forEach((it) => {
      const [icon, cls] = ICONS[it.type] || ICONS.general;
      const row = el('a', 'notif-row' + (it.is_read ? '' : ' is-unread'));
      row.href = it.url;
      const ic = el('span', 'notif-ic ' + cls);
      ic.appendChild(el('i', 'bi ' + icon));
      const body = el('span', 'notif-row-body');
      body.append(el('span', 'notif-row-title', it.title), el('span', 'notif-row-msg', it.message), el('span', 'notif-row-time', it.time));
      row.append(ic, body);
      list.appendChild(row);
    });
  }

  async function load() {
    if (loading) return;
    loading = true;
    try {
      const res = await fetch(ENDPOINT, { headers: jsonHeaders, credentials: 'same-origin' });
      if (!res.ok) return;
      const data = await res.json();
      if (data.status !== 'success') return;
      setCount(data.unread || 0);
      renderList(data.items || []);
    } catch (e) {
      // Mất mạng tạm thời: giữ nguyên số cũ, lần sau thử lại
    } finally {
      loading = false;
    }
  }

  async function readAll() {
    btnReadAll.disabled = true;
    try {
      const headers = window.WI && WI.csrfHeaders ? WI.csrfHeaders(jsonHeaders) : jsonHeaders;
      const res = await fetch(READ_ALL, { method: 'POST', headers, credentials: 'same-origin' });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.status === 'success') {
        await load();
        if (window.WI && WI.toast) WI.toast(data.message, 'success');
      } else if (window.WI && WI.toast) {
        WI.toast(data.message || 'Không thực hiện được, vui lòng thử lại.', 'danger');
      }
    } finally {
      btnReadAll.disabled = false;
    }
  }

  bell.addEventListener('show.bs.dropdown', load);
  if (btnReadAll) btnReadAll.addEventListener('click', readAll);
  setInterval(() => { if (document.visibilityState === 'visible') load(); }, 60000);
  load();
})();
