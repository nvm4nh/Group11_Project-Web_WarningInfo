<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container" style="max-width: 520px;">
    <h1 class="section-title text-center mb-4">Đăng ký tài khoản</h1>

    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>

    <?php if (!empty($data['errors']['general'])): ?>
      <div class="alert alert-danger"><?= e($data['errors']['general']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= url('auth/register') ?>" novalidate>
      <div class="mb-3">
        <label class="form-label">Họ và tên</label>
        <input type="text" name="full_name" class="form-control <?= !empty($data['errors']['full_name']) ? 'is-invalid' : '' ?>"
               value="<?= e($data['old']['full_name'] ?? '') ?>">
        <?php if (!empty($data['errors']['full_name'])): ?>
          <div class="invalid-feedback"><?= e($data['errors']['full_name']) ?></div>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control <?= !empty($data['errors']['email']) ? 'is-invalid' : '' ?>"
               value="<?= e($data['old']['email'] ?? '') ?>">
        <?php if (!empty($data['errors']['email'])): ?>
          <div class="invalid-feedback"><?= e($data['errors']['email']) ?></div>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Mật khẩu</label>
        <input type="password" name="password" class="form-control <?= !empty($data['errors']['password']) ? 'is-invalid' : '' ?>">
        <?php if (!empty($data['errors']['password'])): ?>
          <div class="invalid-feedback"><?= e($data['errors']['password']) ?></div>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Xác nhận mật khẩu</label>
        <input type="password" name="password_confirm" class="form-control <?= !empty($data['errors']['password_confirm']) ? 'is-invalid' : '' ?>">
        <?php if (!empty($data['errors']['password_confirm'])): ?>
          <div class="invalid-feedback"><?= e($data['errors']['password_confirm']) ?></div>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-warning w-100">Đăng ký</button>
    </form>

    <p class="text-center mt-3">
      Đã có tài khoản? <a href="<?= url('auth/login') ?>">Đăng nhập</a>
    </p>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>