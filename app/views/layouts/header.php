<?php /* Layout đầu trang (TV4) – bản tĩnh: menu cố định, chưa lấy dữ liệu từ CSDL */ ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($data['title'] ?? 'Trang chủ') ?> | WarningInfo</title>
  <meta name="description" content="WarningInfo – Hệ thống báo cáo và cảnh báo vấn đề môi trường từ cộng đồng.">
  <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- CSS của dự án (tách theo trang) -->
  <link href="<?= asset('css/base.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/layout.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/components.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/home.css') ?>" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-wi sticky-top">
    <div class="container">
      <a class="navbar-brand" href="<?= url('') ?>">
        <img src="<?= asset('img/logo.svg') ?>" alt="WarningInfo logo">
        <span>Warning<em>Info</em></span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto ms-lg-3">
          <li class="nav-item"><a class="nav-link<?= ($data['active'] ?? '') === 'home' ? ' active' : '' ?>" href="<?= url('') ?>">Trang chủ</a></li>
          <li class="nav-item"><a class="nav-link<?= ($data['active'] ?? '') === 'report' ? ' active' : '' ?>" href="<?= url('report') ?>">Cảnh báo</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Danh mục</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= url('report?category%5B0%5D=1') ?>"><i class="bi bi-trash3 me-2"></i>Ô nhiễm rác thải</a></li>
              <li><a class="dropdown-item" href="<?= url('report?category%5B0%5D=2') ?>"><i class="bi bi-droplet-half me-2"></i>Ô nhiễm nguồn nước</a></li>
              <li><a class="dropdown-item" href="<?= url('report?category%5B0%5D=3') ?>"><i class="bi bi-cloud-haze2 me-2"></i>Ô nhiễm không khí</a></li>
              <li><a class="dropdown-item" href="<?= url('report?category%5B0%5D=4') ?>"><i class="bi bi-cloud-lightning-rain me-2"></i>Ngập úng</a></li>
              <li><a class="dropdown-item" href="<?= url('report?category%5B0%5D=5') ?>"><i class="bi bi-tree me-2"></i>Cháy rừng</a></li>
              <li><a class="dropdown-item" href="<?= url('report?category%5B0%5D=6') ?>"><i class="bi bi-volume-up me-2"></i>Tiếng ồn</a></li>
              <li><a class="dropdown-item" href="<?= url('report?category%5B0%5D=7') ?>"><i class="bi bi-exclamation-octagon me-2"></i>Sự cố hoá chất</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= url('') ?>#cach-hoat-dong">Hướng dẫn</a></li>
        </ul>
        <form class="nav-search position-relative me-lg-3 d-lg-none d-xl-block" action="<?= url('report') ?>" method="get" role="search">
          <i class="bi bi-search"></i>
          <input class="form-control" type="search" name="q" placeholder="Tìm cảnh báo..." aria-label="Tìm kiếm">
        </form>
        <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
          <a href="<?= url('report/create') ?>" class="btn btn-warning fw-semibold"><i class="bi bi-megaphone me-1"></i> Gửi báo cáo</a>
          <a href="<?= url('auth/login') ?>" class="btn btn-outline-primary">Đăng nhập</a>
        </div>
      </div>
    </div>
  </nav>
  <main>
