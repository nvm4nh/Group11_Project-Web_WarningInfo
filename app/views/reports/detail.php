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

            <!-- Nút xác nhận sự cố (Upvote) – AJAX POST report/api_upvote/{id}, xử lý ở ajax-interaction.js -->
            <?php $voted = !empty($data['has_upvoted']); ?>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
              <button type="button" class="btn btn-upvote <?= $voted ? 'btn-primary is-voted' : 'btn-outline-primary' ?>" id="btnUpvote"
                      data-endpoint="<?= url('report/api_upvote/' . (int)$r->id) ?>"
                      <?php if (!isLoggedIn()): ?>data-login-url="<?= url('auth/login') ?>"<?php endif; ?>
                      aria-pressed="<?= $voted ? 'true' : 'false' ?>">
                <i class="bi <?= $voted ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' ?> me-1"></i>
                <span class="upvote-text"><?= $voted ? 'Bạn đã xác nhận' : 'Tôi cũng thấy sự cố này' ?></span>
                (<span class="upvote-count" id="upvoteCount"><?= (int)$r->upvote_count ?></span>)
              </button>
              <span class="text-muted small">Người gửi: <?= e($r->author_name ?: 'Cộng đồng ẩn danh') ?></span>
            </div>
          </div>
        </div>

        <!-- Khung bình luận (TV5 – M3): trang đầu render sẵn, "Tải thêm" và gửi bình luận chạy bằng AJAX (ajax-interaction.js).
             Khi tắt JS: form vẫn gửi bình thường tới report/comment/{id}. -->
        <?php
          $comments     = $data['comments'] ?? [];
          $commentTotal = (int)($data['comment_total'] ?? 0);
          $commentLimit = (int)($data['comment_limit'] ?? 5);
        ?>
        <?php if (!empty($data['internal_notes'])): /* Ghi chú nội bộ – chỉ cán bộ / quản trị viên thấy */ ?>
          <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
            <div class="card-body p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-lock-fill text-warning me-2"></i>Ghi chú nội bộ của cán bộ (<?= count($data['internal_notes']) ?>)</h6>
              <div class="d-flex flex-column gap-2">
                <?php foreach ($data['internal_notes'] as $cm): ?>
                  <div class="p-3 rounded-3 bg-warning-subtle">
                    <div class="d-flex flex-wrap justify-content-between gap-1 mb-1">
                      <span class="fw-bold small"><?= e($cm->full_name ?: 'Cán bộ') ?></span>
                      <span class="text-muted x-small"><?= date('H:i d/m/Y', strtotime($cm->created_at)) ?></span>
                    </div>
                    <p class="mb-0 small text-secondary" style="white-space: pre-line;"><?= e($cm->content) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm" id="commentBox"
             data-endpoint="<?= url('report/api_comments/' . (int)$r->id) ?>"
             data-add-endpoint="<?= url('report/api_add_comment') ?>"
             data-report-id="<?= (int)$r->id ?>" data-limit="<?= $commentLimit ?>" data-page="1">
          <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0"><i class="bi bi-chat-left-dots me-2 text-primary"></i>Phản hồi & Thảo luận (<span id="commentTotal"><?= $commentTotal ?></span>)</h5>
          </div>
          <div class="card-body p-4">
            <!-- Form bình luận đặt trên cùng: bình luận mới nhất hiện ngay bên dưới -->
            <?php if (isLoggedIn()): ?>
              <form method="post" action="<?= url('report/comment/' . $r->id) ?>" id="commentForm" class="mb-4">
                <?= csrf_field() ?>
                <div class="mb-2">
                  <label for="commentContent" class="visually-hidden">Nội dung bình luận</label>
                  <textarea name="content" id="commentContent" class="form-control" rows="3" maxlength="5000"
                            placeholder="Nhập ý kiến xác nhận hoặc bổ sung thông tin hiện trường..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                  <span class="spinner-border spinner-border-sm me-1 d-none" aria-hidden="true"></span>Gửi phản hồi
                </button>
              </form>
            <?php else: ?>
              <div class="alert alert-light border small mb-4">
                <i class="bi bi-person-lock me-1"></i>
                <a href="<?= url('auth/login') ?>">Đăng nhập</a> để gửi phản hồi về sự cố này.
              </div>
            <?php endif; ?>

            <p class="text-muted text-center py-3 mb-0<?= $commentTotal > 0 ? ' d-none' : '' ?>" id="commentEmpty">Chưa có bình luận nào. Hãy gửi phản hồi đầu tiên!</p>
            <div class="d-flex flex-column gap-3" id="commentList" aria-live="polite">
              <?php foreach ($comments as $cm): ?>
                <div class="comment-item p-3 rounded-3 bg-light" data-id="<?= (int)$cm->id ?>">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                    <span class="fw-bold small">
                      <?= e($cm->user_name ?: 'Ẩn danh') ?>
                      <?php if (in_array($cm->user_role, ['admin', 'officer'], true)): ?>
                        <span class="badge bg-primary ms-1">Cán bộ xử lý</span>
                      <?php endif; ?>
                    </span>
                    <span class="text-muted x-small"><?= date('H:i d/m/Y', strtotime($cm->created_at)) ?></span>
                  </div>
                  <p class="mb-0 small text-secondary" style="white-space: pre-line;"><?= e($cm->content) ?></p>
                </div>
              <?php endforeach; ?>
            </div>

            <?php if ($commentTotal > count($comments)): ?>
              <div class="text-center mt-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLoadMoreComments">
                  <span class="spinner-border spinner-border-sm me-1 d-none" aria-hidden="true"></span>
                  Tải thêm bình luận (<span data-remaining><?= $commentTotal - count($comments) ?></span>)
                </button>
              </div>
            <?php endif; ?>
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

<script src="<?= asset('js/ajax-interaction.js') ?>" defer></script>
<script src="<?= asset('js/gallery.js') ?>" defer></script>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
