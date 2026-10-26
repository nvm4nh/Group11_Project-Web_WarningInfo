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
      <div class="d-flex gap-2">
        <!-- NÚT XUẤT CSV ĐÃ ĐƯỢC THÊM VÀO ĐÂY -->
        <a href="<?= url('admin/export_csv') ?>" class="btn btn-success btn-sm">
          <i class="bi bi-file-earmark-excel"></i> Xuất báo cáo CSV
        </a>
        <a href="<?= url('admin') ?>" class="btn btn-outline-secondary btn-sm">← Quay lại Dashboard</a>
      </div>
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
              <form method="post" action="<?= url('admin/updateReportStatus/' . $r->id) ?>" class="d-flex gap-1 align-items-center">
                <select name="status" class="form-select form-select-sm" style="width: auto;">
                  <option value="new" <?= $r->status === 'new' ? 'selected' : '' ?>>Mới tiếp nhận</option>
                  <option value="verifying" <?= $r->status === 'verifying' ? 'selected' : '' ?>>Đang xác minh</option>
                  <option value="assigned" <?= $r->status === 'assigned' ? 'selected' : '' ?>>Đã phân công</option>
                  <option value="in_progress" <?= $r->status === 'in_progress' ? 'selected' : '' ?>>Đang xử lý</option>
                  <option value="resolved" <?= $r->status === 'resolved' ? 'selected' : '' ?>>Đã giải quyết</option>
                  <option value="rejected" <?= $r->status === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
              </form>
              <div class="small mt-1 <?= $assigned ? 'text-primary' : 'text-muted' ?>">
                <i class="bi bi-person-badge me-1"></i><?= $assigned ? 'Thụ lý: ' . e($assigned->officer_name) : 'Chưa phân công' ?>
                <?php if ($assigned && $assigned->deadline): ?>
                  <br><span class="text-danger"><i class="bi bi-clock"></i> Hạn: <?= date('d/m/Y', strtotime($assigned->deadline)) ?></span>
                <?php endif; ?>
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

<!-- MODAL PHÂN CÔNG CÁN BỘ (Thêm mới để JS hoạt động) -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="assignForm">
        <div class="modal-header">
          <h5 class="modal-title" id="assignModalLabel">Phân công cán bộ xử lý</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3 text-muted">Mã báo cáo: <strong id="assignCode"></strong></p>
          <div class="mb-3">
            <label class="form-label">Chọn cán bộ hiện trường</label>
            <select name="officer_id" class="form-select" required>
              <option value="">-- Chọn cán bộ --</option>
              <?php foreach ($officers as $officer): ?>
                <option value="<?= $officer->id ?>"><?= e($officer->full_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Hạn chót xử lý</label>
            <!-- Ô chọn ngày deadline -->
            <input type="date" name="deadline" class="form-control" required min="<?= date('Y-m-d') ?>">
            <div class="form-text">Chọn ngày cán bộ phải hoàn thành xử lý.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-success">Xác nhận Phân công</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var assignModal = document.getElementById('assignModal');
    if(assignModal) {
        assignModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var action = button.getAttribute('data-action');
            var code = button.getAttribute('data-code');
            var currentOfficer = button.getAttribute('data-officer');
            
            var form = document.getElementById('assignForm');
            form.setAttribute('action', action);
            document.getElementById('assignCode').textContent = code;
            
            // Đặt lại giá trị cán bộ đã chọn trước đó (nếu có)
            var select = form.querySelector('select[name="officer_id"]');
            if(currentOfficer) {
                select.value = currentOfficer;
            } else {
                select.value = "";
            }
        });
    }
});
</script>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>