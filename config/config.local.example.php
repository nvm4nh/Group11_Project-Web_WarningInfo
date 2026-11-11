<?php
/**
 * MẪU cấu hình cho hosting (cPanel) – Milestone 4
 *
 * Cách dùng:
 *   1. Copy file này thành config/config.local.php (cùng thư mục).
 *   2. Điền thông tin CSDL mà cPanel cấp (mục "MySQL Databases"). Trên cPanel, tên CSDL và tên user
 *      thường có tiền tố tài khoản, ví dụ: abcd1234_warninginfo.
 *   3. Không commit config.local.php lên GitHub (đã có trong .gitignore).
 *
 * Dòng nào không khai báo sẽ dùng giá trị mặc định trong config/config.php.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'cpaneluser_warninginfo');
define('DB_PASS', 'mat-khau-csdl-cua-hosting');
define('DB_NAME', 'cpaneluser_warninginfo');

// Tuỳ chọn: cố định địa chỉ web (mặc định tự nhận theo tên miền đang truy cập)
// define('URLROOT', 'https://warninginfo.example.vn');

// Hiện chi tiết lỗi kỹ thuật trên trang lỗi 500: LUÔN để false trên hosting
define('APP_DEBUG', false);
