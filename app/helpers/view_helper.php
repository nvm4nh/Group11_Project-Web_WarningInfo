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
