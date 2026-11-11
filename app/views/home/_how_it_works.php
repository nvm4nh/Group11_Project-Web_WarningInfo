<?php /* Trang chủ – cách hoạt động (3 bước nối bằng một dây leo mọc dần khi cuộn) + kêu gọi gửi báo cáo */
$steps = [
  ['bi-camera',         'Gửi báo cáo',           'Chụp ảnh, ghim vị trí và mô tả ngắn gọn vấn đề bạn gặp phải.'],
  ['bi-patch-check',    'Xác minh & cảnh báo',   'Quản trị viên và cộng đồng cùng xác nhận. Báo cáo được gắn mức độ để cảnh báo khu vực.'],
  ['bi-building-check', 'Theo dõi xử lý',        'Báo cáo được chuyển đến cơ quan chức năng. Bạn theo dõi trạng thái xử lý ngay trên trang.'],
];
// Lá trên dây leo: [x, y, góc xoay, cỡ, ngưỡng tiến độ (0–1) để lá bung ra]
$vineLeaves = [
  [70, 48, -62, 40, .05], [128, 78, 58, 34, .1], [262, 34, -48, 42, .21], [318, 34, 42, 34, .26],
  [455, 88, 64, 40, .37], [520, 92, 118, 32, .43], [668, 30, -56, 42, .55], [724, 36, 40, 34, .6],
  [858, 90, 62, 40, .71], [926, 90, 122, 32, .77], [1068, 34, -52, 42, .88], [1130, 30, 36, 36, .94],
];
?>
<!-- ========== CÁCH HOẠT ĐỘNG ========== -->
<section class="section how-section" id="cach-hoat-dong">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Cách WarningInfo hoạt động</h2>
      <p class="section-desc mx-auto">Mỗi báo cáo của bạn đều được xác minh và chuyển đến đúng nơi có thể xử lý.</p>
    </div>
    <!-- data-scrub: forest.js ghi tiến độ cuộn (0 → 1) vào biến --p; dây leo và lá dựa vào --p để mọc -->
    <div class="vine-wrap" data-scrub>
      <svg class="vine" viewBox="0 0 1200 120" aria-hidden="true" focusable="false">
        <path class="vine-stem" pathLength="1" d="M-10 78C90 30 150 100 200 60S330 14 400 62 520 108 600 60 730 14 800 62 920 108 1000 60 1130 20 1210 66"/>
        <?php foreach ($vineLeaves as [$x, $y, $rot, $size, $at]): ?>
          <g transform="translate(<?= $x ?> <?= $y ?>) rotate(<?= $rot ?>)"><g class="vine-leaf" style="--at:<?= $at ?>"><use href="#wi-leaf" width="<?= $size ?>" height="<?= round($size * .48, 1) ?>" y="<?= -round($size * .24, 1) ?>"/></g></g>
        <?php endforeach; ?>
      </svg>
      <div class="row g-4">
        <?php foreach ($steps as $i => [$icon, $title, $text]): ?>
          <div class="col-md-4">
            <div class="card-surface step-card" style="--at:<?= [.17, .5, .83][$i] ?>">
              <span class="step-bud" aria-hidden="true"><?= $i + 1 ?></span>
              <span class="icon-circle"><i class="bi <?= $icon ?>"></i></span>
              <h5><span class="visually-hidden">Bước <?= $i + 1 ?>: </span><?= e($title) ?></h5>
              <p class="text-muted-2 mb-0"><?= e($text) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<!-- ========== CTA ========== -->
<section class="pb-5">
  <div class="container">
    <div class="cta-band">
      <span class="cta-sun" aria-hidden="true"></span>
      <svg class="cta-trees" viewBox="0 0 1200 150" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
        <g class="ct-far"><?= wi_treeline(203, 1200, 150, ['count' => 26, 'min' => 44, 'max' => 92, 'palms' => .18, 'fill' => 4]) ?></g>
        <g class="ct-near"><?= wi_treeline(519, 1200, 150, ['count' => 14, 'min' => 30, 'max' => 64, 'palms' => .4, 'fill' => 4]) ?></g>
      </svg>
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
