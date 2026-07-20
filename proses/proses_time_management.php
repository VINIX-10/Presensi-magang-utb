<?php
// 1. Panggil satpam sesi dan koneksi database
require_once __DIR__ . '/../config/sesi.php';

// === FIX BUG: PEMBUAT TOKEN CSRF ===
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// ====================================

$pesan_alert = "";

// 2. PROSES CRUD AGENDA MANDIRI KALENDER & TARGET MILESTONE (POST)
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
    // PROSES SIMPAN / UPDATE AGENDA (CREATE & EDIT)
    elseif (isset($_POST['simpan_agenda'])) {
        
        // XSS ARMOR: Mengubah tag HTML/Javascript menjadi teks biasa sebelum masuk Database
        $judul = htmlspecialchars($_POST['judul_agenda'], ENT_QUOTES, 'UTF-8');
        $kategori = htmlspecialchars($_POST['kategori'], ENT_QUOTES, 'UTF-8');
        $tanggal = htmlspecialchars($_POST['tanggal_agenda'], ENT_QUOTES, 'UTF-8');
        $deskripsi = htmlspecialchars($_POST['deskripsi_agenda'], ENT_QUOTES, 'UTF-8');
        
        // [PENAMBAHAN KEKURANGAN]: Menangkap Jam dan Offset
        $waktu = htmlspecialchars($_POST['waktu_agenda'], ENT_QUOTES, 'UTF-8');
        $offset = (int)$_POST['pengingat_offset'];

        // Keamanan ekstra: query dipisah berdasarkan tipe action yang dikirim dari Frontend
        if ($action == 'create') {
            // [PENAMBAHAN KEKURANGAN]: Insert dengan kolom waktu dan pengingat_offset
            $stmt = $conn->prepare("INSERT INTO agenda (user_id, judul, kategori, tanggal, waktu, pengingat_offset, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssis", $user_id, $judul, $kategori, $tanggal, $waktu, $offset, $deskripsi);
            
            if ($stmt->execute()) {
                $pesan_alert = "Agenda baru berhasil ditambahkan!";
            }
        } elseif ($action == 'edit' && !empty($id_agenda)) {
            // [PENAMBAHAN KEKURANGAN]: Update dengan kolom waktu dan pengingat_offset
            $stmt = $conn->prepare("UPDATE agenda SET judul = ?, kategori = ?, tanggal = ?, waktu = ?, pengingat_offset = ?, deskripsi = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssisii", $judul, $kategori, $tanggal, $waktu, $offset, $deskripsi, $id_agenda, $user_id);
            
            if ($stmt->execute()) {
                $pesan_alert = "Agenda berhasil diperbarui!";
            }
        }
    }
    // PROSES UBAH MILESTONE BULANAN (UPDATE) - IMPLEMENTASI BARU
    elseif (isset($_POST['ubah_milestone'])) {
        $bulan_key = trim($_POST['bulan_key']);
        $status = trim($_POST['status_milestone']);
        
        // XSS ARMOR untuk deskripsi target operasional dan IT
        $operasional = htmlspecialchars($_POST['operasional_milestone'], ENT_QUOTES, 'UTF-8');
        $it = htmlspecialchars($_POST['it_milestone'], ENT_QUOTES, 'UTF-8');

        $stmt_update = $conn->prepare("UPDATE milestones SET status = ?, operasional = ?, it = ? WHERE user_id = ? AND bulan_key = ?");
        $stmt_update->bind_param("sssis", $status, $operasional, $it, $user_id, $bulan_key);

        if ($stmt_update->execute()) {
            // Refresh halaman agar data terbaru langsung ter-render bersih di halaman depan
            header("Location: time-management.php?bulan=" . $bulan_key);
            exit;
        }
    }
}

// 3. LOGIKA MATEMATIKA RENDER KALENDER
$bulan_aktif = isset($_GET['bulan']) ? sprintf('%02d', $_GET['bulan']) : '07';
$tahun_aktif = '2026';

// Konversi angka ke nama bulan
$nama_bulan_indo = [
    '07' => 'Juli 2026',
    '08' => 'Agustus 2026',
    '09' => 'September 2026',
    '10' => 'Oktober 2026'
];

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

