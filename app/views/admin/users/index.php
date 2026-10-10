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

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>#</th><th>Họ tên</th><th>Email</th><th>Vai trò</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($data['users'] as $u): ?>
          <tr>
            <td><?= (int)$u->id ?></td>
            <td><?= e($u->full_name) ?></td>
            <td><?= e($u->email) ?></td>
            <td colspan="2">
              <form method="post" action="<?= url('admin/updateUser/' . $u->id) ?>" class="row g-2 align-items-center">
                <div class="col-auto">
                  <select name="role" class="form-select form-select-sm">
                    <?php foreach (['user','admin'] as $r): ?>
                      <option value="<?= $r ?>" <?= $u->role === $r ? 'selected' : '' ?>><?= $r ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-auto">
                  <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                </div>
              </form>
            </td>
            <td>
              <form method="post" action="<?= url('admin/deleteUser/' . $u->id) ?>"
                    onsubmit="return confirm('Xoá người dùng này?');">
                <button class="btn btn-sm btn-danger">Xoá</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>