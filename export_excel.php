<?php
// Jalur absolut langsung masuk ke folder config dari halaman depan
require_once __DIR__ . '/config/sesi.php';

// 1. Ambil data informasi mahasiswa
$query_user = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $query_user->fetch_assoc();

// 2. Pengaturan Periode
$periode_mulai = '2026-07-08';
$periode_selesai = '2026-10-08';
$hari_ini = date('Y-m-d');
$jam_kerja = '07:30 - 16:30';

function getHariKerja($start, $end) {
    $mulai = new DateTime($start);
    $selesai = new DateTime($end);
    $selesai->modify('+1 day'); 
    
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($mulai, $interval, $selesai);
    
    $hari_kerja = 0;
    foreach ($period as $dt) {
        if ($dt->format('N') <= 5) { 
            $hari_kerja++;
        }
    }
    return $hari_kerja;
}

$total_hari_kerja = getHariKerja($periode_mulai, $periode_selesai);
$tgl_terlewati = ($hari_ini > $periode_selesai) ? $periode_selesai : $hari_ini;
$hari_kerja_terlewati = getHariKerja($periode_mulai, $tgl_terlewati);

$sisa_hari_kerja = $total_hari_kerja - $hari_kerja_terlewati;
if ($sisa_hari_kerja < 0) $sisa_hari_kerja = 0;

$progres = ($total_hari_kerja > 0) ? ($hari_kerja_terlewati / $total_hari_kerja) : 0;
$progres_persen = round($progres * 100, 2) . '%';

// 3. Ambil data statistik dari Database
$stat_hadir = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status IN ('Hadir', 'Lembur')")->fetch_assoc()['total'];
$stat_izin  = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status = 'Izin'")->fetch_assoc()['total'];
$stat_sakit = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status = 'Sakit'")->fetch_assoc()['total'];
$stat_alpha = 0; 
$stat_cuti  = 0;
$stat_libur = $total_hari_kerja - ($stat_hadir + $stat_izin + $stat_sakit); 

// 4. Ambil riwayat absen lengkap
$query_absen = $conn->query("SELECT * FROM kehadiran WHERE user_id = '$user_id' ORDER BY tanggal ASC");

// =========================================================================
// HEADER UNTUK EXPORT FILE CSV (UNIVERSAL UNTUK HP DAN LAPTOP)
// =========================================================================
$filename = "Absensi_Magang_" . str_replace(' ', '_', $user_data['nama_user']) . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Buka output stream langsung ke browser
$output = fopen('php://output', 'w');

// Menambahkan BOM agar MS Excel membaca UTF-8 (mencegah karakter aneh)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// MENULIS DATA DASHBOARD KE DALAM BARIS CSV
fputcsv($output, ['DASHBOARD ABSENSI MAGANG - ' . strtoupper($user_data['nama_user'])]);
fputcsv($output, []); // Baris Kosong
fputcsv($output, ['Periode Mulai', ': ' . $periode_mulai, '', 'Status', 'Jumlah Hari']);
fputcsv($output, ['Periode Selesai', ': ' . $periode_selesai, '', 'Hadir & Lembur', $stat_hadir]);
fputcsv($output, ['Jam Kerja', ': ' . $jam_kerja, '', 'Izin', $stat_izin]);
fputcsv($output, ['Hari Ini', ': ' . $hari_ini, '', 'Sakit', $stat_sakit]);
fputcsv($output, ['Total Hari Kerja (Senin-Jumat)', ': ' . $total_hari_kerja, '', 'Alpha', $stat_alpha]);
fputcsv($output, ['Hari Kerja Terlewati', ': ' . $hari_kerja_terlewati, '', 'Cuti', $stat_cuti]);
fputcsv($output, ['Sisa Hari Kerja', ': ' . $sisa_hari_kerja, '', 'Libur / Belum Absen', $stat_libur]);
fputcsv($output, ['Progres (%)', ': ' . $progres_persen, '', '', '']);
fputcsv($output, []); // Baris Kosong
fputcsv($output, []); // Baris Kosong

// MENULIS HEADER TABEL UTAMA
fputcsv($output, ['No', 'Tanggal', 'Hari', 'Jam Masuk', 'Jam Keluar', 'Total Jam', 'Status', 'Catatan / Logbook']);

// Fungsi Translate Hari
function getHariIndo(string $tanggal) {
    $hari_inggris = date('l', strtotime($tanggal));
    $daftar_hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    return $daftar_hari[$hari_inggris];
}

// MENULIS ISI TABEL DARI DATABASE
$no = 1;
if($query_absen->num_rows > 0) {
    while($row = $query_absen->fetch_assoc()) {
        $tgl = $row['tanggal'];
        $hari_indo = getHariIndo($tgl);
        
        // Logika Pengambilan Jam Asli dari Database
        if (in_array($row['status'], ['Hadir', 'Lembur'])) {
            $jam_masuk = substr($row['waktu_masuk'], 0, 5);
            
            if (!empty($row['waktu_keluar'])) {
                $jam_keluar = substr($row['waktu_keluar'], 0, 5);
                $selisih = strtotime($row['waktu_keluar']) - strtotime($row['waktu_masuk']);
                $total_jam = round($selisih / 3600, 1) . ' Jam';
            } else {
                $jam_keluar = 'Belum Checkout';
                $total_jam = '-';
            }
        } else {
            $jam_masuk = substr($row['waktu_masuk'], 0, 5); // Waktu lapor
            $jam_keluar = '-';
            $total_jam = '-';
        }

        // Ambil catatan logbook
        $catatan = !empty($row['catatan']) ? $row['catatan'] : '-';

        // Masukkan data per baris ke CSV
        fputcsv($output, [
            $no++, 
            $tgl, 
            $hari_indo, 
            $jam_masuk, 
            $jam_keluar, 
            $total_jam, 
            $row['status'], 
            $catatan
        ]);
    }
} else {
    fputcsv($output, ['Belum ada data absensi yang tercatat.']);
}

// Tutup file stream
fclose($output);
exit;
?>