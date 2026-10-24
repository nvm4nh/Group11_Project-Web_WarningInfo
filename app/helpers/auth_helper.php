<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

// Cán bộ xử lý sự cố (officer). Admin cũng có thể được phân công nên được vào bàn làm việc cán bộ.
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
        setFlash('danger', 'Bạn không có quyền truy cập trang quản trị');
        redirect('auth/login');
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