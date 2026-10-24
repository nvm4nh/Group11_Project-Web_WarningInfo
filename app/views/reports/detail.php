<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/report-detail.css') ?>" rel="stylesheet">

<?php
$r = $data['report'];
$sevBadge = 'bg-warning text-dark';
$sevText = 'Trung bình';
if ($r->priority === 'critical') { $sevBadge = 'bg-danger text-white'; $sevText = 'Nghiêm trọng'; }
elseif ($r->priority === 'high') { $sevBadge = 'bg-orange text-white'; $sevText = 'Cao'; }
elseif ($r->priority === 'low')  { $sevBadge = 'bg-success text-white'; $sevText = 'Thấp'; }

// Nhãn tiếng Việt cho mọi trạng thái trong CSDL (dùng cho badge đầu trang + dòng thời gian xử lý)
$statusMap = [
  'new'            => ['Mới tiếp nhận',        'bg-secondary'],
  'received'       => ['Đã tiếp nhận',         'bg-secondary'],
  'need_more_info' => ['Cần bổ sung thông tin', 'bg-warning text-dark'],
  'verifying'      => ['Đang xác minh',        'bg-info text-dark'],
  'verified'       => ['Đã xác minh',          'bg-info text-dark'],
  'assigned'       => ['Đã phân công',         'bg-primary'],
  'in_progress'    => ['Đang xử lý',           'bg-primary'],
  'resolved'       => ['Đã giải quyết',        'bg-success'],
  'closed'         => ['Đã đóng',              'bg-dark'],
  'rejected'       => ['Từ chối',              'bg-dark'],
];
$statusLabel = function ($code) use ($statusMap) {
  return $statusMap[$code][0] ?? ucfirst(str_replace('_', ' ', (string)$code));
};
$stText  = $statusLabel($r->status);
$stBadge = $statusMap[$r->status][1] ?? 'bg-secondary';
?>

<section class="section py-4 bg-light border-bottom">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <a href="<?= url('report') ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        <h1 class="h3 fw-bold mt-2 mb-1"><?= e($r->title) ?></h1>
        <div class="d-flex flex-wrap gap-2 align-items-center small text-muted">
          <span class="badge bg-light text-dark border">Mã: <?= e($r->report_code) ?></span>
          <span><i class="bi bi-clock me-1"></i><?= date('H:i - d/m/Y', strtotime($r->incident_time)) ?></span>
          <span><i class="bi bi-tag me-1"></i><?= e($r->category_name ?? 'Môi trường') ?></span>
        </div>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge <?= $sevBadge ?> fs-6 px-3 py-2"><?= $sevText ?></span>
        <span class="badge <?= $stBadge ?> fs-6 px-3 py-2"><?= $stText ?></span>
      </div>
    </div>
  </div>
</section>

