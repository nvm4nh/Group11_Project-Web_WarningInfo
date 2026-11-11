<?php
function isLoggedIn() {
    static $synced = false;
    if (!isset($_SESSION['user_id'])) return false;
    if (!$synced) {
        $synced = true;
        syncSessionUser();
    }
    return isset($_SESSION['user_id']);
}

/**
 * Đối chiếu phiên đăng nhập với CSDL 1 lần mỗi request (Milestone 4):
 *  - Tài khoản bị khoá / vô hiệu hoá / đã xoá → đăng xuất ngay, không phải đợi hết phiên.
 *  - Admin đổi vai trò (vd hạ cán bộ xuống người dân) → quyền mới có hiệu lực ngay.
 */
function syncSessionUser() {
    $db = new Database();
    $db->query('SELECT role, full_name, account_status FROM users WHERE id = :id LIMIT 1');
    $db->bind(':id', (int)$_SESSION['user_id']);
    $user = $db->single();

    if (!$user || $user->account_status !== 'active') {
        foreach (['user_id', 'full_name', 'email', 'role'] as $key) unset($_SESSION[$key]);
        session_regenerate_id(true);
        setFlash('danger', $user ? accountStatusMessage($user->account_status) : 'Tài khoản không còn tồn tại.');
        return;
    }
    $_SESSION['role']      = $user->role;
    $_SESSION['full_name'] = $user->full_name;
}

/** Lời nhắn khi tài khoản không ở trạng thái hoạt động */
function accountStatusMessage($status) {
    return $status === 'locked'
        ? 'Tài khoản của bạn đã bị khoá. Vui lòng liên hệ quản trị viên.'
        : 'Tài khoản của bạn đang bị vô hiệu hoá. Vui lòng liên hệ quản trị viên.';
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function isOfficer() {
    return isLoggedIn() && in_array($_SESSION['role'] ?? '', ['officer', 'admin'], true);
}

function requireOfficer() {
    if (!isOfficer()) {
        setFlash('danger', 'Trang này chỉ dành cho cán bộ xử lý sự cố');
        redirect(isLoggedIn() ? '' : 'auth/login');
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Vui lòng đăng nhập để tiếp tục');
        redirect('auth/login');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        setFlash('danger', 'Bạn không có quyền truy cập khu vực Quản trị');
        redirect('');
    }
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}