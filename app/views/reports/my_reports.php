<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="py-4 bg-light">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= url('') ?>">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="<?= url('report') ?>">Cảnh báo</a></li>
        <li class="breadcrumb-item active" aria-current="page">Báo cáo của tôi</li>
      </ol>
    </nav>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= $f['type'] ?> alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-info-circle me-2"></i><?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Header & Thống kê cá nhân -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-journal-text text-primary me-2"></i>Báo cáo của tôi</h2>
        <p class="text-muted mb-0">Theo dõi tiến độ tiếp nhận, xử lý và khắc phục các vấn đề môi trường bạn đã phản ánh.</p>
      </div>
      <div>
        <a href="<?= url('report/create') ?>" class="btn btn-warning fw-semibold px-4 py-2">
          <i class="bi bi-plus-circle me-1"></i> Gửi báo cáo mới
        </a>
      </div>
    </div>

    <!-- Thống kê 4 thẻ số liệu -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100">
          <div class="text-muted small mb-1">Tổng phản ánh</div>
          <div class="fs-3 fw-bold text-dark"><?= (int)($data['stats']['total'] ?? 0) ?></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 border-start border-primary border-4">
          <div class="text-muted small mb-1">Đang xử lý / Xác minh</div>
          <div class="fs-3 fw-bold text-primary"><?= (int)($data['stats']['processing'] ?? 0) ?></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 border-start border-success border-4">
          <div class="text-muted small mb-1">Đã giải quyết</div>
          <div class="fs-3 fw-bold text-success"><?= (int)($data['stats']['resolved'] ?? 0) ?></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 border-start border-secondary border-4">
          <div class="text-muted small mb-1">Từ chối / Bài không hợp lệ</div>
          <div class="fs-3 fw-bold text-secondary"><?= (int)($data['stats']['rejected'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <!-- Danh sách báo cáo -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
      <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Lịch sử sự cố đã gửi</h5>
        <span class="badge bg-light text-dark border"><?= count($data['reports']) ?> bản ghi</span>
      </div>

      <?php if (empty($data['reports'])): ?>
        <div class="card-body py-5 text-center">
          <div class="display-4 text-muted mb-3"><i class="bi bi-inbox"></i></div>
          <h5 class="fw-bold text-secondary">Bạn chưa gửi báo cáo sự cố nào</h5>
          <p class="text-muted small mb-4">Khi bạn phát hiện ô nhiễm rác thải, khói bụi, ngập úng hoặc tiếng ồn, hãy gửi thông tin để cơ quan chức năng kịp thời xử lý.</p>
          <a href="<?= url('report/create') ?>" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-megaphone me-1"></i> Gửi phản ánh đầu tiên
          </a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-muted text-uppercase">
              <tr>
                <th class="ps-4" style="width: 14%;">Mã số</th>
                <th style="width: 30%;">Tiêu đề & Danh mục</th>
                <th style="width: 22%;">Địa chỉ & Thời gian</th>
                <th style="width: 14%;">Mức độ</th>
                <th style="width: 12%;">Trạng thái</th>
                <th class="text-end pe-4" style="width: 8%;">Chi tiết</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($data['reports'] as $r): ?>
                <?php
                  // Mức độ
                  $sevClass = 'bg-secondary';
                  $sevLabel = 'Trung bình';
                  if ($r->priority === 'critical')     { $sevClass = 'bg-danger'; $sevLabel = 'Khẩn cấp'; }
                  elseif ($r->priority === 'high')     { $sevClass = 'bg-warning text-dark'; $sevLabel = 'Nghiêm trọng'; }
                  elseif ($r->priority === 'low')      { $sevClass = 'bg-info text-dark'; $sevLabel = 'Thấp'; }

                  // Trạng thái
                  $stBadge = 'bg-secondary';
                  $stText  = 'Mới tiếp nhận';
                  if ($r->status === 'verifying') {
                    $stBadge = 'bg-info text-dark';
                    $stText  = 'Đang xác minh';
                  } elseif ($r->status === 'in_progress' || $r->status === 'assigned') {
                    $stBadge = 'bg-primary';
                    $stText  = 'Đang xử lý';
                  } elseif ($r->status === 'resolved') {
                    $stBadge = 'bg-success';
                    $stText  = 'Đã giải quyết';
                  } elseif ($r->status === 'rejected') {
                    $stBadge = 'bg-dark';
                    $stText  = 'Từ chối';
                  }
                ?>
                <tr>
                  <td class="ps-4">
                    <span class="badge bg-light text-dark border font-monospace"><?= e($r->report_code) ?></span>
                  </td>
                  <td>
                    <a href="<?= url('report/detail/' . $r->id) ?>" class="fw-bold text-decoration-none text-dark d-block">
                      <?= e($r->title) ?>
                    </a>
                    <span class="badge bg-light text-muted border small mt-1">
                      <?= e($r->category_name ?? 'Sự cố môi trường') ?>
                    </span>
                  </td>
                  <td>
                    <div class="small text-truncate" style="max-width: 240px;" title="<?= e($r->address) ?>">
                      <i class="bi bi-geo-alt text-danger me-1"></i><?= e($r->address ?: 'Chưa có địa chỉ') ?>
                    </div>
                    <div class="small text-muted mt-1">
                      <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($r->incident_time)) ?>
                    </div>
                  </td>
                  <td>
                    <span class="badge <?= $sevClass ?>"><?= $sevLabel ?></span>
                  </td>
                  <td>
                    <span class="badge <?= $stBadge ?>"><?= $stText ?></span>
                  </td>
                  <td class="text-end pe-4">
                    <a href="<?= url('report/detail/' . $r->id) ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                      <i class="bi bi-arrow-right"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
