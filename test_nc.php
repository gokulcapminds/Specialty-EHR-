<?php
session_start();
$_SESSION['user_id'] = 1; // System Admin
require 'backend/vendor/autoload.php';
$nc = new App\Controllers\NotificationController();
$nc->index();
