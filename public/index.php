<?php
require_once '../config/config.php';
// Cookie phiên an toàn (Milestone 4): JS không đọc được (HttpOnly), không gửi kèm request từ trang lạ (SameSite),
// chỉ gửi qua HTTPS khi web chạy HTTPS (Secure)
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => wi_is_https()]);
ini_set('session.use_strict_mode', '1');
session_start();
require_once '../core/App.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';
require_once '../app/helpers/view_helper.php';
require_once '../app/helpers/auth_helper.php';
require_once '../app/helpers/csrf_helper.php';
require_once '../app/helpers/error_helper.php';

// Lỗi chưa xử lý / lỗi Fatal → trang 500 thân thiện, ghi log kèm mã tra cứu (TV4 – Milestone 4)
register_error_pages();

// Ô nhập bị gửi giả dạng mảng (title[]=...) → đưa về chuỗi, tránh lỗi 500 / Warning (TV4 – Milestone 4)
normalize_post_input();

// Chặn mọi request POST không có mã CSRF hợp lệ (TV4 – Milestone 3)
csrf_check_request();

$app = new App();