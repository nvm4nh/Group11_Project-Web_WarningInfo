<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section py-5">
  <div class="container" style="max-width: 560px;">
    <div class="card border-0 shadow-sm text-center p-4 p-md-5">
      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning"
           style="width: 72px; height: 72px; font-size: 2rem;">
        <i class="bi bi-shield-exclamation"></i>
      </div>
      <h1 class="h4 fw-bold mb-2">Không thể thực hiện yêu cầu</h1>
      <p class="text-secondary mb-4"><?= e($data['message'] ?? '') ?></p>
      <div class="d-flex flex-wrap justify-content-center gap-2">
        <button type="button" class="btn btn-primary" onclick="history.back()">
          <i class="bi bi-arrow-left me-1"></i> Quay lại trang trước
        </button>
        <a href="<?= e($data['backUrl'] ?? url('')) ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-clockwise me-1"></i> Tải lại trang
        </a>
      </div>
      <p class="small text-muted mt-4 mb-0">Mã lỗi 403 – thiếu hoặc sai mã bảo mật CSRF.</p>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
