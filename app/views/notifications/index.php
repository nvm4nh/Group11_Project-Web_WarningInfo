<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/notifications.css') ?>" rel="stylesheet">
<?php
$items  = $data['notifications'] ?? [];
$filter = $data['filter'] ?? 'all';
$unread = (int)($data['unreadCount'] ?? 0);
$total  = (int)($data['totalCount'] ?? 0);

// Biểu tượng + màu theo loại thông báo
$typeStyle = [
  'report_status' => ['bi-arrow-repeat',  'notif-ic-status',  'Cập nhật trạng thái'],
  'assignment'    => ['bi-person-check',  'notif-ic-assign',  'Phân công xử lý'],
  'officer_update'=> ['bi-clipboard-check','notif-ic-assign', 'Cán bộ cập nhật'],
  'general'       => ['bi-bell',          'notif-ic-general', 'Thông báo chung'],
];
?>

<section class="section py-4 bg-light border-bottom">
  <div class="container" style="max-width: 860px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-bell text-primary me-2"></i>Thông báo của tôi</h1>
        <p class="text-muted mb-0">
          <?= $unread > 0 ? 'Bạn có <strong>' . $unread . '</strong> thông báo chưa đọc.' : 'Bạn đã đọc hết thông báo.' ?>
        </p>
      </div>
      <?php if ($unread > 0): ?>
        <form method="post" action="<?= url('notification/readAll') ?>" class="m-0">
          <?= csrf_field() ?>
          <input type="hidden" name="filter" value="<?= e($filter) ?>">
          <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-check2-all me-1"></i> Đánh dấu tất cả đã đọc
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section py-4">
  <div class="container" style="max-width: 860px;">
    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show">
        <?= e($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
      </div>
    <?php endif; ?>

    <ul class="nav nav-pills mb-3">
      <li class="nav-item">
        <a class="nav-link<?= $filter === 'all' ? ' active' : '' ?>" href="<?= url('notification') ?>">
          Tất cả <span class="badge rounded-pill bg-secondary-subtle text-secondary ms-1"><?= $total ?></span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?= $filter === 'unread' ? ' active' : '' ?>" href="<?= url('notification?filter=unread') ?>">
          Chưa đọc <span class="badge rounded-pill bg-danger ms-1"><?= $unread ?></span>
        </a>
      </li>
    </ul>

    <?php if (empty($items)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <i class="bi bi-bell-slash display-6 text-muted d-block mb-2"></i>
          <p class="fw-semibold mb-1"><?= $filter === 'unread' ? 'Không có thông báo chưa đọc' : 'Chưa có thông báo nào' ?></p>
          <p class="text-muted small mb-0">Khi báo cáo của bạn được cập nhật hoặc bạn được giao xử lý sự cố, thông báo sẽ hiện ở đây.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="card border-0 shadow-sm overflow-hidden">
        <ul class="list-unstyled mb-0 notif-list">
          <?php foreach ($items as $n): ?>
            <?php [$icon, $icCls, $typeText] = $typeStyle[$n->notification_type] ?? $typeStyle['general']; ?>
            <li class="notif-item<?= $n->is_read ? '' : ' is-unread' ?>">
              <span class="notif-ic <?= $icCls ?>" aria-hidden="true"><i class="bi <?= $icon ?>"></i></span>
              <div class="notif-body">
                <a href="<?= url('notification/open/' . (int)$n->id) ?>" class="notif-title stretched-link">
                  <?= e($n->title) ?>
                </a>
                <p class="notif-msg"><?= e($n->message) ?></p>
                <div class="notif-meta">
                  <span title="<?= e(date('H:i d/m/Y', strtotime($n->created_at))) ?>">
                    <i class="bi bi-clock me-1"></i><?= e(timeAgo($n->age_seconds, $n->created_at)) ?>
                  </span>
                  <span><i class="bi bi-tag me-1"></i><?= e($typeText) ?></span>
                  <?php if (!empty($n->report_code)): ?>
                    <span><i class="bi bi-hash"></i><?= e($n->report_code) ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <?php if (!$n->is_read): ?>
                <form method="post" action="<?= url('notification/read/' . (int)$n->id) ?>" class="notif-action">
                  <?= csrf_field() ?>
                  <input type="hidden" name="filter" value="<?= e($filter) ?>">
                  <button type="submit" class="btn btn-sm btn-light" title="Đánh dấu đã đọc" aria-label="Đánh dấu đã đọc">
                    <i class="bi bi-check2"></i>
                  </button>
                </form>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <p class="text-muted small mt-2 mb-0">Hiển thị tối đa 100 thông báo gần nhất.</p>
    <?php endif; ?>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
