<?php /* Trang chủ – khối hero: tìm kiếm + khung "Đang diễn ra" – BẢN TĨNH (dữ liệu mẫu) */ ?>
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
          <select class="form-select w-auto flex-shrink-0" name="category[]" aria-label="Danh mục">
            <option value="">Tất cả danh mục</option>
            <option value="1">Ô nhiễm rác thải</option>
            <option value="2">Ô nhiễm nguồn nước</option>
            <option value="3">Ô nhiễm không khí</option>
            <option value="4">Ngập úng</option>
            <option value="5">Cháy rừng</option>
            <option value="6">Tiếng ồn</option>
            <option value="7">Sự cố hoá chất</option>
          </select>
          <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        </form>
        <div class="mt-4 d-flex flex-wrap gap-2">
          <a href="<?= url('report/create') ?>" class="btn btn-warning btn-lg fw-semibold"><i class="bi bi-megaphone me-1"></i> Gửi báo cáo</a>
          <a href="<?= url('report') ?>" class="btn btn-outline-light btn-lg">Xem cảnh báo</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="hero-panel">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="live">Đang diễn ra</span>
            <a href="<?= url('report?severity%5B0%5D=critical&severity%5B1%5D=high&status%5B0%5D=pending&status%5B1%5D=processing') ?>" class="small fw-semibold">Xem tất cả</a>
          </div>
            <div class="mini-alert">
              <span class="icon-circle cat-forest"><i class="bi bi-tree"></i></span>
              <div>
                <h6><a href="<?= url('report/detail/1841') ?>">Cháy rừng thông lan rộng, khói bao phủ khu dân cư lân cận</a></h6>
                <div class="d-flex align-items-center gap-2 small text-muted-2"><span class="badge-severity sev-critical"><i class="bi bi-exclamation-octagon-fill"></i> Nghiêm trọng</span> <span>3 giờ trước</span></div>
              </div>
            </div>
            <div class="mini-alert">
              <span class="icon-circle cat-air"><i class="bi bi-cloud-haze2"></i></span>
              <div>
                <h6><a href="<?= url('report/detail/1839') ?>">Khói đen dày đặc từ ống khói nhà máy vào ban đêm</a></h6>
                <div class="d-flex align-items-center gap-2 small text-muted-2"><span class="badge-severity sev-critical"><i class="bi bi-exclamation-octagon-fill"></i> Nghiêm trọng</span> <span>5 giờ trước</span></div>
              </div>
            </div>
            <div class="mini-alert">
              <span class="icon-circle cat-chem"><i class="bi bi-exclamation-octagon"></i></span>
              <div>
                <h6><a href="<?= url('report/detail/1836') ?>">Rò rỉ hoá chất từ xe bồn sau va chạm trên quốc lộ</a></h6>
                <div class="d-flex align-items-center gap-2 small text-muted-2"><span class="badge-severity sev-critical"><i class="bi bi-exclamation-octagon-fill"></i> Nghiêm trọng</span> <span>Hôm qua</span></div>
              </div>
            </div>
          <div class="aqi-chip mt-3" style="background:var(--wi-primary-soft);border-color:#b7dcd6">
            <div class="aqi-value text-primary">37</div>
            <div class="small">
              <b class="d-block">Báo cáo mới trong 24 giờ</b>
              <span class="text-muted-2">Cảm ơn cộng đồng đã chung tay</span>
            </div>
            <i class="bi bi-people ms-auto fs-4 text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
