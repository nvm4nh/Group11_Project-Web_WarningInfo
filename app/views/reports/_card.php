<?php
/* Thẻ 1 sự cố ở trang /report (TV5 – Milestone 3).
   ajax-filter.js dựng lại đúng cấu trúc này khi nạp bằng AJAX – sửa ở đây thì sửa cả renderCard() bên JS. */
[$sevLabel, $sevClass] = ReportModel::priorityBadge($r->priority);
[$stText, $stBadge]    = ReportModel::statusBadge($r->status);
$desc = (string)$r->description;
?>
<div class="col-md-6 col-lg-4">
  <div class="card h-100 border-0 shadow-sm hover-shadow transition">
    <div class="card-body d-flex flex-column">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="badge <?= $sevClass ?>"><?= e($sevLabel) ?></span>
        <span class="badge <?= $stBadge ?>"><?= e($stText) ?></span>
      </div>
      <h5 class="card-title fw-bold mb-2">
        <a href="<?= url('report/detail/' . $r->id) ?>" class="text-decoration-none text-dark">
          <?= e($r->title) ?>
        </a>
      </h5>
      <p class="card-text text-muted small flex-grow-1">
        <?= e(mb_substr($desc, 0, 110)) ?><?= mb_strlen($desc) > 110 ? '...' : '' ?>
      </p>
      <div class="small text-muted mb-2">
        <i class="bi bi-geo-alt me-1 text-danger"></i> <?= e($r->address ?: 'TP. Hồ Chí Minh') ?>
      </div>
      <div class="d-flex justify-content-between align-items-center pt-2 border-top small text-muted">
        <span><i class="bi bi-clock me-1"></i><?= date('d/m/Y', strtotime($r->incident_time)) ?></span>
        <div class="d-flex gap-3">
          <span title="Lượt ủng hộ"><i class="bi bi-hand-thumbs-up me-1"></i><?= (int)$r->upvote_count ?></span>
          <span title="Bình luận"><i class="bi bi-chat-dots me-1"></i><?= (int)$r->comment_count ?></span>
        </div>
      </div>
    </div>
  </div>
</div>
