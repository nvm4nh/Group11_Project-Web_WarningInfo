<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section py-4 bg-light border-bottom">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-shield-exclamation text-primary me-2"></i>Danh sách cảnh báo & Sự cố</h1>
        <p class="text-muted mb-0">Theo dõi toàn bộ các vấn đề môi trường được người dân ghi nhận theo thời gian thực.</p>
      </div>
      <a href="<?= url('report/create') ?>" class="btn btn-warning fw-semibold shadow-sm">
        <i class="bi bi-megaphone me-1"></i> Gửi báo cáo mới
      </a>
    </div>

    <!-- Bộ lọc tìm kiếm -->
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-body p-3">
        <form method="get" action="<?= url('report') ?>" class="row g-2 align-items-center">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
              <input type="text" name="q" class="form-control" placeholder="Tìm theo từ khoá, địa chỉ..." 
                     value="<?= e($data['filters']['keyword'] ?? '') ?>">
            </div>
          </div>
          <div class="col-md-3">
            <select name="category_id" class="form-select">
              <option value="">Tất cả danh mục</option>
              <?php foreach ($data['categories'] as $c): ?>
                <option value="<?= $c->id ?>" <?= ($data['filters']['category_id'] ?? '') == $c->id ? 'selected' : '' ?>>
                  <?= e($c->name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select name="priority" class="form-select">
              <option value="">Mọi mức độ</option>
              <option value="critical" <?= ($data['filters']['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Nghiêm trọng</option>
              <option value="high" <?= ($data['filters']['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Cao</option>
              <option value="medium" <?= ($data['filters']['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Trung bình</option>
              <option value="low" <?= ($data['filters']['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Thấp</option>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Lọc</button>
            <a href="<?= url('report') ?>" class="btn btn-outline-secondary" title="Đặt lại"><i class="bi bi-arrow-counterclockwise"></i></a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<section class="section py-5">
  <div class="container">
    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($data['reports'])): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox text-muted display-1"></i>
        <h4 class="mt-3 text-muted">Không tìm thấy báo cáo nào</h4>
        <p class="text-muted">Thử thay đổi bộ lọc hoặc <a href="<?= url('report/create') ?>">gửi báo cáo đầu tiên</a>.</p>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($data['reports'] as $r): ?>
          <?php
            $sevClass = 'bg-warning text-dark';
            $sevLabel = 'Trung bình';
            if ($r->priority === 'critical') { $sevClass = 'bg-danger text-white'; $sevLabel = 'Nghiêm trọng'; }
            elseif ($r->priority === 'high') { $sevClass = 'bg-orange text-white'; $sevLabel = 'Cao'; }
            elseif ($r->priority === 'low')  { $sevClass = 'bg-success text-white'; $sevLabel = 'Thấp'; }

            $stBadge = 'bg-secondary';
            $stText  = 'Mới tiếp nhận';
            if ($r->status === 'verifying')   { $stBadge = 'bg-info text-dark'; $stText = 'Đang xác minh'; }
            elseif ($r->status === 'in_progress' || $r->status === 'assigned') { $stBadge = 'bg-primary'; $stText = 'Đang xử lý'; }
            elseif ($r->status === 'resolved') { $stBadge = 'bg-success'; $stText = 'Đã giải quyết'; }
            elseif ($r->status === 'rejected') { $stBadge = 'bg-dark'; $stText = 'Từ chối'; }
          ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition">
              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="badge <?= $sevClass ?>"><?= $sevLabel ?></span>
                  <span class="badge <?= $stBadge ?>"><?= $stText ?></span>
                </div>
                <h5 class="card-title fw-bold mb-2">
                  <a href="<?= url('report/detail/' . $r->id) ?>" class="text-decoration-none text-dark">
                    <?= e($r->title) ?>
                  </a>
                </h5>
                <p class="card-text text-muted small flex-grow-1">
                  <?= e(mb_substr($r->description, 0, 110)) ?>...
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
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
