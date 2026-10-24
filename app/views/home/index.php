<?php
if (!defined('APPROOT')) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($_SERVER['REQUEST_URI'] ?? '', 'Group11_Project-Web_WarningInfo') !== false) {
        header("Location: $scheme://$host/Group11_Project-Web_WarningInfo/");
    } else {
        header("Location: $scheme://$host/public/");
    }
    exit;
}
require APPROOT . '/app/views/layouts/header.php';
?>
    <?php if ($f = getFlash()): ?>
      <!-- Thông báo nhanh (vd: "Đăng nhập thành công!") – hiện dạng toast để không đẩy lệch khối hero -->
      <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <div class="toast show align-items-center text-bg-<?= e($f['type']) ?> border-0" role="alert" aria-live="polite" data-flash>
          <div class="d-flex">
            <div class="toast-body"><?= e($f['message']) ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php require APPROOT . '/app/views/home/_hero.php'; ?>
    <?php require APPROOT . '/app/views/home/_stats.php'; ?>
    <?php require APPROOT . '/app/views/home/_categories.php'; ?>
    <?php require APPROOT . '/app/views/home/_latest.php'; ?>
    <?php require APPROOT . '/app/views/home/_how_it_works.php'; ?>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
