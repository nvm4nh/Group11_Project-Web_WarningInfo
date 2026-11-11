<?php /* Trang chủ – 6 cảnh báo mới nhất từ CSDL, lọc theo mức độ bằng ?severity=... */ ?>
<!-- ========== CẢNH BÁO MỚI NHẤT ==========
     Hiện tại lọc bằng link ?severity=...  TV5 có thể đổi sang AJAX (data-severity) -->
<section class="section bg-white border-top border-bottom" id="latest">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <span class="section-eyebrow"><i class="bi bi-broadcast"></i> Cập nhật liên tục</span>
        <h2 class="section-title">Cảnh báo mới nhất</h2>
      </div>
      <ul class="nav nav-pills gap-1 flex-nowrap overflow-x-auto pb-1 mw-100" id="latestTabs">
        <?php $sev = $data['severity'] ?? null; ?>
        <?php foreach (['' => 'Tất cả', 'critical' => 'Nghiêm trọng', 'high' => 'Cao', 'medium' => 'Trung bình', 'low' => 'Thấp'] as $k => $label): ?>
          <li class="nav-item"><a class="nav-link<?= (string)$sev === (string)$k || ($k === '' && !$sev) ? ' active' : '' ?>" data-severity="<?= $k ?>" href="<?= url($k === '' ? '' : '?severity=' . $k) ?>#latest"><?= $label ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="row g-4" id="latestAlerts">
      <?php if (empty($data['latestReports'])): ?>
        <div class="col-12">
          <div class="card-surface text-center py-5 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Chưa có cảnh báo nào ở mức độ này.
          </div>
        </div>
      <?php endif; ?>
      <?php foreach (($data['latestReports'] ?? []) as $r): ?>
        <?php
          [$icon, , $placeholder]       = categoryVisual($r->category_id);
          [$sevText, $sevCls, $sevIcon] = severityVisual($r->priority);
          [$stText, $stCls]             = ReportModel::statusBadge($r->status);
          $thumb = !empty($r->thumbnail) ? uploadUrl($r->thumbnail) : asset('img/placeholder/' . $placeholder);
          $desc  = (string)$r->description;
        ?>
        <div class="col-md-6 col-lg-4">
          <article class="alert-card" data-id="<?= (int)$r->id ?>">
            <div class="thumb">
              <img src="<?= e($thumb) ?>" alt="<?= e($r->title) ?>" loading="lazy">
              <span class="badge-severity <?= $sevCls ?>"><i class="bi <?= $sevIcon ?>"></i> <?= e($sevText) ?></span>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="category"><i class="bi <?= $icon ?>"></i> <?= e($r->category_name ?? 'Khác') ?></span>
                <span class="badge <?= $stCls ?>"><?= e($stText) ?></span>
              </div>
              <h3 class="title"><a href="<?= url('report/detail/' . (int)$r->id) ?>"><?= e($r->title) ?></a></h3>
              <p class="excerpt"><?= e(mb_substr($desc, 0, 140)) ?><?= mb_strlen($desc) > 140 ? '…' : '' ?></p>
              <div class="meta"><span><i class="bi bi-geo-alt"></i><?= e($r->address ?: 'Chưa cập nhật địa chỉ') ?></span></div>
              <div class="card-foot">
                <span><i class="bi bi-clock me-1"></i><?= e(timeAgo($r->age_seconds ?? 0, $r->created_at)) ?></span>
                <span class="stats">
                  <span title="Lượt xác nhận"><i class="bi bi-hand-thumbs-up"></i> <?= (int)$r->upvote_count ?></span>
                  <span title="Bình luận"><i class="bi bi-chat-dots"></i> <?= (int)$r->comment_count ?></span>
                </span>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-5">
      <a href="<?= url('report') ?>" class="btn btn-primary btn-lg px-4">Xem tất cả cảnh báo <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>
