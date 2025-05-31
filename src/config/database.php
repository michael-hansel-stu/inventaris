<?php
define('DB_HOST', '127.0.0.1');      // Atau 'localhost'
define('DB_USERNAME', 'root');      // Username database Anda (default XAMPP adalah 'root')
define('DB_PASSWORD', '');          // Password database Anda (default XAMPP adalah kosong)
define('DB_NAME', 'inventaris');    // Nama database yang Anda buat (sesuai inventaris.sql)
define('DB_PORT', 3306);            // Port MySQL default (opsional jika menggunakan port standar)

// --- Membuat Koneksi ke Database menggunakan MySQLi ---
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

// --- Memeriksa Koneksi ---
if ($conn->connect_error) {
    error_log("Koneksi Gagal: " . $conn->connect_error); // Catat ke log error PHP
    die("Koneksi Gagal: " . $conn->connect_error . ". Pastikan database '" . DB_NAME . "' sudah dibuat dan server MySQL berjalan.");
}

if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}
?>