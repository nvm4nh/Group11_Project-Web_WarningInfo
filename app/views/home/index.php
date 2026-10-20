<?php
if (!defined('APPROOT')) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($_SERVER['REQUEST_URI'] ?? '', 'Group11_Project-Web_WarningInfo') !== false) {
        header("Location: $scheme://$host/Group11_Project-Web_WarningInfo/");
    } else {
        header("Location: $scheme://$host/public/");
    }
    exit;
}
require APPROOT . '/app/views/layouts/header.php';
?>
    <?php require APPROOT . '/app/views/home/_hero.php'; ?>
    <?php require APPROOT . '/app/views/home/_stats.php'; ?>
    <?php require APPROOT . '/app/views/home/_categories.php'; ?>
    <?php require APPROOT . '/app/views/home/_latest.php'; ?>
    <?php require APPROOT . '/app/views/home/_map.php'; ?>
    <?php require APPROOT . '/app/views/home/_how_it_works.php'; ?>
<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
