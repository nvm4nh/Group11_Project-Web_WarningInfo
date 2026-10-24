<?php
/**
 * Chống tấn công CSRF (Cross-Site Request Forgery) – TV4, Milestone 3
 *
 * Ý tưởng: mỗi phiên đăng nhập (session) có 1 mã bí mật ngẫu nhiên (token).
 * Mọi form POST đều gửi kèm mã này; trang web lạ không đọc được mã nên không thể
 * giả mạo request thay người dùng (ví dụ: tự đổi trạng thái sự cố, xoá tài khoản...).
 *
 *   - Trong form:   <?= csrf_field() ?>
 *   - AJAX (fetch): gửi header  X-CSRF-Token: <token>  (lấy từ <meta name="csrf-token">)
 *   - Kiểm tra:     public/index.php gọi csrf_check_request() cho MỌI request POST,
 *                   nên controller không cần tự kiểm tra lại.
 */

const CSRF_SESSION_KEY = 'csrf_token';
const CSRF_FIELD_NAME  = 'csrf_token';
const CSRF_HEADER_NAME = 'HTTP_X_CSRF_TOKEN';

/** Lấy token của phiên hiện tại (tạo mới nếu chưa có). */
function csrf_token() {
    if (empty($_SESSION[CSRF_SESSION_KEY]) || !is_string($_SESSION[CSRF_SESSION_KEY])) {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_SESSION_KEY];
}

/** Ô input ẩn chứa token – đặt bên trong mọi <form method="post">. */
function csrf_field() {
    return '<input type="hidden" name="' . CSRF_FIELD_NAME . '" value="' . e(csrf_token()) . '">';
}

/** Token gửi lên có khớp với token trong session không (so sánh an toàn thời gian). */
function csrf_verify() {
    $sent = $_POST[CSRF_FIELD_NAME] ?? ($_SERVER[CSRF_HEADER_NAME] ?? '');
    return is_string($sent) && $sent !== '' && hash_equals(csrf_token(), $sent);
}

/** Request có phải AJAX/JSON không (để trả lỗi dạng JSON thay vì chuyển trang). */
function csrf_is_ajax() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return isset($_SERVER[CSRF_HEADER_NAME])
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || stripos($accept, 'application/json') !== false;
}

/**
 * Gọi 1 lần ở public/index.php trước khi chạy controller.
 * Request POST thiếu/sai token sẽ bị chặn: AJAX nhận JSON (mã 403), form thường thấy trang báo lỗi
 * (có nút quay lại – trình duyệt giữ nguyên dữ liệu đã nhập trong form).
 */
function csrf_check_request() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || csrf_verify()) {
        return;
    }

    // Gửi file quá lớn (vượt post_max_size) thì PHP xoá sạch $_POST nên token cũng mất → báo đúng nguyên nhân
    $tooLarge = empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
    $message  = $tooLarge
        ? 'Dữ liệu gửi lên quá lớn (máy chủ cho phép tối đa ' . ini_get('post_max_size') . '). Vui lòng chọn ảnh nhỏ hơn rồi gửi lại.'
        : 'Phiên làm việc đã hết hạn hoặc yêu cầu không hợp lệ. Vui lòng tải lại trang rồi thử lại.';

    http_response_code(403);
    if (csrf_is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = ['title' => 'Yêu cầu không hợp lệ', 'message' => $message, 'backUrl' => csrf_safe_back_url()];
    require APPROOT . '/app/views/errors/csrf.php';
    exit;
}

/** Trang trước nếu cùng website, ngược lại về trang chủ (tránh chuyển hướng sang web lạ). */
function csrf_safe_back_url() {
    $back = $_SERVER['HTTP_REFERER'] ?? '';
    $root = rtrim(URLROOT, '/') . '/';
    return ($back !== '' && strpos($back, $root) === 0) ? $back : $root;
}
