<?php /* Trang chủ – cách hoạt động: 3 bước là các thẻ dính chồng lên nhau khi cuộn (wi-motion.js – [data-stack]) + kêu gọi gửi báo cáo */
$steps = [
  ['bi-camera',         'Gửi báo cáo',         'Chụp ảnh, ghim vị trí và mô tả ngắn gọn vấn đề bạn gặp phải.',
   ['Ảnh minh chứng', 'Vị trí GPS', 'Không bắt buộc đăng nhập']],
  ['bi-patch-check',    'Xác minh & cảnh báo', 'Quản trị viên và cộng đồng cùng xác nhận. Báo cáo được gắn mức độ để cảnh báo khu vực.',
   ['Mức độ nghiêm trọng', 'Cộng đồng xác nhận', 'Hiện trên bản đồ']],
  ['bi-building-check', 'Theo dõi xử lý',      'Báo cáo được chuyển đến cơ quan chức năng. Bạn theo dõi trạng thái xử lý ngay trên trang.',
   ['Cán bộ thụ lý', 'Thông báo tức thì', 'Lịch sử xử lý']],
];
?>
<!-- ========== CÁCH HOẠT ĐỘNG ========== -->
<section class="section how-section" id="cach-hoat-dong">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Cách WarningInfo hoạt động</h2>
      <p class="section-desc mx-auto">Mỗi báo cáo của bạn đều được xác minh và chuyển đến đúng nơi có thể xử lý.</p>
    </div>
    <ol class="stack-steps" data-stack>
      <?php foreach ($steps as $i => [$icon, $title, $text, $tags]): ?>
        <li class="stack-step" style="--i:<?= $i ?>">
          <article class="stack-card">
            <div class="stack-copy">
              <span class="stack-no" aria-hidden="true"><?= $i + 1 ?></span>
              <h3><span class="visually-hidden">Bước <?= $i + 1 ?>: </span><?= e($title) ?></h3>
              <p><?= e($text) ?></p>
              <ul class="stack-tags">
                <?php foreach ($tags as $tag): ?><li><?= e($tag) ?></li><?php endforeach; ?>
              </ul>
            </div>
            <div class="stack-art" aria-hidden="true">
              <?= wi_sprig_svg('stack-sprig', $i === 1 ? 'frond' : 'sprig') ?>
              <span class="stack-icon"><i class="bi <?= $icon ?>"></i></span>
            </div>
          </article>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<!-- ========== CTA ========== -->
<section class="pb-5">
  <div class="container">
    <div class="cta-band">
      <canvas class="wi-water" data-water="dark" data-sun="0.86,0.1" aria-hidden="true"></canvas>
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
