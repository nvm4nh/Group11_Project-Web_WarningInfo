<?php /* Trang chủ – lưới danh mục – BẢN TĨNH (dữ liệu mẫu) */ ?>
<!-- ========== DANH MỤC ========== -->
<section class="section">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <span class="section-eyebrow"><i class="bi bi-grid"></i> Danh mục</span>
        <h2 class="section-title">Các vấn đề môi trường</h2>
        <p class="section-desc mb-0">Chọn loại vấn đề để xem các báo cáo và cảnh báo liên quan.</p>
      </div>
      <a href="<?= url('report') ?>" class="btn btn-outline-primary">Xem tất cả <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-3">
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <a href="<?= url('report?category%5B0%5D=1') ?>" class="card-surface category-card">
          <span class="icon-circle cat-waste"><i class="bi bi-trash3"></i></span>
          <div>
            <h6>Ô nhiễm rác thải</h6>
            <span class="count">415 báo cáo</span>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <a href="<?= url('report?category%5B0%5D=2') ?>" class="card-surface category-card">
          <span class="icon-circle cat-water"><i class="bi bi-droplet-half"></i></span>
          <div>
            <h6>Ô nhiễm nguồn nước</h6>
            <span class="count">289 báo cáo</span>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <a href="<?= url('report?category%5B0%5D=3') ?>" class="card-surface category-card">
          <span class="icon-circle cat-air"><i class="bi bi-cloud-haze2"></i></span>
          <div>
            <h6>Ô nhiễm không khí</h6>
            <span class="count">342 báo cáo</span>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <a href="<?= url('report?category%5B0%5D=4') ?>" class="card-surface category-card">
          <span class="icon-circle cat-flood"><i class="bi bi-cloud-lightning-rain"></i></span>
          <div>
            <h6>Ngập úng</h6>
            <span class="count">176 báo cáo</span>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <a href="<?= url('report?category%5B0%5D=5') ?>" class="card-surface category-card">
          <span class="icon-circle cat-forest"><i class="bi bi-tree"></i></span>
          <div>
            <h6>Cháy rừng</h6>
            <span class="count">97 báo cáo</span>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <a href="<?= url('report?category%5B0%5D=6') ?>" class="card-surface category-card">
          <span class="icon-circle cat-noise"><i class="bi bi-volume-up"></i></span>
          <div>
            <h6>Tiếng ồn</h6>
            <span class="count">128 báo cáo</span>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <a href="<?= url('report?category%5B0%5D=7') ?>" class="card-surface category-card">
          <span class="icon-circle cat-chem"><i class="bi bi-exclamation-octagon"></i></span>
          <div>
            <h6>Sự cố hoá chất</h6>
            <span class="count">41 báo cáo</span>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>
