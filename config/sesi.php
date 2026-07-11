<?php
session_start();

// Pabrik CSRF Token: Buat token acak jika belum ada di sesi ini
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Memaksa browser membuang cache agar tombol 'Back' memuat ulang halaman ke server
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
// ----------------------------------
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