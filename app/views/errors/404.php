<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/errors.css') ?>" rel="stylesheet">

<section class="error-page">
  <?= wi_sprig_svg('error-sprig error-sprig-l sway', 'frond') ?>
  <?= wi_sprig_svg('error-sprig error-sprig-r sway') ?>
  <div class="container">
    <div class="error-card">
      <div class="error-visual" aria-hidden="true">
        <span class="error-code">4<i class="bi bi-tree-fill"></i>4</span>
      </div>
      <h1 class="error-title">Không tìm thấy trang</h1>
      <p class="error-text"><?= e($data['message'] ?? '') ?></p>
      <?php if (!empty($data['path']) && $data['path'] !== '/'): ?>
        <p class="error-path"><i class="bi bi-link-45deg me-1"></i><?= e($data['path']) ?></p>
      <?php endif; ?>

      <form class="error-search" action="<?= url('report') ?>" method="get" role="search">
        <i class="bi bi-search"></i>
        <input type="search" name="q" class="form-control" placeholder="Tìm sự cố theo từ khoá, địa chỉ..." aria-label="Tìm sự cố">
        <button type="submit" class="btn btn-primary">Tìm</button>
      </form>

      <div class="error-actions">
        <a href="<?= url('') ?>" class="btn btn-primary"><i class="bi bi-house me-1"></i> Quay lại trang chủ</a>
        <button type="button" class="btn btn-outline-secondary" onclick="history.length > 1 ? history.back() : location.assign('<?= url('') ?>')">
          <i class="bi bi-arrow-left me-1"></i> Trang trước
        </button>
      </div>

      <div class="error-links">
        <span>Có thể bạn đang tìm:</span>
        <a href="<?= url('report') ?>"><i class="bi bi-list-ul"></i> Danh sách cảnh báo</a>
        <a href="<?= url('report/map') ?>"><i class="bi bi-map"></i> Bản đồ sự cố</a>
        <a href="<?= url('report/create') ?>"><i class="bi bi-megaphone"></i> Gửi báo cáo</a>
      </div>
      <p class="error-foot">Mã lỗi 404</p>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
