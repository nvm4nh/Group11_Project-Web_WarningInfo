/* =========================================================
   WarningInfo – Tương tác AJAX (TV5 – Milestone 3, Issue #23)
   1. Trang chi tiết sự cố: nút "Tôi cũng thấy sự cố này" (upvote) – POST report/api_upvote/{id}
   2. Trang chi tiết sự cố: gửi bình luận (POST report/api_add_comment) và "Tải thêm bình luận" (GET report/api_comments/{id})
   3. Trang thông báo: đánh dấu đã đọc từng thông báo / tất cả (POST notification/read/{id}, notification/readAll)
   - Mọi POST gửi kèm header X-CSRF-Token qua WI.csrfHeaders() (main.js) – public/index.php chặn POST thiếu token.
   - Nội dung người dùng nhập luôn gán bằng textContent → không chèn được HTML/JS (chống XSS).
   - Khi tắt JS, các form vẫn gửi bình thường (ReportController::comment, NotificationController::read).
   ========================================================= */
(function () {
  'use strict';

  if (!window.fetch) return;

  const JSON_HEADERS = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
  const postHeaders = (extra) => {
    const base = Object.assign({}, JSON_HEADERS, extra || {});
    return window.WI && WI.csrfHeaders ? WI.csrfHeaders(base) : base;
  };
  const toast = (msg, type) => {
    if (window.WI && WI.toast) WI.toast(msg, type);
  };
  const el = (tag, cls, text) => {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  };

  /** Gọi API, trả về { ok, status, data } – không ném lỗi khi server trả 4xx/5xx */
  async function callApi(url, options) {
    try {
      const res = await fetch(url, Object.assign({ credentials: 'same-origin' }, options));
      const data = await res.json().catch(() => ({}));
      return { ok: res.ok && data.status === 'success', status: res.status, data };
    } catch (e) {
      return { ok: false, status: 0, data: { message: 'Mất kết nối mạng, vui lòng thử lại.' } };
    }
  }

  const failMessage = (r, fallback) =>
    r.status === 403 ? 'Phiên làm việc đã hết hạn, vui lòng tải lại trang.' : (r.data.message || fallback);

  const setLoading = (btn, on) => {
    btn.disabled = on;
    const sp = btn.querySelector('.spinner-border');
    if (sp) sp.classList.toggle('d-none', !on);
  };

  /* -------------------------------------------------------------
     1. Upvote – đổi màu nút + số "nảy" lên (micro-interaction)
     ------------------------------------------------------------- */
  const upvoteBtn = document.getElementById('btnUpvote');
  if (upvoteBtn) {
    const countEl = upvoteBtn.querySelector('.upvote-count');
    const textEl = upvoteBtn.querySelector('.upvote-text');
    const iconEl = upvoteBtn.querySelector('.bi');

    const render = (voted, count) => {
      upvoteBtn.classList.toggle('btn-primary', voted);
      upvoteBtn.classList.toggle('is-voted', voted);
      upvoteBtn.classList.toggle('btn-outline-primary', !voted);
      upvoteBtn.setAttribute('aria-pressed', voted ? 'true' : 'false');
      iconEl.className = 'bi ' + (voted ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up') + ' me-1';
      textEl.textContent = voted ? 'Bạn đã xác nhận' : 'Tôi cũng thấy sự cố này';
      countEl.textContent = String(count);
      countEl.classList.remove('number-bounce');
      void countEl.offsetWidth; // khởi động lại animation khi bấm liên tục
      countEl.classList.add('number-bounce');
    };

    upvoteBtn.addEventListener('click', async () => {
      if (upvoteBtn.dataset.loginUrl) {
        toast('Vui lòng đăng nhập để xác nhận sự cố.', 'warning');
        setTimeout(() => { window.location.href = upvoteBtn.dataset.loginUrl; }, 1200);
        return;
      }
      upvoteBtn.disabled = true;
      const r = await callApi(upvoteBtn.dataset.endpoint, { method: 'POST', headers: postHeaders() });
      upvoteBtn.disabled = false;
      if (r.ok) {
        render(!!r.data.user_voted, r.data.upvotes);
      } else {
        toast(failMessage(r, 'Không thực hiện được, vui lòng thử lại.'), 'danger');
      }
    });
  }

  /* -------------------------------------------------------------
     2. Bình luận – gửi không tải lại trang + "Tải thêm bình luận"
     ------------------------------------------------------------- */
  const box = document.getElementById('commentBox');
  if (box) {
    const list = document.getElementById('commentList');
    const emptyEl = document.getElementById('commentEmpty');
    const totalEl = document.getElementById('commentTotal');
    const form = document.getElementById('commentForm');
    const moreBtn = document.getElementById('btnLoadMoreComments');
    const LIMIT = parseInt(box.dataset.limit, 10) || 5;
    let page = parseInt(box.dataset.page, 10) || 1;

    // Cùng cấu trúc với vòng lặp bình luận trong app/views/reports/detail.php
    function renderComment(c, isNew) {
      const item = el('div', 'comment-item p-3 rounded-3 bg-light' + (isNew ? ' fade-in is-new' : ''));
      item.dataset.id = c.id;
      const head = el('div', 'd-flex flex-wrap justify-content-between align-items-center gap-1 mb-1');
      const name = el('span', 'fw-bold small', c.user_name || 'Ẩn danh');
      if (c.is_staff) name.append(' ', el('span', 'badge bg-primary ms-1', 'Cán bộ xử lý'));
      head.append(name, el('span', 'text-muted x-small', c.created_at_text || ''));
      const body = el('p', 'mb-0 small text-secondary', c.content);
      body.style.whiteSpace = 'pre-line';
      item.append(head, body);
      return item;
    }

    const setTotal = (total) => {
      totalEl.textContent = String(total);
      emptyEl.classList.toggle('d-none', total > 0);
    };

    if (form) {
      const textarea = form.querySelector('textarea[name="content"]');
      const submitBtn = form.querySelector('button[type="submit"]');

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const content = textarea.value.trim();
        if (!content) {
          textarea.focus();
          return;
        }
        setLoading(submitBtn, true);
        const r = await callApi(box.dataset.addEndpoint, {
          method: 'POST',
          headers: postHeaders({ 'Content-Type': 'application/json' }),
          body: JSON.stringify({ report_id: parseInt(box.dataset.reportId, 10), content }),
        });
        setLoading(submitBtn, false);
        if (r.ok) {
          list.prepend(renderComment(r.data.comment, true));
          setTotal(r.data.total);
          textarea.value = '';
          toast(r.data.message || 'Đã gửi bình luận.', 'success');
        } else {
          toast(failMessage(r, 'Không gửi được bình luận, vui lòng thử lại.'), 'danger');
        }
      });
    }

    if (moreBtn) {
      const remainingEl = moreBtn.querySelector('[data-remaining]');
      moreBtn.addEventListener('click', async () => {
        setLoading(moreBtn, true);
        const url = new URL(box.dataset.endpoint, window.location.href);
        url.searchParams.set('page', String(page + 1));
        url.searchParams.set('limit', String(LIMIT));
        const r = await callApi(url.toString(), { headers: JSON_HEADERS });
        setLoading(moreBtn, false);
        if (!r.ok) {
          toast(failMessage(r, 'Không tải được bình luận, vui lòng thử lại.'), 'danger');
          return;
        }
        page = r.data.pagination.page;
        // Bỏ bình luận đã có trên trang: vừa gửi bình luận mới sẽ đẩy lệch vị trí phân trang (OFFSET)
        const shown = new Set(Array.from(list.children, (n) => n.dataset.id).filter(Boolean));
        r.data.data.filter((c) => !shown.has(String(c.id))).forEach((c) => list.appendChild(renderComment(c, true)));
        const loaded = list.children.length;
        const total = r.data.pagination.total;
        setTotal(total);
        if (!r.data.pagination.has_more || loaded >= total) {
          moreBtn.parentElement.remove();
        } else if (remainingEl) {
          remainingEl.textContent = String(total - loaded);
        }
      });
    }
  }

  /* -------------------------------------------------------------
     3. Trang "Thông báo của tôi" – đánh dấu đã đọc không tải lại trang
     ------------------------------------------------------------- */
  const notifPage = document.querySelector('[data-notif-page]'); // danh sách ở app/views/notifications/index.php
  if (notifPage) {
    const summary = document.querySelector('[data-notif-summary]');
    const tabCount = document.querySelector('[data-notif-tab-count]');
    const readAllForm = document.querySelector('[data-notif-read-all]');
    const onlyUnread = notifPage.dataset.filter === 'unread';

    const updateCounts = (unread) => {
      if (summary) {
        summary.replaceChildren();
        if (unread > 0) summary.append('Bạn có ', el('strong', null, String(unread)), ' thông báo chưa đọc.');
        else summary.textContent = 'Bạn đã đọc hết thông báo.';
      }
      if (tabCount) tabCount.textContent = String(unread);
      if (readAllForm) readAllForm.classList.toggle('d-none', unread <= 0);
      // Báo cho chuông thông báo trên thanh menu (notifications.js) cập nhật lại số chưa đọc
      document.dispatchEvent(new CustomEvent('wi:notifications-changed', { detail: { unread } }));
    };

    const markItemRead = (item) => {
      item.classList.remove('is-unread');
      const f = item.querySelector('.notif-action');
      if (f) f.remove();
      if (onlyUnread) {
        item.classList.add('fade-out');
        setTimeout(() => item.remove(), 300);
      }
    };

    document.addEventListener('submit', async (e) => {
      const form = e.target;
      const isAll = form.matches('[data-notif-read-all]');
      if (!isAll && !form.matches('.notif-action')) return;
      e.preventDefault();
      const btn = form.querySelector('button');
      btn.disabled = true;
      const r = await callApi(form.action, { method: 'POST', headers: postHeaders() });
      btn.disabled = false;
      if (!r.ok) {
        toast(failMessage(r, 'Không thực hiện được, vui lòng thử lại.'), 'danger');
        return;
      }
      if (isAll) {
        notifPage.querySelectorAll('.notif-item.is-unread').forEach(markItemRead);
        toast(r.data.message, 'success');
      } else {
        markItemRead(form.closest('.notif-item'));
      }
      updateCounts(r.data.unread || 0);
    });
  }
})();
