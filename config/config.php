<?php
/**
 * Cấu hình hệ thống WarningInfo
 *
 * Máy cá nhân (XAMPP): KHÔNG cần sửa file này.
 * Hosting (cPanel): copy config/config.local.example.php thành config/config.local.php rồi điền
 *   thông tin CSDL của hosting. File config.local.php không đưa lên GitHub (.gitignore) nên mật khẩu
 *   CSDL thật không bị lộ, và lần cập nhật code sau cũng không ghi đè cấu hình của hosting.
 */

// 1. Cấu hình riêng của từng máy / hosting (nếu có) – được ưu tiên hơn các giá trị mặc định bên dưới
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// 2. Giá trị mặc định cho máy chạy XAMPP
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('DB_NAME') || define('DB_NAME', 'warninginfo_db');
defined('APPROOT') || define('APPROOT', dirname(dirname(__FILE__)));
defined('SITENAME') || define('SITENAME', 'WarningInfo');

// 3. URLROOT: tự nhận theo tên miền + thư mục đang chạy, nên cùng một bộ code chạy được ở
//    http://localhost/Group11_Project-Web_WarningInfo (XAMPP) lẫn https://ten-mien.vn (hosting)
//    mà không phải sửa. Muốn cố định thì khai báo URLROOT trong config.local.php.
if (!defined('URLROOT')) {
    define('URLROOT', wi_detect_base_url());
}

// Giờ Việt Nam cho date()/strtotime() — tránh lệch giờ khi php.ini để UTC hoặc Europe/Berlin (mặc định XAMPP)
date_default_timezone_set('Asia/Ho_Chi_Minh');

/** Request hiện tại có chạy qua HTTPS không (kể cả hosting đặt sau proxy / Cloudflare) */
function wi_is_https() {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    if ((string)($_SERVER['SERVER_PORT'] ?? '') === '443') return true;
    return strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

/**
 * Địa chỉ gốc của ứng dụng, ví dụ http://localhost/Group11_Project-Web_WarningInfo hoặc https://warninginfo.vn
 * Dựa vào SCRIPT_NAME (luôn là .../public/index.php) nên đúng cả khi web đặt ở gốc domain, thư mục con,
 * hay khi document root trỏ thẳng vào public/.
 */
function wi_detect_base_url() {
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    // Chỉ nhận tên miền / IP hợp lệ (chặn Host header chứa ký tự lạ chèn vào link)
    if (!preg_match('/^[a-z0-9.\-]+(:\d{1,5})?$/i', $host) && !preg_match('/^\[[0-9a-f:.]+\](:\d{1,5})?$/i', $host)) {
        $host = 'localhost';
    }
    $dir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
    $dir = preg_replace('#/public$#', '', rtrim($dir, '/'));
    return (wi_is_https() ? 'https' : 'http') . '://' . $host . $dir;
}
