<?php
/**
 * Database Configuration
 * SAYUR MAYUR E-Commerce
 */

// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sayur_mayur_app";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (development mode)
// Untuk production, set ke 0
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
