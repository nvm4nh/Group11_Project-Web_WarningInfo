/* =========================================================
   WarningInfo – Bản đồ số Leaflet.js (TV1 – Milestone 3, Issue #3)
   - Tự khởi tạo mọi phần tử [data-wi-map] trên trang.
   - Nạp dữ liệu GeoJSON từ /report/api_geojson bằng Fetch API (AJAX).
   - Marker màu theo mức độ: Đỏ (khẩn cấp) · Vàng (trung bình) · Xanh (đã giải quyết).
   - "Sự cố gần tôi": HTML5 Geolocation + lọc bán kính phía server (Haversine).

   Thuộc tính HTML hỗ trợ:
     data-wi-map                  bật bản đồ
     data-api="URL"               nguồn GeoJSON (chế độ nhiều điểm)
     data-point='{"lat":..}'      1 điểm cố định (trang chi tiết) – không gọi API
     data-filter-form="#id"       form lọc (đổi giá trị => nạp lại không tải trang)
     data-list="#id"              khung danh sách kết quả
     data-count-target="#id"      nơi in số sự cố (không dùng data-count: main.js đã dùng cho hiệu ứng đếm số)
     data-near-btn="#id"          nút "Sự cố gần tôi"
     data-radius-select="#id"     ô chọn bán kính (km)
     data-near-clear="#id"        nút bỏ lọc bán kính
     data-sync-url                ghi bộ lọc lên URL (history.replaceState)
     data-focus="ID"              mở sẵn popup của sự cố có ID này
   ========================================================= */
