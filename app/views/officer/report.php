<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/report-detail.css') ?>" rel="stylesheet">
<link href="<?= asset('css/officer.css') ?>" rel="stylesheet">
<?php
require APPROOT . '/app/views/officer/_labels.php';
$r = $data['report'];
$a = $data['assignment'];
[$pText, $pCls] = $priorityBadge[$r->priority] ?? ['Trung bình', 'bg-warning text-dark'];
[$aText, $aCls] = $assignmentBadge[$a->assignment_status] ?? [$a->assignment_status, 'bg-secondary'];

// Ảnh minh chứng (cùng cách hiển thị với trang chi tiết công khai – gallery.js)
$media = [];
foreach (($r->images ?? []) as $img) {
  $img  = (object)$img;
  $path = trim((string)($img->file_path ?? ''));
  if ($path === '') continue;
  $media[] = [
    'src'   => preg_match('#^https?://#i', $path) ? $path : url(preg_replace('#^/?(public/)?#', '', $path)),
    'video' => strpos((string)($img->mime_type ?? ''), 'video/') === 0,
    'name'  => (string)($img->original_file_name ?? ''),
  ];
}
$hasGps = $r->latitude !== null && $r->longitude !== null && $r->latitude !== '' && $r->longitude !== '';
?>

<section class="section py-4 bg-light border-bottom">
  <div class="container">
    <a href="<?= url('officer') ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Bàn làm việc</a>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-2">
      <div>
        <h1 class="h3 fw-bold mb-1"><?= e($r->title) ?></h1>
        <div class="d-flex flex-wrap gap-2 align-items-center small text-muted">
          <span class="badge bg-light text-dark border font-monospace"><?= e($r->report_code) ?></span>
          <span><i class="bi bi-clock me-1"></i><?= e(date('H:i - d/m/Y', strtotime($r->incident_time))) ?></span>
          <span><i class="bi bi-tag me-1"></i><?= e($r->category_name ?? 'Khác') ?></span>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge <?= $pCls ?> fs-6 px-3 py-2"><?= e($pText) ?></span>
        <span class="badge <?= $statusBadge[$r->status] ?? 'bg-secondary' ?> fs-6 px-3 py-2"><?= e(NotificationModel::statusLabel($r->status)) ?></span>
      </div>
    </div>
  </div>
</section>

