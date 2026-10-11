<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container">
    <h1 class="section-title mb-4">Bảng điều khiển Admin</h1>

    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-danger">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Tổng sự cố</h6>
            <p class="display-6 fw-bold mb-2"><?= (int)($data['totalReports'] ?? 0) ?></p>
            <a href="<?= url('admin/reports') ?>" class="btn btn-sm btn-outline-danger">Xem danh sách</a>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Đang xử lý</h6>
            <p class="display-6 fw-bold mb-2 text-primary"><?= (int)($data['processing'] ?? 0) ?></p>
            <a href="<?= url('admin/reports') ?>" class="btn btn-sm btn-outline-primary">Xem tiến trình</a>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-success">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Danh mục sự cố</h6>
            <p class="display-6 fw-bold mb-2"><?= (int)$data['totalCategories'] ?></p>
            <a href="<?= url('admin/categories') ?>" class="btn btn-sm btn-outline-success">Quản lý danh mục</a>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-info">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Người dùng & Cán bộ</h6>
            <p class="display-6 fw-bold mb-2"><?= (int)$data['totalUsers'] ?></p>
            <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-outline-info">Quản lý tài khoản</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>