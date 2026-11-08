<?php
/* Trang bản đồ sự cố toàn màn hình – TV1 (Milestone 3, Issue #3)
   Dữ liệu nạp bằng AJAX từ report/api_geojson (public/assets/js/wi-map.js) */
require APPROOT . '/app/views/layouts/header.php';

// Khôi phục bộ lọc từ URL (?category_id=1,2&priority=high&state=active) để link chia sẻ được
$rawCats  = $_GET['category_id'] ?? [];
$selCats  = array_map('intval', is_array($rawCats) ? $rawCats : explode(',', (string)$rawCats));
$selPrio  = $_GET['priority'] ?? '';
$selState = $_GET['state'] ?? 'all';
$focusId  = (int)($_GET['focus'] ?? 0);
?>

<section class="section py-4 map-page">
  <div class="container-fluid px-3 px-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
      <div>
        <h1 class="h4 fw-bold mb-1"><i class="bi bi-map text-primary me-2"></i>Bản đồ sự cố môi trường</h1>
        <p class="text-muted small mb-0">Ghim theo toạ độ GPS người dân gửi. Đang hiển thị <b id="mapCount">0</b> sự cố.</p>
      </div>
      <a href="<?= url('report/create') ?>" class="btn btn-warning fw-semibold btn-sm"><i class="bi bi-megaphone me-1"></i> Gửi báo cáo</a>
    </div>

    <div class="row g-3">
      <!-- Bản đồ (lên trước trên mobile) -->
      <div class="col-lg-8 col-xl-9 order-lg-2">
        <div id="incidentMap" class="wi-map" data-wi-map
             data-api="<?= url('report/api_geojson') ?>"
             data-filter-form="#mapFilter"
             data-list="#mapResults"
             data-count-target="#mapCount"
             data-near-btn="#btnNearMe"
             data-near-clear="#btnNearClear"
             data-radius-select="#nearRadius"
             data-sync-url
             <?= $focusId ? 'data-focus="' . $focusId . '"' : '' ?>
             role="region" aria-label="Bản đồ sự cố"></div>
      </div>

      <!-- Bộ lọc + danh sách -->
      <div class="col-lg-4 col-xl-3 order-lg-1">
        <div class="map-sidebar d-flex flex-column gap-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
              <h2 class="h6 fw-bold mb-2"><i class="bi bi-crosshair me-1 text-primary"></i>Sự cố gần tôi</h2>
              <div class="input-group input-group-sm">
                <label class="input-group-text" for="nearRadius">Bán kính</label>
                <select id="nearRadius" class="form-select">
                  <?php foreach ($data['radiusOptions'] as $km): ?>
                    <option value="<?= $km ?>" <?= $km === 5 ? 'selected' : '' ?>><?= $km ?> km</option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="button" id="btnNearMe"><i class="bi bi-geo-alt-fill me-1"></i>Định vị</button>
              </div>
              <button type="button" id="btnNearClear" class="btn btn-link btn-sm px-0 mt-1 d-none"><i class="bi bi-x-circle me-1"></i>Bỏ lọc bán kính</button>
            </div>
          </div>

          <form id="mapFilter" class="card border-0 shadow-sm" autocomplete="off">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-funnel me-1 text-primary"></i>Bộ lọc</h2>
                <button type="reset" class="btn btn-link btn-sm p-0">Đặt lại</button>
              </div>

              <label class="form-label small fw-semibold mb-1" for="mapState">Tình trạng</label>
              <select name="state" id="mapState" class="form-select form-select-sm mb-2">
                <option value="all" <?= $selState === 'all' ? 'selected' : '' ?>>Tất cả</option>
                <option value="active" <?= $selState === 'active' ? 'selected' : '' ?>>Chưa giải quyết</option>
                <option value="resolved" <?= $selState === 'resolved' ? 'selected' : '' ?>>Đã giải quyết</option>
              </select>

              <label class="form-label small fw-semibold mb-1" for="mapPriority">Mức độ</label>
              <select name="priority" id="mapPriority" class="form-select form-select-sm mb-2">
                <option value="">Mọi mức độ</option>
                <option value="critical" <?= $selPrio === 'critical' ? 'selected' : '' ?>>Nghiêm trọng</option>
                <option value="high" <?= $selPrio === 'high' ? 'selected' : '' ?>>Cao</option>
                <option value="medium" <?= $selPrio === 'medium' ? 'selected' : '' ?>>Trung bình</option>
                <option value="low" <?= $selPrio === 'low' ? 'selected' : '' ?>>Thấp</option>
              </select>

              <div class="form-label small fw-semibold mb-1">Danh mục</div>
              <div class="row row-cols-2 row-cols-lg-1 g-0">
                <?php foreach ($data['categories'] as $c): ?>
                  <div class="col">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="category_id[]" value="<?= (int)$c->id ?>"
                             id="mapCat<?= (int)$c->id ?>" <?= in_array((int)$c->id, $selCats, true) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="mapCat<?= (int)$c->id ?>"><?= e($c->name) ?></label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </form>

          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2 small fw-bold">Danh sách trên bản đồ</div>
            <div id="mapResults" class="map-results" aria-live="polite">
              <div class="text-center text-muted small py-4">Đang tải dữ liệu…</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
