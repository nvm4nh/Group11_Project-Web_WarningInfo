<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="section-title mb-0">Quản lý người dùng</h1>
      <a href="<?= url('admin') ?>" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
    </div>

    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>

    <?php
      $roleLabels   = ['user' => 'Người dân', 'officer' => 'Cán bộ', 'admin' => 'Quản trị viên'];
      $statusLabels = ['active' => 'Hoạt động', 'inactive' => 'Vô hiệu hoá', 'locked' => 'Đã khoá'];
      $statusBadges = ['active' => 'success', 'inactive' => 'secondary', 'locked' => 'danger'];
    ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>#</th><th>Họ tên</th><th>Email</th><th>Trạng thái</th><th>Vai trò &amp; trạng thái</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($data['users'] as $u): $isMe = (int)$u->id === (int)($_SESSION['user_id'] ?? 0); ?>
          <tr>
            <td><?= (int)$u->id ?></td>
            <td><?= e($u->full_name) ?><?= $isMe ? ' <span class="badge text-bg-light">Bạn</span>' : '' ?></td>
            <td><?= e($u->email) ?></td>
            <td><span class="badge text-bg-<?= $statusBadges[$u->account_status] ?? 'secondary' ?>"><?= e($statusLabels[$u->account_status] ?? $u->account_status) ?></span></td>
            <td>
              <?php if ($isMe): ?>
                <span class="text-muted small">Không thể tự đổi vai trò / khoá chính mình</span>
              <?php else: ?>
              <form method="post" action="<?= url('admin/updateUser/' . (int)$u->id) ?>" class="row g-2 align-items-center">
                <?= csrf_field() ?>
                <div class="col-auto">
                  <select name="role" class="form-select form-select-sm" aria-label="Vai trò của <?= e($u->email) ?>">
                    <?php foreach ($roleLabels as $r => $label): ?>
                      <option value="<?= $r ?>" <?= $u->role === $r ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-auto">
                  <select name="account_status" class="form-select form-select-sm" aria-label="Trạng thái tài khoản của <?= e($u->email) ?>">
                    <?php foreach ($statusLabels as $st => $label): ?>
                      <option value="<?= $st ?>" <?= $u->account_status === $st ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-auto">
                  <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                </div>
              </form>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!$isMe): ?>
              <form method="post" action="<?= url('admin/deleteUser/' . (int)$u->id) ?>"
                    onsubmit="return confirm('Xoá người dùng này? Nên ưu tiên khoá tài khoản để giữ lại lịch sử báo cáo.');">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger">Xoá</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>