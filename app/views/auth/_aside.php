<?php /* Cột giới thiệu bên trái form đăng nhập / đăng ký (TV4 – Milestone 4); ẩn trên điện thoại */ ?>
<div class="col-lg-5 d-none d-lg-flex">
  <div class="auth-aside">
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
