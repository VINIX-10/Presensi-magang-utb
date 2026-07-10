<?php
// 1. Panggil satpam sesi dan koneksi database
require 'sesi.php';

$pesan_alert = "";

// 2. PROSES SIMPAN CATATAN LOGBOOK (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_logbook'])) {
    $id_kehadiran = $_POST['id_kehadiran'];
    $catatan_kerja = $_POST['catatan_kerja'];
    
    // Keamanan ekstra: pastikan logbook yang diubah memang milik user yang login
    $stmt = $conn->prepare("UPDATE kehadiran SET catatan = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $catatan_kerja, $id_kehadiran, $user_id);
    
    if ($stmt->execute()) {
        $pesan_alert = "Logbook aktivitas berhasil diperbarui!";
    } else {
        $pesan_alert = "Gagal memperbarui logbook.";
    }
}

// 3. MENGAMBIL DATA RIWAYAT ABSENSI
$query_riwayat = $conn->query("SELECT * FROM kehadiran WHERE user_id = '$user_id' ORDER BY tanggal DESC");

// 4. FUNGSI TRANSLATE HARI
function hariIndo(string $tanggal) {
    $hari_inggris = date('l', strtotime($tanggal));
    $daftar_hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    return $daftar_hari[$hari_inggris];
}
?>