<section class="section py-5">
  <div class="container">
    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show">
        <?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Cột chính: Nội dung & Bình luận -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-file-text me-2 text-primary"></i>Mô tả chi tiết sự cố</h5>
            <p class="lead fs-6 text-secondary mb-4" style="white-space: pre-line;">
              <?= e($r->description) ?>
            </p>

            <!-- Ảnh minh chứng (TV4). Dữ liệu $r->images do ReportModel::getById() nạp (phần của TV3):
                 mỗi phần tử có file_path, mime_type, original_file_name -->
            <?php
              $media = [];
              foreach (($r->images ?? []) as $img) {
                $img  = (object)$img;
                $path = trim((string)($img->file_path ?? ''));
                if ($path === '') continue;
                $src = preg_match('#^https?://#i', $path) ? $path : url(preg_replace('#^/?(public/)?#', '', $path));
                $media[] = [
                  'src'   => $src,
                  'video' => strpos((string)($img->mime_type ?? ''), 'video/') === 0,
                  'name'  => (string)($img->original_file_name ?? ''),
                ];
              }
              $mediaCount = count($media);
            ?>
            <div class="mb-4">
              <h6 class="fw-bold mb-2"><i class="bi bi-images me-2 text-primary"></i>Ảnh minh chứng<?= $mediaCount ? ' (' . $mediaCount . ')' : '' ?></h6>
              <?php if ($mediaCount === 0): ?>
                <div class="gallery-empty"><i class="bi bi-image me-1"></i>Người gửi chưa đính kèm ảnh minh chứng cho sự cố này.</div>
              <?php else: ?>
                <div class="report-gallery count-<?= min($mediaCount, 5) ?>" data-gallery>
                  <?php foreach ($media as $i => $m): ?>
                    <a href="<?= e($m['src']) ?>" class="gallery-item<?= $i > 4 ? ' d-none' : '' ?>" target="_blank" rel="noopener"
                       data-type="<?= $m['video'] ? 'video' : 'image' ?>" data-name="<?= e($m['name']) ?>"
                       aria-label="Phóng to <?= $m['video'] ? 'video' : 'ảnh' ?> minh chứng <?= $i + 1 ?>">
                      <?php if ($m['video']): ?>
                        <video src="<?= e($m['src']) ?>#t=0.1" muted playsinline preload="metadata"></video>
                        <span class="play-icon"><i class="bi bi-play-fill"></i></span>
                      <?php else: ?>
                        <img src="<?= e($m['src']) ?>" alt="Ảnh minh chứng <?= $i + 1 ?>: <?= e($r->title) ?>" loading="lazy">
                        <span class="zoom-hint"><i class="bi bi-arrows-fullscreen"></i></span>
                      <?php endif; ?>
                      <?php if ($i === 4 && $mediaCount > 5): ?>
                        <span class="more">+<?= $mediaCount - 5 ?></span>
                      <?php endif; ?>
                    </a>
                  <?php endforeach; ?>
                </div>
                <div class="form-text"><i class="bi bi-hand-index me-1"></i>Bấm vào ảnh để phóng to, dùng phím ← → hoặc vuốt để xem ảnh khác.</div>
              <?php endif; ?>
            </div>

            <div class="p-3 bg-light rounded-3 mb-4">
              <div class="row g-2 small">
                <div class="col-sm-6">
                  <span class="text-muted">Địa chỉ hiện trường:</span>
                  <div class="fw-semibold"><i class="bi bi-geo-alt text-danger me-1"></i><?= e($r->address ?: 'Đang cập nhật') ?></div>
                </div>
                <?php if ($r->latitude && $r->longitude): ?>
                  <div class="col-sm-6">
                    <span class="text-muted">Toạ độ GPS:</span>
                    <div class="fw-semibold"><i class="bi bi-crosshair text-primary me-1"></i><?= e($r->latitude) ?>, <?= e($r->longitude) ?></div>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Nút tương tác Upvote cộng đồng (Gọi AJAX) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
              <button class="btn btn-outline-primary" id="btnUpvote" data-id="<?= $r->id ?>">
                <i class="bi bi-hand-thumbs-up me-1"></i> Tôi cũng thấy sự cố này (<span id="upvoteCount"><?= (int)$r->upvote_count ?></span>)
              </button>
              <span class="text-muted small">Người gửi: <?= e($r->author_name ?: 'Cộng đồng ẩn danh') ?></span>
            </div>
          </div>
        </div>

        <!-- Khung bình luận & Thảo luận -->
        <?php
          // Ghi chú nội bộ của cán bộ (is_internal = 1) chỉ cán bộ / quản trị viên thấy
          $visibleComments = array_values(array_filter($r->comments ?? [], function ($cm) {
            return !$cm->is_internal || isOfficer();
          }));
        ?>
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0"><i class="bi bi-chat-left-dots me-2 text-primary"></i>Phản hồi & Thảo luận (<?= count($visibleComments) ?>)</h5>
          </div>
          <div class="card-body p-4">
            <?php if (empty($visibleComments)): ?>
              <p class="text-muted text-center py-3">Chưa có bình luận nào. Hãy gửi phản hồi đầu tiên!</p>
            <?php else: ?>
              <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($visibleComments as $cm): ?>
                  <div class="p-3 rounded-3 <?= $cm->is_internal ? 'bg-warning-subtle border-warning' : 'bg-light' ?>">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                      <span class="fw-bold small">
                        <?= e($cm->full_name ?: 'Ẩn danh') ?>
                        <?php if ($cm->role === 'admin' || $cm->role === 'officer'): ?>
                          <span class="badge bg-primary ms-1">Cán bộ xử lý</span>
                        <?php endif; ?>
                        <?php if ($cm->is_internal): ?>
                          <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lock-fill me-1"></i>Nội bộ</span>
                        <?php endif; ?>
                      </span>
                      <span class="text-muted x-small"><?= date('H:i d/m/Y', strtotime($cm->created_at)) ?></span>
                    </div>
                    <p class="mb-0 small text-secondary"><?= e($cm->content) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Form bình luận -->
            <form method="post" action="<?= url('report/comment/' . $r->id) ?>">
              <?= csrf_field() ?>
              <div class="mb-3">
                <textarea name="content" class="form-control" rows="3" placeholder="Nhập ý kiến xác nhận hoặc bổ sung thông tin hiện trường..." required></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-sm px-4">Gửi phản hồi</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Cột phụ: Lịch sử tiến trình xử lý (Workflow) -->
      <div class="col-lg-4">
        <?php if (!empty($data['use_map'])): /* Bản đồ vị trí hiện trường – TV1 (Milestone 3) */ ?>
          <?php
            $mapPoint = [
              'lat'         => (float)$r->latitude,
              'lng'         => (float)$r->longitude,
              'title'       => $r->title,
              'category_id' => (int)$r->category_id,
              'level'       => ReportModel::mapLevel($r->priority, $r->status),
            ];
          ?>
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
              <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2 text-primary"></i>Vị trí hiện trường</h6>
            </div>
            <div class="card-body p-2">
              <div class="wi-map is-mini" data-wi-map data-no-scroll-zoom
                   data-point="<?= e(json_encode($mapPoint, JSON_UNESCAPED_UNICODE)) ?>"
                   role="region" aria-label="Bản đồ vị trí sự cố"></div>
              <div class="d-flex gap-2 p-2 pb-1">
                <a href="<?= url('report/map?focus=' . (int)$r->id) ?>" class="btn btn-outline-primary btn-sm flex-fill"><i class="bi bi-map me-1"></i>Xem bản đồ lớn</a>
                <a href="https://www.openstreetmap.org/directions?route=%3B<?= (float)$r->latitude ?>%2C<?= (float)$r->longitude ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm flex-fill"><i class="bi bi-sign-turn-right me-1"></i>Chỉ đường</a>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Tiến trình xử lý</h6>
          </div>
          <div class="card-body p-4">
            <?php if (empty($r->history)): ?>
              <p class="text-muted small mb-0">Hệ thống đang ghi nhận thông tin ban đầu.</p>
            <?php else: ?>
              <ul class="list-unstyled position-relative border-start border-2 border-primary ms-2 ps-3 mb-0">
                <?php foreach ($r->history as $h): ?>
                  <li class="mb-3 position-relative">
                    <span class="position-absolute translate-middle-x rounded-circle bg-primary" 
                          style="left: -19px; top: 6px; width: 10px; height: 10px;"></span>
                    <div class="fw-semibold small"><?= e($statusLabel($h->new_status)) ?></div>
                    <div class="text-muted x-small"><?= date('H:i d/m/Y', strtotime($h->changed_at)) ?></div>
                    <?php if ($h->note): ?>
                      <div class="text-secondary small mt-1 bg-light p-2 rounded"><?= e($h->note) ?></div>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.getElementById('btnUpvote')?.addEventListener('click', function() {
  const reportId = this.dataset.id;
  fetch('<?= url("report/upvote/") ?>' + reportId, { method: 'POST', headers: { 'X-CSRF-Token': '<?= e(csrf_token()) ?>' } })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        document.getElementById('upvoteCount').textContent = data.count;
      } else {
        alert(data.message);
      }
    });
});
</script>

<script src="<?= asset('js/gallery.js') ?>" defer></script>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
