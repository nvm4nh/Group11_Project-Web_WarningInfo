<?php
$totalReports = number_format($data['stats']['total_reports'] ?? 0, 0, ',', '.');
$processing   = number_format($data['stats']['processing'] ?? 0, 0, ',', '.');
$resolved     = number_format($data['stats']['resolved'] ?? 0, 0, ',', '.');
$totalUsers   = number_format($data['stats']['total_users'] ?? 0, 0, ',', '.');
?>
<!-- ========== THỐNG KÊ ========== -->
<div class="container stats-wrap">
  <div class="row g-3">
    <div class="col-6 col-lg-3">
      <div class="card-surface stat-card">
        <span class="icon-circle bg-primary-soft text-primary"><i class="bi bi-megaphone"></i></span>
        <div><div class="num"><?= $totalReports ?></div><div class="label">Báo cáo đã gửi</div></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card-surface stat-card">
        <span class="icon-circle" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-gear"></i></span>
        <div><div class="num"><?= $processing ?></div><div class="label">Đang xử lý</div></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card-surface stat-card">
        <span class="icon-circle" style="background:#dcfce7;color:#16a34a"><i class="bi bi-check2-circle"></i></span>
        <div><div class="num"><?= $resolved ?></div><div class="label">Đã giải quyết</div></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card-surface stat-card">
        <span class="icon-circle" style="background:#fff7ed;color:#ea580c"><i class="bi bi-people"></i></span>
        <div><div class="num"><?= $totalUsers ?></div><div class="label">Thành viên</div></div>
      </div>
    </div>
  </div>
</div>
