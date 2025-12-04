<?php
// Pengaturan koneksi database
$db_host = 'localhost';     // Biasanya 'localhost'
$db_user = 'root';          // User database Anda (default XAMPP)
$db_pass = '';              // Password database Anda (default XAMPP)
$db_name = 'db_cargo';      // Nama database yang Anda buat

// Membuat koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Mengatur karakter set
$conn->set_charset("utf8mb4");
?>