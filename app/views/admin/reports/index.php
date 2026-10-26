<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<?php
$priorityMap = [
  'critical' => ['Nghiêm trọng', 'bg-danger text-white'],
  'high'     => ['Cao',          'bg-warning text-dark'],
  'medium'   => ['Trung bình',   'bg-info text-dark'],
  'low'      => ['Thấp',         'bg-success text-white'],
];
$closedStatuses = ['resolved', 'closed', 'rejected'];
$images      = $data['images'] ?? [];
$assignments = $data['assignments'] ?? [];
$officers    = $data['officers'] ?? [];
$statuses    = $data['statuses'] ?? [];
?>

<section class="section">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h1 class="section-title mb-0">Quản lý sự cố môi trường</h1>
        <p class="text-muted small mb-0">Tiếp nhận, kiểm tra và điều phối tiến trình xử lý báo cáo.</p>
      </div>
      <a href="<?= url('admin') ?>" class="btn btn-outline-secondary btn-sm">← Quay lại Dashboard</a>
    </div>

    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show">
        <?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="table-responsive bg-white rounded shadow-sm p-3">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Mã</th>
            <th>Tiêu đề & Địa chỉ</th>
            <th>Danh mục</th>
            <th>Mức độ</th>
            <th style="min-width: 260px;">Trạng thái xử lý</th>
            <th class="text-nowrap">Thao tác</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($data['reports'])): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Chưa có báo cáo nào.</td></tr>
        <?php endif; ?>

        <?php foreach ($data['reports'] as $r): ?>
          <?php
            [$pText, $pBadge] = $priorityMap[$r->priority] ?? [$r->priority, 'bg-secondary'];
            $assigned = $assignments[$r->id] ?? null;
            $isClosed = in_array($r->status, $closedStatuses, true);

            // Dữ liệu cho modal "Xem nhanh" — JS chỉ gán bằng textContent nên an toàn với XSS
            $quick = [
              'code'        => $r->report_code,
              'title'       => $r->title,
              'category'    => $r->category_name ?? 'N/A',
              'priority'    => $pText,
              'priorityCls' => $pBadge,
              'status'      => $statuses[$r->status] ?? $r->status,
              'address'     => $r->address ?: 'Chưa cập nhật',
              'coords'      => ($r->latitude && $r->longitude) ? $r->latitude . ', ' . $r->longitude : '',
              'contact'     => trim(implode(' · ', array_filter([$r->contact_name, $r->contact_phone, $r->contact_email]))) ?: 'Ẩn danh',
              'time'        => date('H:i d/m/Y', strtotime($r->incident_time)),
              'description' => $r->description,
              'officer'     => $assigned ? $assigned->officer_name : '',
              'detailUrl'   => url('report/detail/' . $r->id),
              'images'      => array_map(function ($img) {
                                 return ['src' => url($img->file_path), 'name' => (string)$img->original_file_name];
                               }, $images[$r->id] ?? []),
            ];
          ?>
          <tr>
            <td>
              <span class="badge bg-light text-dark border"><?= e($r->report_code) ?></span>
            </td>
            <td>
              <div class="fw-bold"><?= e($r->title) ?></div>
              <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= e($r->address ?: 'Chưa cập nhật') ?></div>
              <?php if (!empty($images[$r->id])): ?>
                <div class="text-muted small"><i class="bi bi-images me-1"></i><?= count($images[$r->id]) ?> ảnh minh chứng</div>
              <?php endif; ?>
            </td>
            <td><?= e($r->category_name ?? 'N/A') ?></td>
            <td><span class="badge <?= $pBadge ?>"><?= e($pText) ?></span></td>
            <td>
              <form method="post" action="<?= url('admin/updateReportStatus/' . $r->id) ?>" class="d-flex flex-column gap-1">
                <?= csrf_field() ?>
                <div class="d-flex gap-1">
                  <select name="status" class="form-select form-select-sm" aria-label="Trạng thái của <?= e($r->report_code) ?>">
                    <?php foreach ($statuses as $code => $label): ?>
                      <option value="<?= e($code) ?>" <?= $r->status === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                </div>
                <input type="text" name="note" class="form-control form-control-sm" maxlength="500"
                       placeholder="Ghi chú xử lý (tuỳ chọn)" aria-label="Ghi chú / lý do xử lý">
              </form>
              <div class="small mt-1 <?= $assigned ? 'text-primary' : 'text-muted' ?>">
                <i class="bi bi-person-badge me-1"></i><?= $assigned ? 'Thụ lý: ' . e($assigned->officer_name) : 'Chưa phân công' ?>
              </div>
            </td>
            <td class="text-nowrap">
              <button type="button" class="btn btn-sm btn-outline-primary" title="Xem nhanh"
                      data-bs-toggle="modal" data-bs-target="#quickViewModal"
                      data-report="<?= e(json_encode($quick, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
                <i class="bi bi-eye"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-success" title="Phân công cán bộ"
                      data-bs-toggle="modal" data-bs-target="#assignModal"
                      data-action="<?= e(url('admin/assignOfficer/' . $r->id)) ?>"
                      data-code="<?= e($r->report_code) ?>"
                      data-title="<?= e($r->title) ?>"
                      data-officer="<?= $assigned ? (int)$assigned->officer_id : '' ?>"
                      <?= $isClosed ? 'disabled' : '' ?>>
                <i class="bi bi-person-plus"></i>
              </button>
              <a href="<?= url('report/detail/' . $r->id) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Mở trang chi tiết">
                <i class="bi bi-box-arrow-up-right"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal: Xem nhanh chi tiết sự cố (TV5) -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="qvTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <span class="badge bg-light text-dark border" data-qv="code"></span>
          <h5 class="modal-title fw-bold mt-1" id="qvTitle" data-qv="title"></h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge" data-qv="priority"></span>
          <span class="badge bg-secondary" data-qv="status"></span>
          <span class="badge bg-light text-dark border"><i class="bi bi-tag me-1"></i><span data-qv="category"></span></span>
        </div>
        <dl class="row small mb-3">
          <dt class="col-sm-3 text-muted fw-normal">Thời điểm</dt>   <dd class="col-sm-9" data-qv="time"></dd>
          <dt class="col-sm-3 text-muted fw-normal">Địa chỉ</dt>     <dd class="col-sm-9" data-qv="address"></dd>
          <dt class="col-sm-3 text-muted fw-normal qv-coords">Toạ độ GPS</dt><dd class="col-sm-9 qv-coords" data-qv="coords"></dd>
          <dt class="col-sm-3 text-muted fw-normal">Người gửi</dt>   <dd class="col-sm-9" data-qv="contact"></dd>
          <dt class="col-sm-3 text-muted fw-normal">Cán bộ thụ lý</dt><dd class="col-sm-9" data-qv="officer"></dd>
        </dl>
        <h6 class="fw-bold">Mô tả chi tiết</h6>
        <p class="text-secondary mb-3" style="white-space: pre-line;" data-qv="description"></p>
        <h6 class="fw-bold">Ảnh minh chứng <span class="text-muted fw-normal" data-qv="imageCount"></span></h6>
        <div class="row g-2" id="qvImages"></div>
      </div>
      <div class="modal-footer">
        <a href="#" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm" id="qvDetailLink">
          <i class="bi bi-box-arrow-up-right me-1"></i>Mở trang chi tiết
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Phân công cán bộ xử lý (TV2) -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignTitle" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="" class="modal-content" id="assignForm">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="assignTitle">Phân công cán bộ xử lý</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">
          Sự cố: <span class="badge bg-light text-dark border" id="assignCode"></span>
          <span class="fw-semibold text-dark" id="assignReportTitle"></span>
        </p>
        <?php if (empty($officers)): ?>
          <div class="alert alert-warning small mb-0">
            Chưa có tài khoản cán bộ. Vào <a href="<?= url('admin/users') ?>">Quản lý người dùng</a> để cấp vai trò <strong>officer</strong>.
          </div>
        <?php else: ?>
          <div class="mb-3">
            <label for="assignOfficer" class="form-label fw-semibold">Cán bộ thụ lý <span class="text-danger">*</span></label>
            <select name="officer_id" id="assignOfficer" class="form-select" required>
              <option value="">— Chọn cán bộ —</option>
              <?php foreach ($officers as $o): ?>
                <option value="<?= (int)$o->id ?>"><?= e($o->full_name) ?> (<?= $o->role === 'admin' ? 'Quản trị viên' : 'Cán bộ' ?> · <?= e($o->email) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
                    <div class="mb-3">
            <label for="assignDeadline" class="form-label fw-semibold">Hạn chót xử lý</label>
            <input type="date" name="deadline" id="assignDeadline" class="form-control" min="<?= date('Y-m-d') ?>">
            <div class="form-text">Chọn ngày cán bộ cần hoàn thành. Không được chọn ngày trong quá khứ.</div>
          </div>
          <div>
            <label for="assignNote" class="form-label fw-semibold">Ghi chú giao việc</label>
            <textarea name="note" id="assignNote" rows="3" class="form-control" maxlength="1000"
                      placeholder="Ví dụ: Kiểm tra hiện trường trong 24h, phối hợp UBND phường..."></textarea>
          </div>
          <div class="form-text mt-2">Trạng thái sự cố sẽ tự chuyển sang <strong>Đã phân công</strong>; cán bộ và người gửi nhận được thông báo.</div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Huỷ</button>
        <button type="submit" class="btn btn-success btn-sm" <?= empty($officers) ? 'disabled' : '' ?>>
          <i class="bi bi-person-check me-1"></i>Phân công
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= asset('js/admin-reports.js') ?>" defer></script>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
