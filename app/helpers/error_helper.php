<?php
/**
 * Trang lỗi 404 / 500 (TV4 – Milestone 4)
 *
 *   show_404('Lời nhắn tuỳ chọn')  – dùng trong controller khi không tìm thấy dữ liệu (vd sự cố không tồn tại)
 *   show_500($maLoi, $exception)   – được gọi tự động khi có lỗi chưa xử lý (xem register_error_pages())
 *
 * Chi tiết kỹ thuật của lỗi 500 chỉ hiện khi app_debug() = true (mặc định: khi mở web ngay trên máy chạy XAMPP).
 * Trên hosting chỉ hiện mã lỗi; nội dung đầy đủ được ghi vào error log của PHP.
 */

/**
 * Có hiện chi tiết lỗi không. Ưu tiên APP_DEBUG trong config (hosting: config.local.php đặt false).
 * Nếu không khai báo thì CHỈ bật khi mở web ngay trên máy chạy XAMPP – phải thoả đủ 3 điều kiện, vì trên hosting
 * có proxy phía trước (Nginx, LiteSpeed, Cloudflare...) thì REMOTE_ADDR của mọi người cũng có thể là 127.0.0.1:
 *   1) URLROOT và tên miền đang mở đều là localhost / 127.0.0.1 / *.test / *.local
 *   2) kết nối đến từ chính máy (REMOTE_ADDR 127.0.0.1 hoặc ::1)
 *   3) request không đi qua proxy (không có X-Forwarded-For, X-Real-IP, Forwarded, Via, CF-Connecting-IP)
 */
function app_debug() {
    if (defined('APP_DEBUG')) return (bool)APP_DEBUG;

    $isLocalHost = function ($host) {
        $host = strtolower(trim(preg_replace('/:\d+$/', '', (string)$host), '[]'));
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true) || (bool)preg_match('/\.(test|local|localhost)$/', $host);
    };
    if (!defined('URLROOT') || !$isLocalHost(parse_url(URLROOT, PHP_URL_HOST))) return false;
    if (!$isLocalHost($_SERVER['HTTP_HOST'] ?? '')) return false;
    if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) return false;
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_FORWARDED', 'HTTP_VIA', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_HOST'] as $h) {
        if (!empty($_SERVER[$h])) return false;
    }
    return true;
}

/** Request AJAX / API (trả JSON thay vì trang HTML) */
function wants_json_response() {
    $uri = is_string($_GET['url'] ?? null) ? $_GET['url'] : '';   // ?url[]=... (mảng) → coi như rỗng, không lỗi
    return stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || preg_match('#(^|/)api_#', $uri);
}

/** Xoá mọi nội dung đã in ra dở dang (để trang lỗi không bị lẫn với nửa trang cũ) */
function discard_output() {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

/** Hiện trang 404 rồi dừng */
function show_404($message = null) {
    discard_output();
    http_response_code(404);
    if (wants_json_response()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $message ?: 'Không tìm thấy tài nguyên yêu cầu.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $data = [
        'title'   => 'Không tìm thấy trang',
        'message' => $message ?: 'Đường dẫn có thể đã bị đổi, bị gõ nhầm hoặc nội dung đã được gỡ khỏi hệ thống.',
        'path'    => '/' . trim(is_string($_GET['url'] ?? null) ? $_GET['url'] : '', '/'),
    ];
    require APPROOT . '/app/views/errors/404.php';
    exit;
}

/** Hiện trang 500 rồi dừng (trang độc lập, không dùng header/footer để chắc chắn hiển thị được) */
function show_500($ref, $error = null) {
    discard_output();
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (wants_json_response()) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Hệ thống đang gặp sự cố, vui lòng thử lại sau.', 'ref' => $ref], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $data = [
        'title'  => 'Hệ thống đang gặp sự cố',
        'ref'    => $ref,
        'detail' => (app_debug() && $error) ? $error : null,   // ['type','message','file','line']
    ];
    require APPROOT . '/app/views/errors/500.php';
    exit;
}

/** Ghi lỗi vào error log kèm mã tra cứu, trả về mã đó */
function log_app_error($type, $message, $file, $line) {
    $ref = 'E' . date('ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2)));
    error_log("[WarningInfo $ref] $type: $message in $file:$line");
    return $ref;
}

/**
 * Gọi 1 lần ở public/index.php: bắt mọi exception chưa xử lý và lỗi Fatal → trang 500.
 * Bật output buffering để khi lỗi xảy ra giữa chừng thì xoá được phần trang đã in.
 */
function register_error_pages() {
    ob_start();

    set_exception_handler(function ($e) {
        $ref = log_app_error(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        try {
            show_500($ref, ['type' => get_class($e), 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        } catch (Throwable $inner) {
            // Chính trang 500 cũng lỗi → ghi log và in bản tối giản, không bao giờ để lộ "Uncaught ..." ra màn hình
            log_app_error(get_class($inner), $inner->getMessage(), $inner->getFile(), $inner->getLine());
            discard_output();
            if (!headers_sent()) http_response_code(500);
            echo '<!doctype html><meta charset="utf-8"><title>Lỗi hệ thống</title><p>Hệ thống đang gặp sự cố, vui lòng thử lại sau. Mã tra cứu: '
                . htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') . '</p>';
            exit;
        }
    });

    register_shutdown_function(function () {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $ref = log_app_error('Fatal error', $err['message'], $err['file'], $err['line']);
            show_500($ref, ['type' => 'Fatal error', 'message' => $err['message'], 'file' => $err['file'], 'line' => $err['line']]);
        }
    });
}
