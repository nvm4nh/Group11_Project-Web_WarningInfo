<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section py-4 bg-light border-bottom page-canopy"><?= wi_canopy() ?>
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-shield-exclamation text-primary me-2"></i>Danh sách cảnh báo & Sự cố</h1>
        <p class="text-muted mb-0">Theo dõi toàn bộ các vấn đề môi trường được người dân ghi nhận theo thời gian thực.</p>
      </div>
      <a href="<?= url('report/create') ?>" class="btn btn-warning fw-semibold shadow-sm">
        <i class="bi bi-megaphone me-1"></i> Gửi báo cáo mới
      </a>
    </div>

    <!-- Bộ lọc tìm kiếm: gửi form bình thường khi tắt JS; khi bật JS, ajax-filter.js lọc tại chỗ qua report/api_list -->
    <?php
      $f = $data['filters'];
      $badges = ['priority' => ReportModel::PRIORITY_BADGES, 'status' => ReportModel::STATUS_BADGES];
    ?>
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-body p-3">
        <form method="get" action="<?= url('report') ?>" class="row g-2 align-items-center" id="reportFilter"
              data-api="<?= url('report/api_list') ?>"
              data-detail-url="<?= url('report/detail/') ?>"
              data-page-size="<?= (int)$data['page_size'] ?>"
              data-badges="<?= e(json_encode($badges, JSON_UNESCAPED_UNICODE)) ?>">
          <div class="col-md-6 col-lg-3">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
              <input type="search" name="q" class="form-control" placeholder="Tìm theo từ khoá, địa chỉ..." aria-label="Từ khoá"
                     value="<?= e($f['keyword'] ?? '') ?>" autocomplete="off">
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <select name="category_id" class="form-select" aria-label="Danh mục">
              <option value="">Tất cả danh mục</option>
              <?php foreach ($data['categories'] as $c): ?>
                <option value="<?= $c->id ?>" <?= ($f['category_id'] ?? '') == $c->id ? 'selected' : '' ?>><?= e($c->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-4 col-lg-2">
            <select name="priority" class="form-select" aria-label="Mức độ">
              <option value="">Mọi mức độ</option>
              <?php foreach (ReportModel::PRIORITY_BADGES as $code => [$label]): ?>
                <option value="<?= $code ?>" <?= ($f['priority'] ?? '') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-4 col-lg-2">
            <select name="status" class="form-select" aria-label="Trạng thái">
              <option value="">Mọi trạng thái</option>
              <?php foreach (ReportModel::STATUS_BADGES as $code => [$label]): ?>
                <option value="<?= $code ?>" <?= ($f['status'] ?? '') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Lọc</button>
            <a href="<?= url('report') ?>" class="btn btn-outline-secondary" title="Đặt lại" aria-label="Đặt lại bộ lọc" data-filter-reset><i class="bi bi-arrow-counterclockwise"></i></a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<section class="section py-5">
  <div class="container">
    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php
      $total  = (int)$data['total'];
      $shown  = count($data['reports']);
      $nextQs = http_build_query(array_filter([
        'q' => $f['keyword'] ?? '', 'category_id' => $f['category_id'] ?? '', 'priority' => $f['priority'] ?? '',
        'status' => $f['status'] ?? '', 'page' => (int)$data['page'] + 1,
      ], 'strlen'));
    ?>
    <p class="text-muted small mb-3" id="reportCount" aria-live="polite">
      <?= $total > 0 ? 'Tìm thấy <strong>' . $total . '</strong> sự cố' : '' ?>
    </p>

    <div class="row g-4" id="reportResults" aria-busy="false">
      <?php if ($total === 0): ?>
        <div class="col-12 text-center py-5" data-empty>
          <i class="bi bi-inbox text-muted display-1"></i>
          <h4 class="mt-3 text-muted">Không tìm thấy báo cáo nào</h4>
          <p class="text-muted">Thử thay đổi bộ lọc hoặc <a href="<?= url('report/create') ?>">gửi báo cáo đầu tiên</a>.</p>
        </div>
      <?php else: ?>
        <?php foreach ($data['reports'] as $r) { require __DIR__ . '/_card.php'; } ?>
      <?php endif; ?>
    </div>

    <div class="text-center mt-4">
      <a href="<?= url('report?' . $nextQs) ?>" class="btn btn-outline-primary px-4<?= $shown < $total ? '' : ' d-none' ?>"
         id="reportLoadMore" data-page="<?= (int)$data['page'] ?>">
        <i class="bi bi-arrow-down-circle me-1"></i> Xem thêm sự cố
      </a>
    </div>
  </div>
</section>

<script src="<?= asset('js/ajax-filter.js') ?>" defer></script>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
