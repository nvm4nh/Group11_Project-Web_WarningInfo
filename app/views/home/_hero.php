<?php /* Trang chủ – khối hero: tìm kiếm + khung "Đang diễn ra" (sự cố khẩn cấp lấy từ CSDL) */ ?>
<!-- ========== HERO ========== -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-7">
        <span class="hero-badge"><i class="bi bi-shield-check"></i> Nền tảng cộng đồng giám sát môi trường</span>
        <h1 class="mt-3 mb-3">Thấy vấn đề môi trường?<br><span class="hl">Báo ngay</span> để cùng xử lý.</h1>
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
      <div class="col-lg-5">
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
              <span class="icon-circle" style="background:#dcfce7;color:#16a34a"><i class="bi bi-shield-check"></i></span>
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
          <div class="aqi-chip mt-3" style="background:var(--wi-primary-soft);border-color:#b7dcd6">
            <div class="aqi-value text-primary"><?= $recent24h ?></div>
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