// 5. AMBIL DATA MILESTONE DINAMIS DARI DATABASE (DENGAN AUTOMATIC SEEDER)
$milestone_list = [];
$stmt_ms = $conn->prepare("SELECT * FROM milestones WHERE user_id = ? ORDER BY bulan_key ASC");
$stmt_ms->bind_param("i", $user_id);
$stmt_ms->execute();
$res_ms = $stmt_ms->get_result();

while ($row = $res_ms->fetch_assoc()) {
    $milestone_list[$row['bulan_key']] = [
        'id'          => $row['id'],
        'judul'       => $row['judul'],
        'status'      => $row['status'],
        'operasional' => $row['operasional'],
        'it'          => $row['it']
    ];
}

// OTOMASI SEEDER: Jika data milestone di database user ini masih kosong, isi otomatis
if (empty($milestone_list)) {
    $defaults = [
        ['07', 'Milestone 1 (Juli)', 'Selesai', 'Penginputan & rekapitulasi data harian finansial (Tabungan, Giro, Depo) Uker Sumedang ke Excel.', 'Analisis kelemahan sistem absen fisik pemagang & perancangan basis data Tracker.'],
        ['08', 'Milestone 2 (Agustus)', 'Berjalan', 'Monitoring akuisisi produk digital (Brimo, Qlola, QRIS) dan validasi leads Brispot.', 'Desain UI/UX dashboard desktop serta sinkronisasi penataan kolom logbook agar sesuai output Excel.'],
        ['09', 'Milestone 3 (September)', 'Pending', 'Evaluasi berkala alokasi Dana Talangan Brilink dan volume transaksi Uker.', 'Implementasi koding CRUD agenda mandiri kalender dan pengujian fungsi unduh file rekapitulasi.'],
        ['10', 'Milestone 4 (Oktober)', 'Pending', 'Penyusunan laporan akhir magang, dokumentasi kode program, serta serah terima sistem.', 'Final deployment sistem absensi magang ke server produksi Laragon.']
    ];

    foreach ($defaults as $d) {
        $ins = $conn->prepare("INSERT INTO milestones (user_id, bulan_key, judul, status, operasional, it) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param("isssss", $user_id, $d[0], $d[1], $d[2], $d[3], $d[4]);
        $ins->execute();
    }

    // Ambil ulang setelah di-isi data default
    $stmt_ms->execute();
    $res_ms = $stmt_ms->get_result();
    while ($row = $res_ms->fetch_assoc()) {
        $milestone_list[$row['bulan_key']] = [
            'id'          => $row['id'],
            'judul'       => $row['judul'],
            'status'      => $row['status'],
            'operasional' => $row['operasional'],
            'it'          => $row['it']
        ];
    }
}

// 6. FUNGSI TRANSLATE HARI (Dipertahankan jika nanti dibutuhkan Frontend)
function hariIndo(string $tanggal)
{
    $hari_inggris = date('l', strtotime($tanggal));
    $daftar_hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    return $daftar_hari[$hari_inggris];
}

// =========================================================================
// 7. FITUR PENGINGAT DINAMIS (ALGORITMA PENCARIAN DATABASE)
// =========================================================================
$agenda_besok = null;

// [PENAMBAHAN KEKURANGAN]: Ganti data mock statis menjadi pencarian SQL langsung ke database
$query_reminder = $conn->prepare("
    SELECT judul, tanggal, waktu, pengingat_offset, deskripsi 
    FROM agenda 
    WHERE user_id = ? 
    AND CONCAT(tanggal, ' ', waktu) > NOW() 
    AND NOW() >= DATE_SUB(CONCAT(tanggal, ' ', waktu), INTERVAL pengingat_offset HOUR)
    ORDER BY tanggal ASC, waktu ASC 
    LIMIT 1
");

$query_reminder->bind_param("i", $user_id);
$query_reminder->execute();
$result_reminder = $query_reminder->get_result();

if ($row_reminder = $result_reminder->fetch_assoc()) {
    $agenda_besok = [
        'judul'     => $row_reminder['judul'],
        'tanggal'   => $row_reminder['tanggal'],
        'waktu'     => $row_reminder['waktu'],
        'offset'    => $row_reminder['pengingat_offset'],
        'deskripsi' => $row_reminder['deskripsi']
    ];
}
?>