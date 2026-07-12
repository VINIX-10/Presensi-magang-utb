<?php
// 1. Panggil satpam sesi dan koneksi database
require_once __DIR__ . '/../config/sesi.php';

$pesan_alert = "";

// 2. PROSES CRUD AGENDA MANDIRI KALENDER (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // SATPAM CSRF: Periksa apakah token dikirim dan cocok dengan yang ada di server
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error 403: CSRF Token Invalid! Terdeteksi aktivitas mencurigakan.");
    }

    $action = $_POST['action'] ?? '';
    $id_agenda = $_POST['id_agenda'] ?? '';

    // PROSES HAPUS (DELETE)
    if (isset($_POST['hapus_agenda']) && !empty($id_agenda)) {
        $stmt = $conn->prepare("DELETE FROM agenda WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id_agenda, $user_id);
        
        if ($stmt->execute()) {
            $pesan_alert = "Agenda berhasil dihapus!";
        }
    } 
    // PROSES SIMPAN / UPDATE (CREATE & EDIT)
    elseif (isset($_POST['simpan_agenda'])) {
        
        // XSS ARMOR: Mengubah tag HTML/Javascript menjadi teks biasa sebelum masuk Database
        $judul = htmlspecialchars($_POST['judul_agenda'], ENT_QUOTES, 'UTF-8');
        $kategori = htmlspecialchars($_POST['kategori'], ENT_QUOTES, 'UTF-8');
        $tanggal = htmlspecialchars($_POST['tanggal_agenda'], ENT_QUOTES, 'UTF-8');
        $deskripsi = htmlspecialchars($_POST['deskripsi_agenda'], ENT_QUOTES, 'UTF-8');

        // Keamanan ekstra: query dipisah berdasarkan tipe action yang dikirim dari Frontend
        if ($action == 'create') {
            $stmt = $conn->prepare("INSERT INTO agenda (user_id, judul, kategori, tanggal, deskripsi) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $judul, $kategori, $tanggal, $deskripsi);
            
            if ($stmt->execute()) {
                $pesan_alert = "Agenda baru berhasil ditambahkan!";
            }
        } elseif ($action == 'edit' && !empty($id_agenda)) {
            $stmt = $conn->prepare("UPDATE agenda SET judul = ?, kategori = ?, tanggal = ?, deskripsi = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $judul, $kategori, $tanggal, $deskripsi, $id_agenda, $user_id);
            
            if ($stmt->execute()) {
                $pesan_alert = "Agenda berhasil diperbarui!";
            }
        }
    }
}

// 3. LOGIKA MATEMATIKA RENDER KALENDER
$bulan_aktif = isset($_GET['bulan']) ? sprintf('%02d', $_GET['bulan']) : '07';
$tahun_aktif = '2026';

// Menghitung slot kosong dan total hari untuk tampilan grid kalender
$total_hari = date('t', strtotime("$tahun_aktif-$bulan_aktif-01"));
$hari_pertama = date('N', strtotime("$tahun_aktif-$bulan_aktif-01"));
$slot_kosong = $hari_pertama - 1; 

// 4. AMBIL DATA AGENDA DARI DATABASE UNTUK BULAN AKTIF
$agenda_list = [];
$query_agenda = $conn->query("SELECT * FROM agenda WHERE user_id = '$user_id' AND MONTH(tanggal) = '$bulan_aktif' AND YEAR(tanggal) = '$tahun_aktif'");

if ($query_agenda) {
    while ($row = $query_agenda->fetch_assoc()) {
        // Kelompokkan data berdasarkan tanggal kalender (format hari 1-31)
        $tgl_hari = date('j', strtotime($row['tanggal'])); 
        $agenda_list[$tgl_hari] = $row; 
    }
}

// 5. FUNGSI TRANSLATE HARI (Dipertahankan jika nanti dibutuhkan Frontend)
function hariIndo(string $tanggal)
{
    $hari_inggris = date('l', strtotime($tanggal));
    $daftar_hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    return $daftar_hari[$hari_inggris];
}
?>