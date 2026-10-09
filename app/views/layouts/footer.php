<?php /* Layout cuối trang (TV4) – bản tĩnh */ ?>
  </main>
  <footer class="footer-wi" id="footer">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <a class="navbar-brand mb-3" href="<?= url('') ?>">
            <img src="<?= asset('img/logo.svg') ?>" alt="" width="36" height="36">
            <span>Warning<em>Info</em></span>
          </a>
          <p class="mb-3">Nền tảng giúp cộng đồng báo cáo, theo dõi và cảnh báo sớm các vấn đề môi trường — vì một Việt Nam xanh, sạch và an toàn hơn.</p>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
          <h6>Khám phá</h6>
          <ul class="list-unstyled footer-links mb-0">
            <li><a href="<?= url('') ?>">Trang chủ</a></li>
            <li><a href="<?= url('report') ?>">Cảnh báo mới</a></li>
            <li><a href="<?= url('report/create') ?>">Gửi báo cáo</a></li>
            <li><a href="<?= url('auth/register') ?>">Đăng ký thành viên</a></li>
          </ul>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <h6>Danh mục</h6>
          <ul class="list-unstyled footer-links mb-0">
            <li><a href="<?= url('report?category%5B0%5D=1') ?>">Ô nhiễm rác thải</a></li>
            <li><a href="<?= url('report?category%5B0%5D=2') ?>">Ô nhiễm nguồn nước</a></li>
            <li><a href="<?= url('report?category%5B0%5D=3') ?>">Ô nhiễm không khí</a></li>
            <li><a href="<?= url('report?category%5B0%5D=4') ?>">Ngập úng</a></li>
            <li><a href="<?= url('report?category%5B0%5D=5') ?>">Cháy rừng</a></li>
            <li><a href="<?= url('report?category%5B0%5D=6') ?>">Tiếng ồn</a></li>
          </ul>
        </div>
        <div class="col-md-4 col-lg-3">
          <h6>Khẩn cấp</h6>
          <div class="hotline-box mb-2">
            <small class="d-block mb-1">Cứu hoả – Cứu nạn</small>
            <strong><i class="bi bi-telephone-fill me-2"></i>114</strong>
          </div>
          <small>Công an: <b class="text-white">113</b> · Cấp cứu: <b class="text-white">115</b></small>
        </div>
      </div>
      <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
        <span>© <?= date('Y') ?> WarningInfo. Đồ án môn Lập trình Web – Nhóm 11.</span>
      </div>
    </div>
  </footer>
  <button class="back-to-top" type="button" aria-label="Lên đầu trang"><i class="bi bi-arrow-up"></i></button>
<?php require APPROOT . '/app/views/layouts/scripts.php'; ?>
