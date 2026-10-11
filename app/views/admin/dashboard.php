<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container">
    <h1 class="section-title mb-4">Bảng điều khiển Admin</h1>

    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Người dùng</h5>
            <p class="display-6 mb-0"><?= (int)$data['totalUsers'] ?></p>
            <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-outline-primary mt-2">Quản lý</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Danh mục</h5>
            <p class="display-6 mb-0"><?= (int)$data['totalCategories'] ?></p>
            <a href="<?= url('admin/categories') ?>" class="btn btn-sm btn-outline-primary mt-2">Quản lý</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>