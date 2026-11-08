<?php /* Trang chủ – Bản đồ điểm nóng sự cố (TV1 – Milestone 3). Dữ liệu GeoJSON nạp bằng AJAX. */ ?>
<section class="section" id="ban-do">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <span class="section-eyebrow"><i class="bi bi-geo-alt"></i> Bản đồ số</span>
        <h2 class="section-title">Điểm nóng môi trường quanh bạn</h2>
        <p class="text-muted mb-0">Đang theo dõi <b id="homeMapCount">0</b> sự cố chưa được giải quyết.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <select id="homeNearRadius" class="form-select w-auto" aria-label="Bán kính tìm kiếm">
          <option value="2">2 km</option>
          <option value="5" selected>5 km</option>
          <option value="10">10 km</option>
        </select>
        <button type="button" class="btn btn-outline-primary" id="homeNearMe"><i class="bi bi-crosshair me-1"></i> Sự cố gần tôi</button>
        <button type="button" class="btn btn-link d-none" id="homeNearClear">Bỏ lọc</button>
        <a href="<?= url('report/map') ?>" class="btn btn-primary"><i class="bi bi-arrows-fullscreen me-1"></i> Mở bản đồ lớn</a>
      </div>
    </div>
    <div class="wi-map" data-wi-map data-no-scroll-zoom
         data-api="<?= url('report/api_geojson?state=active') ?>"
         data-count-target="#homeMapCount"
         data-near-btn="#homeNearMe"
         data-near-clear="#homeNearClear"
         data-radius-select="#homeNearRadius"
         role="region" aria-label="Bản đồ sự cố đang diễn ra"></div>
  </div>
</section>
