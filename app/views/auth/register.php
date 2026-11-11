<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/auth.css') ?>" rel="stylesheet">
<?php $err = $data['errors'] ?? []; $old = $data['old'] ?? []; $asideTitle = 'Tham gia giám sát môi trường'; ?>

<section class="section auth-section">
  <div class="container" style="max-width: 980px;">
    <div class="auth-card">
      <div class="row g-0">
        <?php require APPROOT . '/app/views/auth/_aside.php'; ?>
        <div class="col-lg-7">
          <div class="auth-form">
            <h1 class="auth-title">Đăng ký tài khoản</h1>
            <p class="auth-sub">Miễn phí, chỉ mất chưa đến 1 phút.</p>

            <?php if ($f = getFlash()): ?>
              <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
            <?php endif; ?>
            <?php if (!empty($err['general'])): ?>
              <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?= e($err['general']) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/register') ?>" novalidate id="registerForm">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label class="form-label" for="regName">Họ và tên</label>
                <div class="input-group has-validation">
                  <span class="input-group-text"><i class="bi bi-person"></i></span>
                  <input type="text" name="full_name" id="regName" autocomplete="name" placeholder="Nguyễn Văn A" maxlength="100"
                         class="form-control <?= !empty($err['full_name']) ? 'is-invalid' : '' ?>" value="<?= e($old['full_name'] ?? '') ?>">
                  <?php if (!empty($err['full_name'])): ?><div class="invalid-feedback"><?= e($err['full_name']) ?></div><?php endif; ?>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="regEmail">Email</label>
                <div class="input-group has-validation">
                  <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                  <input type="email" name="email" id="regEmail" autocomplete="email" placeholder="ban@example.com"
                         class="form-control <?= !empty($err['email']) ? 'is-invalid' : '' ?>" value="<?= e($old['email'] ?? '') ?>">
                  <?php if (!empty($err['email'])): ?><div class="invalid-feedback"><?= e($err['email']) ?></div><?php endif; ?>
                </div>
              </div>
              <div class="row g-3 mb-4">
                <div class="col-sm-6">
                  <label class="form-label" for="regPassword">Mật khẩu</label>
                  <div class="input-group has-validation">
                    <input type="password" name="password" id="regPassword" autocomplete="new-password" placeholder="Tối thiểu 6 ký tự"
                           class="form-control <?= !empty($err['password']) ? 'is-invalid' : '' ?>">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-toggle-password="#regPassword" aria-label="Hiện mật khẩu"><i class="bi bi-eye"></i></button>
                    <?php if (!empty($err['password'])): ?><div class="invalid-feedback"><?= e($err['password']) ?></div><?php endif; ?>
                  </div>
                  <div class="form-text" id="passHint">Tối thiểu 6 ký tự.</div>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="regConfirm">Xác nhận mật khẩu</label>
                  <div class="input-group has-validation">
                    <input type="password" name="password_confirm" id="regConfirm" autocomplete="new-password" placeholder="Nhập lại mật khẩu"
                           class="form-control <?= !empty($err['password_confirm']) ? 'is-invalid' : '' ?>">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-toggle-password="#regConfirm" aria-label="Hiện mật khẩu"><i class="bi bi-eye"></i></button>
                    <?php if (!empty($err['password_confirm'])): ?><div class="invalid-feedback"><?= e($err['password_confirm']) ?></div><?php endif; ?>
                  </div>
                  <div class="form-text" id="confirmHint" aria-live="polite"></div>
                </div>
              </div>
              <button type="submit" class="btn btn-warning w-100 btn-lg fw-semibold"><i class="bi bi-person-plus me-1"></i> Đăng ký</button>
            </form>

            <p class="auth-switch">Đã có tài khoản? <a href="<?= url('auth/login') ?>">Đăng nhập</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?= asset('js/auth.js') ?>" defer></script>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
