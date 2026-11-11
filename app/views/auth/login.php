<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/auth.css') ?>" rel="stylesheet">
<?php $err = $data['errors'] ?? []; $asideTitle = 'Chào mừng bạn quay lại'; ?>

<section class="section auth-section">
  <div class="container" style="max-width: 980px;">
    <div class="auth-card">
      <div class="row g-0">
        <?php require APPROOT . '/app/views/auth/_aside.php'; ?>
        <div class="col-lg-7">
          <div class="auth-form">
            <h1 class="auth-title">Đăng nhập</h1>
            <p class="auth-sub">Đăng nhập để gửi phản hồi, theo dõi báo cáo và nhận thông báo.</p>

            <?php if ($f = getFlash()): ?>
              <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
            <?php endif; ?>
            <?php if (!empty($err['general'])): ?>
              <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?= e($err['general']) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/login') ?>" novalidate>
              <?= csrf_field() ?>
              <div class="mb-3">
                <label class="form-label" for="loginEmail">Email</label>
                <div class="input-group has-validation">
                  <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                  <input type="email" name="email" id="loginEmail" autocomplete="email" placeholder="ban@example.com"
                         class="form-control <?= !empty($err['email']) ? 'is-invalid' : '' ?>" value="<?= e($data['email'] ?? '') ?>">
                  <?php if (!empty($err['email'])): ?><div class="invalid-feedback"><?= e($err['email']) ?></div><?php endif; ?>
                </div>
              </div>
              <div class="mb-4">
                <label class="form-label" for="loginPassword">Mật khẩu</label>
                <div class="input-group has-validation">
                  <span class="input-group-text"><i class="bi bi-lock"></i></span>
                  <input type="password" name="password" id="loginPassword" autocomplete="current-password" placeholder="Nhập mật khẩu"
                         class="form-control <?= !empty($err['password']) ? 'is-invalid' : '' ?>">
                  <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-toggle-password="#loginPassword" aria-label="Hiện mật khẩu"><i class="bi bi-eye"></i></button>
                  <?php if (!empty($err['password'])): ?><div class="invalid-feedback"><?= e($err['password']) ?></div><?php endif; ?>
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập</button>
            </form>

            <p class="auth-switch">Chưa có tài khoản? <a href="<?= url('auth/register') ?>">Đăng ký ngay</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?= asset('js/auth.js') ?>" defer></script>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
