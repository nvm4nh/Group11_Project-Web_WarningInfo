<?php /* Trang chủ – lưới danh mục (ô hình phiến lá), số báo cáo đếm từ CSDL */ ?>
<!-- ========== DANH MỤC ========== -->
<section class="section section-categories">
  <?= wi_sprig_svg('sec-sprig sec-sprig-right sway', 'frond') ?>
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <h2 class="section-title">Các vấn đề môi trường</h2>
        <p class="section-desc mb-0">Chọn loại vấn đề để xem các báo cáo và cảnh báo liên quan.</p>
      </div>
      <a href="<?= url('report') ?>" class="btn btn-outline-primary">Xem tất cả <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <?php $counts = $data['categoryCounts'] ?? []; ?>
    <div class="row g-3">
      <?php foreach (array_values($data['categories'] ?? []) as $i => $c): ?>
        <?php [$icon, $catCls] = categoryVisual($c->id); $n = (int)($counts[(int)$c->id] ?? 0); ?>
        <div class="col-6 col-md-4 col-lg-3 reveal" style="--i:<?= (int)$i ?>">
          <a href="<?= url('report?category_id=' . (int)$c->id) ?>" class="card-surface category-card">
            <span class="icon-circle <?= $catCls ?>"><i class="bi <?= $icon ?>"></i></span>
            <div>
              <h6><?= e($c->name) ?></h6>
              <span class="count"><?= $n > 0 ? number_format($n, 0, ',', '.') . ' báo cáo' : 'Chưa có báo cáo' ?></span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
