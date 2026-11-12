<?php
/**
 * Router cho máy chủ có sẵn của PHP (`php -S`) – CHỈ dùng khi phát triển, ví dụ với extension
 * "PHP Server" của VS Code (cấu hình sẵn ở .vscode/settings.json):
 *
 *     php -S localhost:3000 -t public public/router.php
 *
 * Làm thay 2 file .htaccess (php -S không đọc .htaccess):
 *   - file tĩnh có thật trong public/ (CSS, JS, ảnh, uploads) → để máy chủ trả thẳng
 *   - còn lại → public/index.php?url=<đường dẫn>, giống RewriteRule trên Apache
 * Trên Apache / hosting, file này không có tác dụng (trả 404 nếu ai đó gọi thẳng /router.php).
 */
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$file = __DIR__ . $path;

// Chặn file ẩn (.htaccess, .user.ini...) giống public/.htaccess
if (preg_match('#/\.#', $path)) {
    http_response_code(404);
    exit;
}

// File tĩnh: máy chủ PHP tự phục vụ (đúng Content-Type). Không chạy file .php khác ngoài index.php
if ($path !== '/' && is_file($file) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') {
    return false;
}

// Header bảo mật mà public/.htaccess đặt trên Apache
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$url = trim($path, '/');
if ($url !== '' && $url !== 'index.php') {
    $_GET['url'] = $url;
}
// config.php tự nhận URLROOT từ SCRIPT_NAME: đặt về /index.php để URL gốc là http://localhost:3000
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF']    = '/index.php';

chdir(__DIR__);   // index.php dùng đường dẫn tương đối '../config/...'
require __DIR__ . '/index.php';
