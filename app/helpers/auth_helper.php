<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
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