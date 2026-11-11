<?php
/* Trang lỗi 500 – trang ĐỘC LẬP (không dùng header/footer chung, không truy vấn CSDL)
   để vẫn hiển thị được kể cả khi lỗi nằm trong layout hoặc CSDL mất kết nối. */
$ref    = $data['ref'] ?? '';
$detail = $data['detail'] ?? null;
$home   = defined('URLROOT') ? rtrim(URLROOT, '/') . '/' : '/';
$asset  = $home . 'assets/';
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Hệ thống đang gặp sự cố | WarningInfo</title>
  <link rel="icon" href="<?= $h($asset . 'img/logo.svg') ?>" type="image/svg+xml">
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $h($asset . 'css/base.css') ?>" rel="stylesheet">
  <link href="<?= $h($asset . 'css/errors.css') ?>" rel="stylesheet">
</head>
<body class="error-standalone">
  <header class="error-topbar">
    <a href="<?= $h($home) ?>" class="error-brand">
      <img src="<?= $h($asset . 'img/logo.svg') ?>" alt="" width="34" height="34">
      <span>Warning<em>Info</em></span>
    </a>
  </header>

  <main class="error-page">
    <div class="container">
      <div class="error-card">
        <div class="error-visual error-visual-500" aria-hidden="true">
          <span class="error-code">5<i class="bi bi-gear-wide-connected"></i>0</span>
        </div>
        <h1 class="error-title">Hệ thống đang gặp sự cố</h1>
        <p class="error-text">Đã có lỗi xảy ra phía máy chủ nên trang chưa hiển thị được. Lỗi đã được ghi lại để nhóm kỹ thuật xử lý, bạn vui lòng thử lại sau ít phút.</p>

        <div class="error-actions">
          <button type="button" class="btn btn-primary" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i> Thử lại</button>
          <a href="<?= $h($home) ?>" class="btn btn-outline-secondary"><i class="bi bi-house me-1"></i> Quay lại trang chủ</a>
        </div>

        <div class="error-hotline">
          <i class="bi bi-telephone-fill"></i>
          <div>
            <b>Cần báo sự cố khẩn cấp?</b>
            <span>Gọi ngay Cứu hoả – Cứu nạn <a href="tel:114">114</a> · Công an <a href="tel:113">113</a> · Cấp cứu <a href="tel:115">115</a></span>
          </div>
        </div>

        <?php if ($ref !== ''): ?>
          <p class="error-foot">Mã lỗi 500 · Mã tra cứu: <code><?= $h($ref) ?></code></p>
        <?php endif; ?>

        <?php if ($detail): ?>
          <details class="error-debug" open>
            <summary><i class="bi bi-bug me-1"></i>Chi tiết kỹ thuật (chỉ hiện khi chạy trên máy phát triển)</summary>
            <p class="mb-1"><b><?= $h($detail['type'] ?? '') ?>:</b> <?= $h($detail['message'] ?? '') ?></p>
            <p class="mb-0 text-muted"><?= $h($detail['file'] ?? '') ?> : <?= (int)($detail['line'] ?? 0) ?></p>
          </details>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
