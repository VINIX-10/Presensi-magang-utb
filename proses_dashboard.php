<?php
// 1. Panggil satpam sesi (karena user pakai nama file sesi.php)
require 'sesi.php'; 

// 2. Persiapan Data Dasar
$pesan_alert = "";
$tanggal_hari_ini = date('Y-m-d');
$waktu_sekarang = date('H:i:s');
$hari_ini_angka = date('N');
$is_weekend = ($hari_ini_angka >= 6) ? true : false;

// 3. AMBIL DATA USER (Untuk Profile Card)
$query_user = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $query_user->fetch_assoc();

// 4. PROSES SUBMIT ABSEN (MASUK / PULANG / LEMBUR)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_masuk']) || isset($_POST['submit_lembur'])) {
        $status = isset($_POST['submit_lembur']) ? 'Lembur' : $_POST['status'];

        $cek_absen = $conn->query("SELECT id FROM kehadiran WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
        if ($cek_absen->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO kehadiran (user_id, tanggal, waktu_masuk, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $tanggal_hari_ini, $waktu_sekarang, $status);
            if ($stmt->execute()) {
                // Logika Alert Cerdas berdasarkan Status
                if ($status == 'Lembur') {
                    $pesan_alert = "Semangat lembur! Waktu ekstra kamu sudah tercatat.";
                } elseif ($status == 'Sakit') {
                    $pesan_alert = "Laporan sakit diterima. Jangan lupa istirahat dan semoga lekas sembuh!";
                } elseif ($status == 'Izin') {
                    $pesan_alert = "Laporan izin berhasil dicatat. Semoga urusan hari ini dilancarkan!";
                } else {
                    $pesan_alert = "Mantap! Absen masuk berhasil dicatat. Selamat magang!";
                }
            } else {
                $pesan_alert = "Gagal menyimpan laporan hari ini!";
            }
        }
    } elseif (isset($_POST['submit_pulang'])) {
        $update = $conn->query("UPDATE kehadiran SET waktu_keluar = '$waktu_sekarang' WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
        if ($update) {
            $pesan_alert = "Absen PULANG berhasil dicatat! Selamat beristirahat.";
        }
    }
}

// 5. CEK STATUS ABSENSI HARI INI (Untuk Smart Action Box)
$query_absen_hari_ini = $conn->query("SELECT * FROM kehadiran WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
$data_absen_hari_ini = $query_absen_hari_ini->fetch_assoc();

// 6. HITUNG STATISTIK KARTU (KPI)
$stat_hadir = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status IN ('Hadir', 'Lembur')")->fetch_assoc()['total'];
$stat_izin = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status IN ('Sakit', 'Izin')")->fetch_assoc()['total'];
?>