<?php /* Layout cuối trang (TV4) */ ?>
  </main>
  <footer class="footer-wi" id="footer">
    <!-- Hàng cây viền trên chân trang: mọc lên khi cuộn tới (forest.js thêm class .in) -->
    <svg class="footer-trees reveal" viewBox="0 0 1440 150" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
      <g class="ft-far"><?= wi_treeline(407, 1440, 150, ['count' => 30, 'min' => 46, 'max' => 96, 'palms' => .2, 'fill' => 4]) ?></g>
      <g class="ft-near"><?= wi_treeline(912, 1440, 150, ['count' => 18, 'min' => 34, 'max' => 70, 'palms' => .35, 'fill' => 4]) ?></g>
    </svg>
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
            <li><a href="<?= url('report/map') ?>">Bản đồ sự cố</a></li>
            <li><a href="<?= url('report/create') ?>">Gửi báo cáo</a></li>
            <?php if (isLoggedIn()): ?>
              <li><a href="<?= url('report/my_reports') ?>">Báo cáo của tôi</a></li>
            <?php else: ?>
              <li><a href="<?= url('auth/register') ?>">Đăng ký thành viên</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <h6>Danh mục</h6>
          <ul class="list-unstyled footer-links mb-0">
            <li><a href="<?= url('report?category_id=1') ?>">Ô nhiễm rác thải</a></li>
            <li><a href="<?= url('report?category_id=2') ?>">Ô nhiễm nguồn nước</a></li>
            <li><a href="<?= url('report?category_id=3') ?>">Ô nhiễm không khí</a></li>
            <li><a href="<?= url('report?category_id=4') ?>">Ngập úng</a></li>
            <li><a href="<?= url('report?category_id=5') ?>">Cháy rừng</a></li>
            <li><a href="<?= url('report?category_id=6') ?>">Tiếng ồn</a></li>
            <li><a href="<?= url('report?category_id=7') ?>">Sự cố hoá chất</a></li>
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
        <span class="footer-note"><i class="bi bi-tree me-1"></i>Mỗi báo cáo là một mầm xanh cho thành phố</span>
      </div>
    </div>
  </footer>
  <button class="back-to-top" type="button" aria-label="Lên đầu trang"><i class="bi bi-arrow-up"></i></button>
<?php require APPROOT . '/app/views/layouts/scripts.php'; ?>
