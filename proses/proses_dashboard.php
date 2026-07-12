<?php
// Menggunakan __DIR__ agar path bersifat absolut dan anti-error saat di-include oleh index.php
require_once __DIR__ . '/../config/sesi.php';

// AMAN DARI ERROR: Deklarasikan variabel $user_id dari sesi secara eksplisit
$user_id = $_SESSION['user_id'];

// 2. Persiapan Data Dasar
$pesan_alert = "";
$tanggal_hari_ini = date('Y-m-d');
$waktu_sekarang = date('H:i:s');
$hari_ini_angka = date('N');
$is_weekend = ($hari_ini_angka >= 6) ? true : false;

// 3. AMBIL DATA USER (Untuk Profile Card)
$query_user = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $query_user->fetch_assoc();

// 4. PROSES SUBMIT POST (ABSEN MASUK, PULANG, & LOGBOOK)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // SATPAM CSRF: Periksa apakah token dikirim dan cocok dengan yang ada di server
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error 403: CSRF Token Invalid! Terdeteksi aktivitas mencurigakan.");
    }

    // A. LOGIKA ABSEN MASUK / LEMBUR
    if (isset($_POST['submit_masuk']) || isset($_POST['submit_lembur'])) {
        $status = isset($_POST['submit_lembur']) ? 'Lembur' : $_POST['status'];

        $cek_absen = $conn->query("SELECT id FROM kehadiran WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
        if ($cek_absen->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO kehadiran (user_id, tanggal, waktu_masuk, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $tanggal_hari_ini, $waktu_sekarang, $status);
            
            if ($stmt->execute()) {
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
    } 
    
    // B. LOGIKA ABSEN PULANG
    elseif (isset($_POST['submit_pulang'])) {
        $update = $conn->query("UPDATE kehadiran SET waktu_keluar = '$waktu_sekarang' WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
        if ($update) {
            $pesan_alert = "Absen PULANG berhasil dicatat! Selamat beristirahat.";
        }
    }
    
    // C. LOGIKA SIMPAN LOGBOOK (YANG SEBELUMNYA HILANG)
    elseif (isset($_POST['simpan_catatan'])) {
        $id_kehadiran = $_POST['id_kehadiran'];
        
        // XSS ARMOR: Mengamankan teks dari input jahat
        $catatan = htmlspecialchars($_POST['catatan'], ENT_QUOTES, 'UTF-8');

        // Keamanan ekstra: Pastikan yang diupdate adalah logbook milik user yang login
        $stmt = $conn->prepare("UPDATE kehadiran SET catatan = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $catatan, $id_kehadiran, $user_id);

        if ($stmt->execute()) {
            $pesan_alert = "Catatan Logbook berhasil diperbarui!";
        } else {
            $pesan_alert = "Gagal memperbarui logbook.";
        }
    }
}

// 5. CEK STATUS ABSENSI HARI INI (Untuk Smart Action Box)
$query_absen_hari_ini = $conn->query("SELECT * FROM kehadiran WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
$data_absen_hari_ini = $query_absen_hari_ini->fetch_assoc();

// 6. HITUNG STATISTIK KARTU (KPI)
$stat_hadir = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status IN ('Hadir', 'Lembur')")->fetch_assoc()['total'];
$stat_izin = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status IN ('Sakit', 'Izin')")->fetch_assoc()['total'];

// 7. AMBIL DATA UNTUK GRAFIK (CHART.JS) BULANAN
$tahun_ini = '2026'; // Bisa diganti date('Y') jika ingin otomatis tahun berjalan
$data_grafik_hadir = array_fill(1, 12, 0); // Siapkan array kosong untuk 12 bulan

// Query menghitung total kehadiran (Hadir + Lembur) per bulan di tahun ini
$query_grafik = $conn->query("
    SELECT MONTH(tanggal) as bulan, COUNT(id) as total 
    FROM kehadiran 
    WHERE user_id = '$user_id' 
    AND status IN ('Hadir', 'Lembur') 
    AND YEAR(tanggal) = '$tahun_ini' 
    GROUP BY MONTH(tanggal)
");

if ($query_grafik) {
    while ($row = $query_grafik->fetch_assoc()) {
        $data_grafik_hadir[$row['bulan']] = (int)$row['total'];
    }
}

// Konversi array PHP menjadi string JSON agar bisa dibaca oleh JavaScript
$json_grafik_hadir = json_encode(array_values($data_grafik_hadir));
?>