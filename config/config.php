<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'warninginfo_db');
define('APPROOT', dirname(dirname(__FILE__)));
define('URLROOT', 'http://localhost/Group11_Project-Web_WarningInfo');
define('SITENAME', 'WarningInfo');

// Giờ Việt Nam cho date()/strtotime() — tránh lệch giờ khi php.ini để UTC hoặc Europe/Berlin (mặc định XAMPP)
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
