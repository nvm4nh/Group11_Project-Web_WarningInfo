<?php /* Trang chủ – khối hero: cảnh rừng nhiều lớp (SVG), ô tìm kiếm + khung "Đang diễn ra" (sự cố khẩn cấp lấy từ CSDL)
         Cảnh nền: mặt trời / tia nắng đi theo con trỏ chuột, các lớp cây trượt lệch nhau (parallax) – xem forest.js */ ?>
<!-- ========== HERO ========== -->
<section class="hero forest-hero" data-forest-hero>
  <div class="fh-scene" aria-hidden="true">
    <div class="fh-sky"></div>
    <div class="fh-sun"></div>
    <div class="fh-layer" data-depth=".03">
      <svg class="fh-band fh-city" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMax slice" focusable="false"><?= wi_skyline(77, 1440, 320, 150) ?></svg>
    </div>
    <div class="fh-layer" data-depth=".07">
      <svg class="fh-band fh-trees-far" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMax slice" focusable="false"><?= wi_treeline(31, 1440, 300, ['count' => 34, 'min' => 64, 'max' => 120, 'palms' => .14, 'fill' => 40]) ?></svg>
    </div>
    <div class="fh-rays"></div>
    <div class="fh-layer" data-depth=".15">
      <svg class="fh-band fh-trees-mid" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMax slice" focusable="false"><?= wi_treeline(58, 1440, 306, ['count' => 20, 'min' => 78, 'max' => 150, 'palms' => .32, 'fill' => 30]) ?></svg>
    </div>
    <canvas class="fh-motes"></canvas>
    <div class="fh-layer fh-front" data-depth=".3">
      <?= wi_sprig_svg('fh-leaf fh-leaf-l1 sway') ?>
      <?= wi_sprig_svg('fh-leaf fh-leaf-l2 sway', 'frond') ?>
    </div>
    <div class="fh-layer fh-front" data-depth=".46">
      <?= wi_sprig_svg('fh-leaf fh-leaf-r1 sway') ?>
      <?= wi_sprig_svg('fh-leaf fh-leaf-r2 sway', 'frond') ?>
      <?= wi_sprig_svg('fh-leaf fh-leaf-r3 sway') ?>
    </div>
    <svg class="fh-ground" viewBox="0 0 1440 90" preserveAspectRatio="none" focusable="false"><path d="M0 46C210 4 420 78 720 42S1210 8 1440 52V90H0Z"/></svg>
  </div>
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-7 fh-copy">
        <h1 class="mb-3">Thấy vấn đề môi trường?<br>Báo ngay để cùng xử lý.</h1>
        <p class="lead mb-4">Gửi báo cáo kèm hình ảnh và vị trí chỉ trong 1 phút. Theo dõi cảnh báo ô nhiễm, thiên tai và sự cố môi trường gần bạn.</p>
        <form class="hero-search d-flex flex-column flex-sm-row gap-2 gap-sm-0" id="heroSearch" action="<?= url('report') ?>" method="get" role="search">
          <div class="d-flex align-items-center flex-grow-1">
            <i class="bi bi-search text-muted-2 ms-3"></i>
            <input type="search" class="form-control" name="q" placeholder="Nhập từ khoá: cá chết, khói bụi, rác thải..." aria-label="Từ khoá">
          </div>
          <span class="divider"></span>
          <select class="form-select w-auto flex-shrink-0" name="category_id" aria-label="Danh mục">
            <option value="">Tất cả danh mục</option>
            <?php foreach (($data['categories'] ?? []) as $c): ?>
              <option value="<?= (int)$c->id ?>"><?= e($c->name) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        </form>
        <div class="mt-4 d-flex flex-wrap gap-2">
          <a href="<?= url('report/create') ?>" class="btn btn-warning btn-lg fw-semibold"><i class="bi bi-megaphone me-1"></i> Gửi báo cáo</a>
          <a href="<?= url('report') ?>" class="btn btn-outline-light btn-lg">Xem cảnh báo</a>
        </div>
      </div>
      <div class="col-lg-5 fh-side">
        <?php
          // Khung "Đang diễn ra": sự cố mức Cao / Nghiêm trọng chưa xử lý xong (dữ liệu thật – Milestone 4)
          $urgent    = $data['urgentReports'] ?? [];
          $recent24h = (int)($data['recent24h'] ?? 0);
        ?>
        <div class="hero-panel">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="live">Đang diễn ra</span>
            <a href="<?= url('report/map') ?>" class="small fw-semibold">Xem trên bản đồ</a>
          </div>
          <?php if (empty($urgent)): ?>
            <div class="mini-alert">
              <span class="icon-circle" style="background:#dcfce7;color:#15803d"><i class="bi bi-shield-check"></i></span>
              <div>
                <h6 class="mb-1">Hiện không có sự cố khẩn cấp nào</h6>
                <div class="small text-muted-2">Các sự cố mức Cao / Nghiêm trọng đang xử lý sẽ hiện ở đây.</div>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($urgent as $u): ?>
              <?php [$icon, $catCls] = categoryVisual($u->category_id); [$sevText, $sevCls, $sevIcon] = severityVisual($u->priority); ?>
              <div class="mini-alert">
                <span class="icon-circle <?= $catCls ?>"><i class="bi <?= $icon ?>"></i></span>
                <div>
                  <h6><a href="<?= url('report/detail/' . (int)$u->id) ?>"><?= e($u->title) ?></a></h6>
                  <div class="d-flex flex-wrap align-items-center gap-2 small text-muted-2">
                    <span class="badge-severity <?= $sevCls ?>"><i class="bi <?= $sevIcon ?>"></i> <?= e($sevText) ?></span>
                    <span><?= e(timeAgo($u->age_seconds, $u->created_at)) ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
          <div class="aqi-chip mt-3">
            <div class="aqi-value"><?= $recent24h ?></div>
            <div class="small">
              <b class="d-block">Báo cáo mới trong 24 giờ</b>
              <span class="text-muted-2"><?= $recent24h > 0 ? 'Cảm ơn cộng đồng đã chung tay' : 'Thấy sự cố? Hãy là người báo đầu tiên' ?></span>
            </div>
            <i class="bi bi-people ms-auto fs-4 text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
