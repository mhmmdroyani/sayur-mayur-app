<?php
if (!isset($_SESSION)) {
    session_start();
}

// Cek apakah sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

// Session timeout (30 menit)
$timeout = 1800; // 30 minutes
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?timeout=1");
    exit;
}

// Update last activity time
$_SESSION['login_time'] = time();
