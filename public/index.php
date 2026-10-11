<?php
session_start();
require_once '../config/config.php';
require_once '../core/App.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';
require_once '../app/helpers/view_helper.php';
require_once '../app/helpers/auth_helper.php';

$app = new App();