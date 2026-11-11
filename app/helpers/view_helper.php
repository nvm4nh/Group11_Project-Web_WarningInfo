<?php
/**
 * Hàm tiện ích dùng chung cho View & Controller (TV4 – Lê Trọng Hiếu)
 * Được nạp trong public/index.php.
 */

/* ---------- Bảo mật & đường dẫn ---------- */

// In dữ liệu ra HTML an toàn (chống XSS). LUÔN dùng khi in dữ liệu người dùng nhập.
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// url('report/detail/5') => http://localhost/DoAnWeb_MVC/report/detail/5
function url($path = '') {
    return rtrim(URLROOT, '/') . '/' . ltrim($path, '/');
}

// asset('css/style.css') => http://localhost/DoAnWeb_MVC/assets/css/style.css
// (.htaccess ở thư mục gốc tự chuyển vào public/, nên KHÔNG ghi /public/ trong link)
function asset($path) {
    return rtrim(URLROOT, '/') . '/assets/' . ltrim($path, '/');
}

// Tạo link giữ nguyên query string: queryUrl('report', ['q' => 'rác', 'page' => 2])
function queryUrl($path, array $params = []) {
    $params = array_filter($params, function ($v) { return $v !== null && $v !== '' && $v !== []; });
    $qs = http_build_query($params);
    return url($path) . ($qs ? '?' . $qs : '');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

/* ---------- Thời gian tương đối (Milestone 3 – hộp thông báo) ---------- */

// timeAgo(90) => "1 phút trước". Nhận số giây đã trôi qua (tính sẵn trong SQL) và ngày gốc để hiện khi đã lâu.
function timeAgo($seconds, $datetime = null) {
    $s = max(0, (int)$seconds);
    if ($s < 60)        return 'Vừa xong';
    if ($s < 3600)      return floor($s / 60) . ' phút trước';
    if ($s < 86400)     return floor($s / 3600) . ' giờ trước';
    if ($s < 172800)    return 'Hôm qua';
    if ($s < 7 * 86400) return floor($s / 86400) . ' ngày trước';
    return $datetime ? date('d/m/Y', strtotime($datetime)) : floor($s / 86400) . ' ngày trước';
}

/* ---------- Biểu tượng / màu theo danh mục & mức độ (Milestone 4 – trang chủ) ---------- */

// categoryVisual(2) => ['bi-droplet-half', 'cat-water', 'water.svg']; danh mục admin tự thêm dùng kiểu "khác"
function categoryVisual($categoryId) {
    $map = [
        1 => ['bi-trash3',              'cat-waste',  'waste.svg'],
        2 => ['bi-droplet-half',        'cat-water',  'water.svg'],
        3 => ['bi-cloud-haze2',         'cat-air',    'air.svg'],
        4 => ['bi-cloud-lightning-rain','cat-flood',  'flood.svg'],
        5 => ['bi-tree',                'cat-forest', 'forest.svg'],
        6 => ['bi-volume-up',           'cat-noise',  'noise.svg'],
        7 => ['bi-exclamation-octagon', 'cat-chem',   'chem.svg'],
    ];
    return $map[(int)$categoryId] ?? ['bi-exclamation-circle', 'cat-other', 'other.svg'];
}

// severityVisual('high') => ['Cao', 'sev-high', 'bi-exclamation-triangle-fill'] (huy hiệu .badge-severity ở trang chủ)
function severityVisual($priority) {
    $map = [
        'critical' => ['Nghiêm trọng', 'sev-critical', 'bi-exclamation-octagon-fill'],
        'high'     => ['Cao',          'sev-high',     'bi-exclamation-triangle-fill'],
        'medium'   => ['Trung bình',   'sev-medium',   'bi-exclamation-circle-fill'],
        'low'      => ['Thấp',         'sev-low',      'bi-info-circle-fill'],
    ];
    return $map[$priority] ?? $map['medium'];
}

// Đường dẫn ảnh đã upload (file_path trong CSDL có thể có hoặc không có "public/" ở đầu)
function uploadUrl($path) {
    $path = trim((string)$path);
    if ($path === '') return '';
    return preg_match('#^https?://#i', $path) ? $path : url(preg_replace('#^/?(public/)?#', '', $path));
}

