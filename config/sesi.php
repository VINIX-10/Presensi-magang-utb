<?php
session_start();
require 'koneksi.php'; 
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu benar

// 1. Cek apakah user sudah login
if (!isset($_SESSION['nama_user']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Cek Timeout (15 Menit)
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();

// 3. Tangkap ID user untuk digunakan di halaman manapun yang memanggil file ini
$user_id = $_SESSION['user_id'];
?>