<section class="section py-4">
  <div class="container">
    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show">
        <?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Cột phải (trên điện thoại hiện trước): nhiệm vụ & thao tác -->
      <div class="col-lg-4 order-lg-2">
        <div class="card border-0 shadow-sm officer-panel">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-clipboard-check me-2 text-primary"></i>Nhiệm vụ của bạn</h2>
            <span class="badge <?= $aCls ?>"><?= e($aText) ?></span>
          </div>
          <div class="card-body">
            <dl class="officer-dl mb-0">
              <dt>Người giao</dt><dd><?= e($a->assigner_name ?: 'Quản trị viên') ?></dd>
              <dt>Thời điểm giao</dt><dd><?= e(date('H:i d/m/Y', strtotime($a->assigned_at))) ?></dd>
              <?php if (!empty($a->deadline)): ?>
                <dt>Hạn xử lý</dt><dd class="text-danger fw-semibold"><?= e(date('H:i d/m/Y', strtotime($a->deadline))) ?></dd>
              <?php endif; ?>
              <?php if (!empty($a->completed_at)): ?>
                <dt>Hoàn thành lúc</dt><dd><?= e(date('H:i d/m/Y', strtotime($a->completed_at))) ?></dd>
              <?php endif; ?>
            </dl>
            <?php if (!empty($a->assignment_note)): ?>
              <div class="task-note mt-3 mb-0"><i class="bi bi-chat-left-quote me-1"></i><?= e($a->assignment_note) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Thông tin liên hệ người gửi: chỉ cán bộ/quản trị thấy -->
        <div class="card border-0 shadow-sm mt-4">
          <div class="card-header bg-white py-3">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Liên hệ người gửi</h2>
          </div>
          <div class="card-body small">
            <div class="mb-1"><i class="bi bi-person me-2 text-muted"></i><?= e($r->contact_name ?: ($r->author_name ?: 'Ẩn danh')) ?></div>
            <?php if (!empty($r->contact_phone)): ?>
              <div class="mb-1"><i class="bi bi-telephone me-2 text-muted"></i><a href="tel:<?= e($r->contact_phone) ?>"><?= e($r->contact_phone) ?></a></div>
            <?php endif; ?>
            <?php if (!empty($r->contact_email)): ?>
              <div class="mb-1"><i class="bi bi-envelope me-2 text-muted"></i><a href="mailto:<?= e($r->contact_email) ?>"><?= e($r->contact_email) ?></a></div>
            <?php endif; ?>
            <?php if (empty($r->contact_phone) && empty($r->contact_email)): ?>
              <div class="text-muted">Người gửi không để lại số điện thoại / email.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Cột trái: nội dung sự cố -->
      <div class="col-lg-8 order-lg-1">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3"><i class="bi bi-file-text me-2 text-primary"></i>Nội dung sự cố</h2>
            <p class="text-secondary mb-4" style="white-space: pre-line;"><?= e($r->description) ?></p>

            <div class="p-3 bg-light rounded-3 mb-4 small">
              <div class="row g-2">
                <div class="col-sm-6">
                  <span class="text-muted">Địa chỉ hiện trường:</span>
                  <div class="fw-semibold"><i class="bi bi-geo-alt text-danger me-1"></i><?= e($r->address ?: 'Chưa cập nhật') ?></div>
                </div>
                <div class="col-sm-6">
                  <span class="text-muted">Toạ độ GPS:</span>
                  <?php if ($hasGps): ?>
                    <div class="fw-semibold">
                      <i class="bi bi-crosshair text-primary me-1"></i><?= e($r->latitude) ?>, <?= e($r->longitude) ?>
                      <a class="ms-1" target="_blank" rel="noopener" href="https://www.google.com/maps?q=<?= e($r->latitude) ?>,<?= e($r->longitude) ?>">Chỉ đường <i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                  <?php else: ?>
                    <div class="text-muted">Không có</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <h3 class="h6 fw-bold mb-2"><i class="bi bi-images me-2 text-primary"></i>Ảnh minh chứng<?= $media ? ' (' . count($media) . ')' : '' ?></h3>
            <?php if (!$media): ?>
              <div class="gallery-empty"><i class="bi bi-image me-1"></i>Người gửi chưa đính kèm ảnh minh chứng.</div>
            <?php else: ?>
              <div class="report-gallery count-<?= min(count($media), 5) ?>" data-gallery>
                <?php foreach ($media as $i => $m): ?>
                  <a href="<?= e($m['src']) ?>" class="gallery-item<?= $i > 4 ? ' d-none' : '' ?>" target="_blank" rel="noopener"
                     data-type="<?= $m['video'] ? 'video' : 'image' ?>" data-name="<?= e($m['name']) ?>" aria-label="Phóng to ảnh <?= $i + 1 ?>">
                    <?php if ($m['video']): ?>
                      <video src="<?= e($m['src']) ?>#t=0.1" muted playsinline preload="metadata"></video>
                      <span class="play-icon"><i class="bi bi-play-fill"></i></span>
                    <?php else: ?>
                      <img src="<?= e($m['src']) ?>" alt="Ảnh minh chứng <?= $i + 1 ?>" loading="lazy">
                      <span class="zoom-hint"><i class="bi bi-arrows-fullscreen"></i></span>
                    <?php endif; ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white py-3">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử xử lý</h2>
          </div>
          <div class="card-body">
            <?php if (empty($r->history)): ?>
              <p class="text-muted small mb-0">Chưa có thay đổi trạng thái nào.</p>
            <?php else: ?>
              <ul class="officer-timeline list-unstyled mb-0">
                <?php foreach ($r->history as $h): ?>
                  <li>
                    <div class="fw-semibold small"><?= e(NotificationModel::statusLabel($h->new_status)) ?></div>
                    <div class="text-muted x-small"><?= e(date('H:i d/m/Y', strtotime($h->changed_at))) ?><?= !empty($h->changer_name) ? ' · ' . e($h->changer_name) : '' ?></div>
                    <?php if (!empty($h->note)): ?>
                      <div class="text-secondary small mt-1 bg-light p-2 rounded"><?= e($h->note) ?></div>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white py-3">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-chat-left-dots me-2 text-primary"></i>Trao đổi &amp; ghi chú (<?= count($r->comments ?? []) ?>)</h2>
          </div>
          <div class="card-body">
            <?php if (empty($r->comments)): ?>
              <p class="text-muted small mb-0">Chưa có bình luận hay ghi chú nào.</p>
            <?php else: ?>
              <div class="d-flex flex-column gap-2">
                <?php foreach ($r->comments as $cm): ?>
                  <div class="p-3 rounded-3 <?= $cm->is_internal ? 'officer-note-internal' : 'bg-light' ?>">
                    <div class="d-flex flex-wrap justify-content-between gap-1 mb-1">
                      <span class="fw-bold small">
                        <?= e($cm->full_name ?: 'Ẩn danh') ?>
                        <?php if ($cm->is_internal): ?><span class="badge bg-warning text-dark ms-1"><i class="bi bi-lock-fill me-1"></i>Nội bộ</span><?php endif; ?>
                      </span>
                      <span class="text-muted x-small"><?= e(date('H:i d/m/Y', strtotime($cm->created_at))) ?></span>
                    </div>
                    <p class="mb-0 small text-secondary" style="white-space: pre-line;"><?= e($cm->content) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?= asset('js/gallery.js') ?>" defer></script>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
