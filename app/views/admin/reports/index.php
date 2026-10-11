<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
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
            <th>Trạng thái xử lý</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($data['reports'] as $r): ?>
          <tr>
            <td>
              <span class="badge bg-light text-dark border"><?= e($r->report_code) ?></span>
            </td>
            <td>
              <div class="fw-bold"><?= e($r->title) ?></div>
              <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= e($r->address ?: 'Chưa cập nhật') ?></div>
            </td>
            <td><?= e($r->category_name ?? 'N/A') ?></td>
            <td>
              <?php
                $pBadge = 'bg-secondary';
                if ($r->priority === 'critical') $pBadge = 'bg-danger text-white';
                elseif ($r->priority === 'high') $pBadge = 'bg-warning text-dark';
                elseif ($r->priority === 'medium') $pBadge = 'bg-info text-dark';
                elseif ($r->priority === 'low') $pBadge = 'bg-success text-white';
              ?>
              <span class="badge <?= $pBadge ?>"><?= e($r->priority) ?></span>
            </td>
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
            </td>
            <td>
              <a href="<?= url('report/detail/' . $r->id) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Xem chi tiết">
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

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
