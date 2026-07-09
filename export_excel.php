<?php
session_start();
require 'koneksi.php';

// Verifikasi sesi
if (!isset($_SESSION['nama_user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Ambil data informasi mahasiswa
$query_user = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $query_user->fetch_assoc();

// 2. Pengaturan Periode (Sesuai Referensi)
$periode_mulai = '2026-07-08';
$periode_selesai = '2026-10-08';
$hari_ini = date('Y-m-d');
$jam_kerja = '07:30 - 16:30';

// Fungsi untuk menghitung Hari Kerja (Senin-Jumat)
function getHariKerja($start, $end) {
    $mulai = new DateTime($start);
    $selesai = new DateTime($end);
    $selesai->modify('+1 day'); // Termasuk hari terakhir
    
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($mulai, $interval, $selesai);
    
    $hari_kerja = 0;
    foreach ($period as $dt) {
        if ($dt->format('N') <= 5) { // 1 (Senin) s.d 5 (Jumat)
            $hari_kerja++;
        }
    }
    return $hari_kerja;
}

// Perhitungan Hari
$total_hari_kerja = getHariKerja($periode_mulai, $periode_selesai);

// Batasi perhitungan hari terlewati sampai hari ini (atau maksimal sampai periode selesai)
$tgl_terlewati = ($hari_ini > $periode_selesai) ? $periode_selesai : $hari_ini;
$hari_kerja_terlewati = getHariKerja($periode_mulai, $tgl_terlewati);

$sisa_hari_kerja = $total_hari_kerja - $hari_kerja_terlewati;
if ($sisa_hari_kerja < 0) $sisa_hari_kerja = 0;

$progres = ($total_hari_kerja > 0) ? ($hari_kerja_terlewati / $total_hari_kerja) : 0;
$progres_persen = round($progres * 100, 2) . '%';

// 3. Ambil data statistik kehadiran dari Database
$stat_hadir = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status = 'Hadir'")->fetch_assoc()['total'];
$stat_izin  = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status = 'Izin'")->fetch_assoc()['total'];
$stat_sakit = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status = 'Sakit'")->fetch_assoc()['total'];
$stat_alpha = 0; // Asumsi belum ada tabel khusus alpha
$stat_cuti  = 0;
$stat_libur = $total_hari_kerja - ($stat_hadir + $stat_izin + $stat_sakit); // Asumsi sisa hari adalah libur/belum absen

// 4. Ambil riwayat absen lengkap
$query_absen = $conn->query("SELECT * FROM kehadiran WHERE user_id = '$user_id' ORDER BY tanggal ASC");

// Set header agar output dikenali sebagai file Excel (.xls)
header("Content-type: application/vnd-ms-excel");
$filename = "Absensi_Magang_" . str_replace(' ', '_', $user_data['nama_user']) . ".xls";
header("Content-Disposition: attachment; filename=\"$filename\"");
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .tabel-utama { border-collapse: collapse; width: 100%; }
        .tabel-utama th, .tabel-utama td { border: 1px solid #000; padding: 5px; }
        .bg-blue { background-color: #4472C4; color: #fff; font-weight: bold; }
        .bg-gray { background-color: #D9D9D9; font-weight: bold; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .no-border td { border: none !important; }
    </style>
</head>
<body>

    <table class="tabel-utama" style="margin-bottom: 30px;">
        <tr class="no-border">
            <td colspan="5" style="font-size: 16px; font-weight: bold;">DASHBOARD ABSENSI MAGANG</td>
        </tr>
        <tr class="no-border"><td colspan="5"></td></tr>
        
        <tr class="no-border">
            <td width="20%">Periode Mulai</td>
            <td width="20%">: <?php echo $periode_mulai; ?></td>
            <td width="10%"></td>
            <td width="15%" class="bg-gray">Status</td>
            <td width="15%" class="bg-gray text-center">Jumlah Hari</td>
        </tr>
        <tr class="no-border">
            <td>Periode Selesai</td>
            <td>: <?php echo $periode_selesai; ?></td>
            <td></td>
            <td style="border: 1px solid #000;">Hadir</td>
            <td style="border: 1px solid #000;" class="text-center"><?php echo $stat_hadir; ?></td>
        </tr>
        <tr class="no-border">
            <td>Jam Kerja</td>
            <td>: <?php echo $jam_kerja; ?></td>
            <td></td>
            <td style="border: 1px solid #000;">Izin</td>
            <td style="border: 1px solid #000;" class="text-center"><?php echo $stat_izin; ?></td>
        </tr>
        <tr class="no-border">
            <td>Hari Ini</td>
            <td>: <?php echo $hari_ini; ?></td>
            <td></td>
            <td style="border: 1px solid #000;">Sakit</td>
            <td style="border: 1px solid #000;" class="text-center"><?php echo $stat_sakit; ?></td>
        </tr>
        <tr class="no-border">
            <td>Total Hari Kerja (Senin-Jumat)</td>
            <td>: <?php echo $total_hari_kerja; ?></td>
            <td></td>
            <td style="border: 1px solid #000;">Alpha</td>
            <td style="border: 1px solid #000;" class="text-center"><?php echo $stat_alpha; ?></td>
        </tr>
        <tr class="no-border">
            <td>Hari Kerja Terlewati</td>
            <td>: <?php echo $hari_kerja_terlewati; ?></td>
            <td></td>
            <td style="border: 1px solid #000;">Cuti</td>
            <td style="border: 1px solid #000;" class="text-center"><?php echo $stat_cuti; ?></td>
        </tr>
        <tr class="no-border">
            <td>Sisa Hari Kerja</td>
            <td>: <?php echo $sisa_hari_kerja; ?></td>
            <td></td>
            <td style="border: 1px solid #000;">Libur / Belum Absen</td>
            <td style="border: 1px solid #000;" class="text-center"><?php echo $stat_libur; ?></td>
        </tr>
        <tr class="no-border">
            <td>Progres (%)</td>
            <td>: <?php echo $progres_persen; ?></td>
            <td colspan="3"></td>
        </tr>
    </table>

    <br><br>

    <table class="tabel-utama">
        <tr>
            <th class="bg-blue">No</th>
            <th class="bg-blue">Tanggal</th>
            <th class="bg-blue">Hari</th>
            <th class="bg-blue">Jam Masuk</th>
            <th class="bg-blue">Jam Keluar</th>
            <th class="bg-blue">Jam Kerja (jam)</th>
            <th class="bg-blue">Status</th>
            <th class="bg-blue">Keterangan</th>
            <th class="bg-blue">Tanda Tangan</th>
        </tr>
        
        <?php 
        // Fungsi untuk konversi hari bahasa Inggris ke bahasa Indonesia
        function getHariIndo($tanggal) {
            $hari_inggris = date('l', strtotime($tanggal));
            $daftar_hari = array(
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            );
            return $daftar_hari[$hari_inggris];
        }

        $no = 1;
        if($query_absen->num_rows > 0) {
            while($row = $query_absen->fetch_assoc()) {
                $tgl = $row['tanggal'];
                $hari_indo = getHariIndo($tgl);
                
                // Kalkulasi Jam Kerja (Dummy default jam keluar = 16:30 jika ada jam masuk)
                $jam_masuk = $row['waktu_masuk'];
                $jam_keluar = ($row['status'] == 'Hadir') ? '16:30:00' : '';
                $jam_kerja_akumulasi = ($row['status'] == 'Hadir') ? '9' : ''; 

                echo "<tr>";
                echo "<td class='text-center'>" . $no++ . "</td>";
                echo "<td>" . $tgl . "</td>";
                echo "<td>" . $hari_indo . "</td>";
                echo "<td class='text-center'>" . substr($jam_masuk, 0, 5) . "</td>";
                echo "<td class='text-center'>" . substr($jam_keluar, 0, 5) . "</td>";
                echo "<td class='text-center'>" . $jam_kerja_akumulasi . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td></td>"; // Keterangan Kosong
                echo "<td></td>"; // Tanda Tangan Kosong
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9' class='text-center'>Belum ada data absensi yang tercatat.</td></tr>";
        }
        ?>
    </table>

</body>
</html>