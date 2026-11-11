<?php /* Cột giới thiệu bên trái form đăng nhập / đăng ký (TV4 – Milestone 4); ẩn trên điện thoại.
         Nền là một góc rừng thu nhỏ: mặt trời, hàng cây và cành lá (SVG – layouts/_forest.php). */ ?>
<div class="col-lg-5 d-none d-lg-flex">
  <div class="auth-aside">
    <span class="auth-sun" aria-hidden="true"></span>
    <?= wi_sprig_svg('auth-sprig sway') ?>
    <svg class="auth-trees" viewBox="0 0 520 170" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
      <g class="at-far"><?= wi_treeline(66, 520, 170, ['count' => 12, 'min' => 50, 'max' => 100, 'palms' => .2, 'fill' => 4]) ?></g>
      <g class="at-near"><?= wi_treeline(140, 520, 170, ['count' => 7, 'min' => 36, 'max' => 70, 'palms' => .45, 'fill' => 4]) ?></g>
    </svg>
    <div class="auth-aside-body">
      <img src="<?= asset('img/logo.svg') ?>" alt="" width="48" height="48" class="mb-3">
      <h2><?= e($asideTitle ?? 'Cùng cộng đồng bảo vệ môi trường') ?></h2>
      <p>Tài khoản WarningInfo giúp bạn theo dõi mọi báo cáo đã gửi và nhận phản hồi từ cán bộ xử lý.</p>
      <ul class="auth-benefits">
        <li><i class="bi bi-camera"></i><span>Gửi báo cáo kèm ảnh minh chứng và toạ độ GPS</span></li>
        <li><i class="bi bi-clipboard-check"></i><span>Theo dõi tiến độ xử lý ở "Báo cáo của tôi"</span></li>
        <li><i class="bi bi-bell"></i><span>Nhận thông báo ngay khi sự cố được cập nhật</span></li>
      </ul>
    </div>
  </div>
</div>