(function () {
  'use strict';

  if (!window.L) {
    console.error('[WIMap] Chưa nạp thư viện Leaflet.js');
    return;
  }

  const DEFAULT_CENTER = [10.7769, 106.7009];   // Trung tâm TP. Hồ Chí Minh
  const DEFAULT_ZOOM = 12;
  const ASSET_BASE = (document.querySelector('script[src*="/assets/js/wi-map.js"]')?.src || '').replace(/js\/wi-map\.js.*$/, '');

  const LEVELS = {
    urgent:   { label: 'Khẩn cấp',      color: '#dc2626' },
    medium:   { label: 'Trung bình',    color: '#f59e0b' },
    resolved: { label: 'Đã giải quyết', color: '#16a34a' },
  };

  // id danh mục => [icon Bootstrap, ảnh placeholder]
  const CATEGORY = {
    1: ['trash3', 'waste'],
    2: ['droplet-half', 'water'],
    3: ['cloud-haze2', 'air'],
    4: ['cloud-lightning-rain', 'flood'],
    5: ['tree', 'forest'],
    6: ['volume-up', 'noise'],
    7: ['exclamation-octagon', 'chem'],
  };

  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
  ));
  const toast = (msg, type) => (window.WI && WI.toast ? WI.toast(msg, type) : console.log(msg));
  const $ = (sel) => (sel ? document.querySelector(sel) : null);

  function markerIcon(level, categoryId) {
    const icon = (CATEGORY[categoryId] || ['geo-alt'])[0];
    return L.divIcon({
      className: 'wi-marker-wrap',
      html: `<span class="wi-marker lvl-${esc(level)}"><i class="bi bi-${icon}"></i></span>`,
      iconSize: [34, 34],
      iconAnchor: [17, 34],
      popupAnchor: [0, -32],
    });
  }

  function popupHtml(p) {
    const placeholder = ASSET_BASE + 'img/placeholder/' + ((CATEGORY[p.category_id] || [])[1] || 'other') + '.svg';
    const img = p.thumbnail_url || placeholder;
    const lvl = LEVELS[p.level] || LEVELS.medium;
    const dist = p.distance_km != null
      ? `<span><i class="bi bi-signpost-2"></i> Cách bạn ${p.distance_km.toLocaleString('vi-VN')} km</span>` : '';
    return `
      <div class="wi-popup">
        <img src="${esc(img)}" alt="" class="wi-popup-thumb" loading="lazy" onerror="this.src='${esc(placeholder)}'">
        <div class="wi-popup-body">
          <div class="wi-popup-tags">
            <span class="wi-dot" style="background:${lvl.color}"></span>
            <span>${esc(p.priority_label)}</span> · <span>${esc(p.status_label)}</span>
          </div>
          <h6 class="wi-popup-title">${esc(p.title)}</h6>
          <div class="wi-popup-meta">
            <span><i class="bi bi-tag"></i> ${esc(p.category_name || '')}</span>
            <span><i class="bi bi-geo-alt"></i> ${esc(p.address || 'Chưa có địa chỉ')}</span>
            ${dist}
          </div>
          <a href="${esc(p.detail_url)}" class="btn btn-primary btn-sm w-100 mt-2">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>`;
  }

  function addLegend(map) {
    const legend = L.control({ position: 'bottomleft' });
    legend.onAdd = () => {
      const div = L.DomUtil.create('div', 'wi-map-legend');
      div.innerHTML = Object.values(LEVELS)
        .map((l) => `<span><i class="wi-dot" style="background:${l.color}"></i>${l.label}</span>`).join('');
      return div;
    };
    legend.addTo(map);
  }

  class WIMap {
    constructor(el) {
      this.el = el;
      this.ds = el.dataset;
      this.markers = new Map();          // id => marker
      this.layer = L.featureGroup();
      this.near = null;                  // {lat, lng, radius}
      this.nearLayer = L.layerGroup();
      this.pickingCenter = false;
      this.abort = null;

      this.map = L.map(el, { scrollWheelZoom: !el.hasAttribute('data-no-scroll-zoom') })
        .setView(DEFAULT_CENTER, DEFAULT_ZOOM);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }).addTo(this.map);
      this.layer.addTo(this.map);
      this.nearLayer.addTo(this.map);

      if (this.ds.point) {
        this.renderSinglePoint(JSON.parse(this.ds.point));
        return;
      }

      addLegend(this.map);
      this.form = $(this.ds.filterForm);
      this.list = $(this.ds.list);
      this.countEl = $(this.ds.countTarget);
      this.radiusSelect = $(this.ds.radiusSelect);
      this.nearClear = $(this.ds.nearClear);
      this.focusId = parseInt(this.ds.focus, 10) || null;

      this.bindEvents();
      this.load(true);
    }

    /* ----- Trang chi tiết: 1 điểm cố định ----- */
    renderSinglePoint(p) {
      const latlng = [p.lat, p.lng];
      L.marker(latlng, { icon: markerIcon(p.level, p.category_id), title: p.title })
        .addTo(this.layer)
        .bindTooltip(p.title, { direction: 'top', offset: [0, -30] });
      this.map.setView(latlng, 16);
    }

    bindEvents() {
      if (this.form) {
        this.form.addEventListener('change', () => this.load(true));
        this.form.addEventListener('submit', (e) => { e.preventDefault(); this.load(true); });
        this.form.addEventListener('reset', () => setTimeout(() => this.clearNear(), 0));
      }
      $(this.ds.nearBtn)?.addEventListener('click', () => this.locate());
      this.nearClear?.addEventListener('click', () => this.clearNear());
      this.radiusSelect?.addEventListener('change', () => {
        if (this.near) { this.near.radius = this.radius(); this.drawNear(); this.load(true); }
      });

      // Dự phòng khi trình duyệt chặn GPS: người dùng nhấp lên bản đồ để chọn tâm
      this.map.on('click', (e) => {
        if (!this.pickingCenter) return;
        this.pickingCenter = false;
        this.el.classList.remove('is-picking');
        this.setNear(e.latlng.lat, e.latlng.lng, false);
      });

      // Click danh sách => bay tới marker
      this.list?.addEventListener('click', (e) => {
        const item = e.target.closest('[data-report-id]');
        if (!item) return;
        e.preventDefault();
        this.focus(parseInt(item.dataset.reportId, 10));
      });
    }

    radius() {
      return parseFloat(this.radiusSelect?.value) || 5;
    }

    /** Tham số truy vấn API từ form lọc + bán kính */
    params() {
      const qs = new URLSearchParams();
      if (this.form) {
        const fd = new FormData(this.form);
        const cats = fd.getAll('category_id[]').filter(Boolean);
        if (cats.length) qs.set('category_id', cats.join(','));
        ['priority', 'state'].forEach((k) => { const v = fd.get(k); if (v) qs.set(k, v); });
      }
      if (this.near) {
        qs.set('lat', this.near.lat.toFixed(6));
        qs.set('lng', this.near.lng.toFixed(6));
        qs.set('radius', this.near.radius);
      }
      return qs;
    }

    async load(fit) {
      const qs = this.params();
      if (this.abort) this.abort.abort();
      this.abort = new AbortController();
      this.el.classList.add('is-loading');
      try {
        const apiUrl = new URL(this.ds.api, window.location.href);   // giữ tham số cố định trong data-api
        qs.forEach((v, k) => apiUrl.searchParams.set(k, v));
        const res = await fetch(apiUrl, {
          headers: { Accept: 'application/json' },
          signal: this.abort.signal,
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Không tải được dữ liệu bản đồ');
        this.render(json.features || [], fit);
        if (this.ds.syncUrl !== undefined) this.syncUrl(qs);
      } catch (err) {
        if (err.name !== 'AbortError') toast(err.message || 'Lỗi kết nối máy chủ', 'danger');
      } finally {
        this.el.classList.remove('is-loading');
      }
    }

    render(features, fit) {
      this.layer.clearLayers();
      this.markers.clear();

      features.forEach((f) => {
        const [lng, lat] = f.geometry.coordinates;
        const p = f.properties;
        const m = L.marker([lat, lng], { icon: markerIcon(p.level, p.category_id), title: p.title, riseOnHover: true })
          .bindPopup(popupHtml(p), { maxWidth: 260, minWidth: 230, className: 'wi-leaflet-popup' });
        m.addTo(this.layer);
        this.markers.set(p.id, m);
      });

      if (this.countEl) this.countEl.textContent = features.length.toLocaleString('vi-VN');
      this.renderList(features);

      if (this.focusId && this.markers.has(this.focusId)) {
        this.focus(this.focusId);
        this.focusId = null;
      } else if (fit) {
        this.fit();
      }
    }

    renderList(features) {
      if (!this.list) return;
      if (!features.length) {
        this.list.innerHTML = `<div class="text-center text-muted small py-4">
          <i class="bi bi-geo d-block fs-3 mb-1"></i>Không có sự cố nào khớp bộ lọc${this.near ? ' trong bán kính ' + this.near.radius + ' km' : ''}.</div>`;
        return;
      }
      this.list.innerHTML = features.map(({ properties: p }) => {
        const lvl = LEVELS[p.level] || LEVELS.medium;
        const dist = p.distance_km != null ? `<span class="ms-auto text-nowrap">${p.distance_km.toLocaleString('vi-VN')} km</span>` : '';
        return `<a href="${esc(p.detail_url)}" class="wi-map-item" data-report-id="${p.id}">
            <span class="wi-dot mt-1" style="background:${lvl.color}"></span>
            <span class="flex-grow-1 min-w-0">
              <span class="d-block fw-semibold text-truncate">${esc(p.title)}</span>
              <span class="d-flex gap-2 small text-muted"><span class="text-truncate">${esc(p.category_name || '')} · ${esc(p.status_label)}</span>${dist}</span>
            </span>
          </a>`;
      }).join('');
    }

    fit() {
      const bounds = this.near ? this.nearCircle.getBounds() : this.layer.getBounds();
      if (bounds.isValid()) this.map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
    }

    focus(id) {
      const m = this.markers.get(id);
      if (!m) return;
      this.map.flyTo(m.getLatLng(), Math.max(this.map.getZoom(), 15), { duration: 0.6 });
      this.map.once('moveend', () => m.openPopup());
      if (window.innerWidth < 992) this.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    /* ----- Sự cố gần tôi ----- */
    locate() {
      if (!('geolocation' in navigator)) {
        this.askPick('Trình duyệt không hỗ trợ định vị GPS.');
        return;
      }
      this.el.classList.add('is-loading');
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          this.el.classList.remove('is-loading');
          this.setNear(pos.coords.latitude, pos.coords.longitude, true);
        },
        (err) => {
          this.el.classList.remove('is-loading');
          const why = err.code === err.PERMISSION_DENIED
            ? 'Bạn đã chặn quyền truy cập vị trí.'
            : 'Không lấy được vị trí GPS hiện tại.';
          this.askPick(why);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
      );
    }

    askPick(reason) {
      this.pickingCenter = true;
      this.el.classList.add('is-picking');
      toast(reason + ' Hãy nhấp vào bản đồ để chọn vị trí của bạn.', 'warning');
    }

    setNear(lat, lng, fromGps) {
      this.near = { lat, lng, radius: this.radius(), fromGps };
      this.drawNear();
      this.nearClear?.classList.remove('d-none');
      this.load(true);
    }

    drawNear() {
      this.nearLayer.clearLayers();
      const c = [this.near.lat, this.near.lng];
      this.nearCircle = L.circle(c, {
        radius: this.near.radius * 1000, color: '#0f766e', weight: 1.5, fillOpacity: 0.08, dashArray: '6 6',
      }).addTo(this.nearLayer);
      L.marker(c, {
        icon: L.divIcon({ className: 'wi-marker-wrap', html: '<span class="wi-me"></span>', iconSize: [18, 18], iconAnchor: [9, 9] }),
        interactive: false,
      }).addTo(this.nearLayer);
    }

    clearNear() {
      if (!this.near && !this.pickingCenter) return;
      this.near = null;
      this.pickingCenter = false;
      this.el.classList.remove('is-picking');
      this.nearLayer.clearLayers();
      this.nearClear?.classList.add('d-none');
      this.load(true);
    }

    syncUrl(qs) {
      // Chỉ lưu bộ lọc lên URL (chia sẻ được); không ghi toạ độ cá nhân của người dùng
      ['lat', 'lng', 'radius'].forEach((k) => qs.delete(k));
      const url = new URL(window.location.href);
      url.search = qs.toString();
      history.replaceState(null, '', url);
    }
  }

  window.WIMap = WIMap;
  document.querySelectorAll('[data-wi-map]').forEach((el) => {
    try { el._wiMap = new WIMap(el); } catch (e) { console.error('[WIMap]', e); }
  });
})();
