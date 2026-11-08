<?php
session_start();
require_once '../config/config.php';
require_once '../core/App.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';
require_once '../app/helpers/view_helper.php';
require_once '../app/helpers/auth_helper.php';
require_once '../app/helpers/csrf_helper.php';

// Chặn mọi request POST không có mã CSRF hợp lệ (TV4 – Milestone 3)
csrf_check_request();

$app = new App();