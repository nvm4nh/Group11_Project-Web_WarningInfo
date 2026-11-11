<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/officer.css') ?>" rel="stylesheet">
<?php
require APPROOT . '/app/views/officer/_labels.php';
$tab    = $data['tab'] ?? 'active';
$items  = $data['assignments'] ?? [];
$counts = $data['counts'] ?? ['waiting' => 0, 'doing' => 0, 'done' => 0, 'urgent' => 0];
$tabs = [
  'active' => ['Đang thụ lý',   $counts['waiting'] + $counts['doing']],
  'done'   => ['Đã hoàn thành', $counts['done']],
  'all'    => ['Tất cả',        null],
];
?>

<section class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= url('') ?>">Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Bàn làm việc cán bộ</li>
      </ol>
    </nav>
    <h1><i class="bi bi-briefcase me-2"></i>Bàn làm việc cán bộ</h1>
    <p class="mb-0">Xin chào <strong class="text-white"><?= e($_SESSION['full_name'] ?? '') ?></strong>, đây là các sự cố bạn được phân công xử lý.</p>
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

    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="officer-stat stat-waiting"><i class="bi bi-inbox"></i><div><b><?= $counts['waiting'] ?></b><span>Chờ nhận việc</span></div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="officer-stat stat-doing"><i class="bi bi-hourglass-split"></i><div><b><?= $counts['doing'] ?></b><span>Đang xử lý</span></div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="officer-stat stat-urgent"><i class="bi bi-exclamation-triangle"></i><div><b><?= $counts['urgent'] ?></b><span>Khẩn cấp đang thụ lý</span></div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="officer-stat stat-done"><i class="bi bi-check2-circle"></i><div><b><?= $counts['done'] ?></b><span>Đã hoàn thành</span></div></div>
      </div>
    </div>

    <ul class="nav nav-pills mb-3 flex-nowrap overflow-auto">
      <?php foreach ($tabs as $key => [$label, $count]): ?>
        <li class="nav-item">
          <a class="nav-link<?= $tab === $key ? ' active' : '' ?>" href="<?= url('officer?tab=' . $key) ?>">
            <?= e($label) ?><?php if ($count !== null): ?> <span class="badge rounded-pill bg-light text-dark ms-1"><?= (int)$count ?></span><?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if (empty($items)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <i class="bi bi-clipboard-check display-6 text-muted d-block mb-2"></i>
          <p class="fw-semibold mb-1"><?= $tab === 'done' ? 'Chưa có sự cố nào hoàn thành' : 'Hiện không có sự cố nào cần xử lý' ?></p>
          <p class="text-muted small mb-0">Khi quản trị viên phân công, sự cố sẽ hiện ở đây và bạn nhận được thông báo ở biểu tượng chuông.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($items as $a): ?>
          <?php
            [$pText, $pCls] = $priorityBadge[$a->priority] ?? ['Trung bình', 'bg-warning text-dark'];
            [$aText, $aCls] = $assignmentBadge[$a->assignment_status] ?? [$a->assignment_status, 'bg-secondary'];
            $thumb = $a->thumbnail ? url(preg_replace('#^/?(public/)?#', '', $a->thumbnail)) : null;
          ?>
          <article class="task-card prio-<?= e($a->priority) ?>">
            <div class="task-thumb">
              <?php if ($thumb): ?>
                <img src="<?= e($thumb) ?>" alt="" loading="lazy">
              <?php else: ?>
                <i class="bi bi-image"></i>
              <?php endif; ?>
            </div>
            <div class="task-body">
              <div class="d-flex flex-wrap gap-1 mb-1">
                <span class="badge bg-light text-dark border font-monospace"><?= e($a->report_code) ?></span>
                <span class="badge <?= $pCls ?>"><?= e($pText) ?></span>
                <span class="badge <?= $statusBadge[$a->report_status] ?? 'bg-secondary' ?>"><?= e(NotificationModel::statusLabel($a->report_status)) ?></span>
              </div>
              <h2 class="task-title"><a href="<?= url('officer/report/' . (int)$a->report_id) ?>" class="stretched-link"><?= e($a->title) ?></a></h2>
              <div class="task-meta">
                <span><i class="bi bi-geo-alt"></i><?= e($a->address ?: 'Chưa có địa chỉ') ?></span>
                <span><i class="bi bi-tag"></i><?= e($a->category_name ?? 'Khác') ?></span>
                <span><i class="bi bi-clock"></i>Giao <?= e(mb_strtolower(timeAgo($a->age_seconds, $a->assigned_at))) ?><?= $a->assigner_name ? ' bởi ' . e($a->assigner_name) : '' ?></span>
                <?php if (!empty($a->deadline)): ?>
                  <span class="text-danger"><i class="bi bi-alarm"></i>Hạn: <?= e(date('H:i d/m/Y', strtotime($a->deadline))) ?></span>
                <?php endif; ?>
              </div>
              <?php if (!empty($a->assignment_note)): ?>
                <p class="task-note"><i class="bi bi-chat-left-quote me-1"></i><?= e($a->assignment_note) ?></p>
              <?php endif; ?>
            </div>
            <div class="task-side">
              <span class="badge <?= $aCls ?>"><?= e($aText) ?></span>
              <span class="btn btn-sm btn-primary task-go">Xem &amp; xử lý <i class="bi bi-arrow-right"></i></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
