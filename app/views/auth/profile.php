<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="py-4 bg-light">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= url('') ?>">Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thông tin cá nhân</li>
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

    <div class="row g-4">
      <!-- Cột trái: Tóm tắt tài khoản -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden text-center p-4">
          <div class="mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm" style="width: 84px; height: 84px; font-size: 2.2rem;">
              <i class="bi bi-person-fill"></i>
            </div>
          </div>
          <h4 class="fw-bold mb-1"><?= e($data['user']->full_name) ?></h4>
          <p class="text-muted small mb-2"><?= e($data['user']->email) ?></p>

          <div class="mb-3">
            <?php
              $roleBadge = 'bg-secondary';
              $roleName  = 'Người dân';
              if ($data['user']->role === 'admin') {
                $roleBadge = 'bg-danger';
                $roleName  = 'Quản trị viên';
              } elseif ($data['user']->role === 'officer') {
                $roleBadge = 'bg-success';
                $roleName  = 'Cán bộ xử lý';
              }
            ?>
            <span class="badge <?= $roleBadge ?> px-3 py-2"><?= $roleName ?></span>
            <span class="badge bg-light text-dark border px-3 py-2 ms-1">Đang hoạt động</span>
          </div>

          <div class="text-start border-top pt-3 small text-muted">
            <div class="d-flex justify-content-between mb-2">
              <span><i class="bi bi-calendar3 me-1"></i> Ngày tham gia:</span>
              <span class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($data['user']->created_at)) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span><i class="bi bi-telephone me-1"></i> Số điện thoại:</span>
              <span class="fw-semibold text-dark"><?= e($data['user']->phone ?: 'Chưa cập nhật') ?></span>
            </div>
          </div>

          <div class="mt-4 pt-2 border-top">
            <a href="<?= url('report/my_reports') ?>" class="btn btn-outline-primary w-100 fw-semibold">
              <i class="bi bi-journal-text me-1"></i> Xem báo cáo của tôi
            </a>
          </div>
        </div>
      </div>

      <!-- Cột phải: Form cập nhật thông tin & Đổi mật khẩu -->
      <div class="col-lg-8">
        <!-- Form cập nhật thông tin cá nhân -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-primary mb-1"><i class="bi bi-person-gear me-2"></i>Cập nhật thông tin cá nhân</h5>
            <p class="text-muted small">Cập nhật họ tên và thông tin liên lạc để cơ quan chức năng dễ dàng phản hồi khi xử lý sự cố.</p>
          </div>
          <div class="card-body p-4">
            <form action="<?= url('auth/profile') ?>" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update_info">

              <div class="mb-3">
                <label for="full_name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= !empty($data['profileErrors']['full_name']) ? 'is-invalid' : '' ?>" id="full_name" name="full_name" value="<?= e($data['user']->full_name) ?>" required>
                <?php if (!empty($data['profileErrors']['full_name'])): ?>
                  <div class="invalid-feedback"><?= $data['profileErrors']['full_name'] ?></div>
                <?php endif; ?>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label for="email" class="form-label fw-semibold">Địa chỉ Email</label>
                  <input type="email" class="form-control bg-light" id="email" value="<?= e($data['user']->email) ?>" disabled readonly>
                  <div class="form-text">Email dùng để đăng nhập và không thể thay đổi.</div>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                  <input type="tel" class="form-control" id="phone" name="phone" placeholder="VD: 0912345678" value="<?= e($data['user']->phone ?? '') ?>">
                </div>
              </div>

              <div class="text-end pt-2">
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                  <i class="bi bi-check2-circle me-1"></i> Lưu thay đổi
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Form đổi mật khẩu -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-danger mb-1"><i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu</h5>
            <p class="text-muted small">Mật khẩu mới cần tối thiểu 6 ký tự để đảm bảo tính an toàn cho tài khoản của bạn.</p>
          </div>
          <div class="card-body p-4">
            <form action="<?= url('auth/profile') ?>" method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="change_password">

              <div class="mb-3">
                <label for="current_password" class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                <input type="password" class="form-control <?= !empty($data['passwordErrors']['current_password']) ? 'is-invalid' : '' ?>" id="current_password" name="current_password" required>
                <?php if (!empty($data['passwordErrors']['current_password'])): ?>
                  <div class="invalid-feedback"><?= $data['passwordErrors']['current_password'] ?></div>
                <?php endif; ?>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label for="new_password" class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                  <input type="password" class="form-control <?= !empty($data['passwordErrors']['new_password']) ? 'is-invalid' : '' ?>" id="new_password" name="new_password" required>
                  <?php if (!empty($data['passwordErrors']['new_password'])): ?>
                    <div class="invalid-feedback"><?= $data['passwordErrors']['new_password'] ?></div>
                  <?php endif; ?>
                </div>
                <div class="col-md-6">
                  <label for="confirm_password" class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                  <input type="password" class="form-control <?= !empty($data['passwordErrors']['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required>
                  <?php if (!empty($data['passwordErrors']['confirm_password'])): ?>
                    <div class="invalid-feedback"><?= $data['passwordErrors']['confirm_password'] ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="text-end pt-2">
                <button type="submit" class="btn btn-outline-danger px-4 fw-semibold">
                  <i class="bi bi-key me-1"></i> Cập nhật mật khẩu
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
