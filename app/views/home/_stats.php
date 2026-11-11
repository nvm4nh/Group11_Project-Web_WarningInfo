<?php
/* Trang chủ – dải số liệu: 4 ô trong một dải liền, số chạy tăng dần khi cuộn tới (main.js – [data-count]) */
$statCells = [
  ['bi-megaphone',     'background:var(--wi-primary-soft);color:var(--wi-primary)', (int)($data['stats']['total_reports'] ?? 0), 'Báo cáo đã gửi'],
  ['bi-gear',          'background:#ede9fe;color:#6d28d9',                          (int)($data['stats']['processing'] ?? 0),    'Đang xử lý'],
  ['bi-check2-circle', 'background:#dcfce7;color:#15803d',                          (int)($data['stats']['resolved'] ?? 0),      'Đã giải quyết'],
  ['bi-people',        'background:#fff3d6;color:#8a5200',                          (int)($data['stats']['total_users'] ?? 0),   'Thành viên'],
];
?>
<!-- ========== THỐNG KÊ ========== -->
<div class="container stats-wrap">
  <div class="stats-band">
    <?php foreach ($statCells as [$icon, $tint, $num, $label]): ?>
      <div class="stat-card">
        <span class="icon-circle" style="<?= $tint ?>"><i class="bi <?= $icon ?>"></i></span>
        <div><div class="num" data-count="<?= $num ?>"><?= number_format($num, 0, ',', '.') ?></div><div class="label"><?= $label ?></div></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
