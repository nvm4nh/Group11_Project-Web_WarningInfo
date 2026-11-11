<?php /* Trang chủ – cách hoạt động + kêu gọi gửi báo cáo – BẢN TĨNH (dữ liệu mẫu) */ ?>
<!-- ========== CÁCH HOẠT ĐỘNG ========== -->
<section class="section" id="cach-hoat-dong">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-eyebrow"><i class="bi bi-lightning-charge"></i> Đơn giản &amp; nhanh chóng</span>
      <h2 class="section-title">Cách WarningInfo hoạt động</h2>
      <p class="section-desc mx-auto">Mỗi báo cáo của bạn đều được xác minh và chuyển đến đúng nơi có thể xử lý.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4 reveal">
        <div class="card-surface step-card">
          <span class="step-no">01</span>
          <span class="icon-circle"><i class="bi bi-camera"></i></span>
          <h5>Gửi báo cáo</h5>
          <p class="text-muted-2 mb-0">Chụp ảnh, ghim vị trí và mô tả ngắn gọn vấn đề bạn gặp phải.</p>
        </div>
      </div>
      <div class="col-md-4 reveal">
        <div class="card-surface step-card">
          <span class="step-no">02</span>
          <span class="icon-circle"><i class="bi bi-patch-check"></i></span>
          <h5>Xác minh &amp; cảnh báo</h5>
          <p class="text-muted-2 mb-0">Quản trị viên và cộng đồng cùng xác nhận. Báo cáo được gắn mức độ để cảnh báo khu vực.</p>
        </div>
      </div>
      <div class="col-md-4 reveal">
        <div class="card-surface step-card">
          <span class="step-no">03</span>
          <span class="icon-circle"><i class="bi bi-building-check"></i></span>
          <h5>Theo dõi xử lý</h5>
          <p class="text-muted-2 mb-0">Báo cáo được chuyển đến cơ quan chức năng. Bạn theo dõi trạng thái xử lý ngay trên trang.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ========== CTA ========== -->
<section class="pb-5">
  <div class="container">
    <div class="cta-band">
      <div class="row align-items-center g-4 position-relative">
        <div class="col-lg-8">
          <h3 class="mb-2">Bạn vừa chứng kiến một sự cố môi trường?</h3>
          <p class="mb-0">Mỗi báo cáo đều có giá trị. Hãy góp phần để vấn đề được phát hiện sớm và xử lý kịp thời.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="<?= url('report/create') ?>" class="btn btn-warning btn-lg fw-semibold me-2 mb-2 mb-sm-0"><i class="bi bi-megaphone me-1"></i> Gửi báo cáo</a>
          <a href="<?= url('auth/register') ?>" class="btn btn-outline-light btn-lg">Đăng ký</a>
        </div>
      </div>
    </div>
  </div>
</section>
