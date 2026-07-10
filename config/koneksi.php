<?php
$host = "localhost";
$user = "root";       // User default Laragon
$pass = "";           // Password default Laragon (dikosongkan)
$db   = "absensi_db"; // Nama database yang dibuat di HeidiSQL

$conn = new mysqli($host, $user, $pass, $db);

// Memeriksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>