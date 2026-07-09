<?php
session_start();
require 'koneksi.php'; // Hubungkan ke database
date_default_timezone_set('Asia/Jakarta');

// 1. Cek apakah user sudah login
if (!isset($_SESSION['nama_user']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Cek Timeout (15 Menit = 900 Detik)
$timeout_duration = 200; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();

$user_id = $_SESSION['user_id'];

// Mengambil seluruh data kehadiran user ini, diurutkan dari tanggal terbaru
$query_riwayat = $conn->query("SELECT * FROM kehadiran WHERE user_id = '$user_id' ORDER BY tanggal DESC");

// Fungsi sederhana untuk translate nama hari ke Bahasa Indonesia
function hariIndo($tanggal) {
    $hari_inggris = date('l', strtotime($tanggal));
    $daftar_hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    return $daftar_hari[$hari_inggris];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Management - Absensi Magang</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen overflow-hidden text-gray-800 bg-[#F4F7FE]">

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden transition-opacity"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-100 flex flex-col justify-between transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="p-6">
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center gap-3 text-blue-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    <h1 class="text-xl font-bold tracking-wide">UTB Tracker</h1>
                </div>
                <button id="closeSidebarBtn" class="md:hidden text-gray-400 hover:text-red-500 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <p class="text-xs font-bold text-gray-400 mb-4 tracking-wider">MAIN MENU</p>
            <nav class="space-y-2">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="time-management.php" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-600 rounded-xl font-semibold transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Time Management
                </a>
            </nav>
        </div>
        <div class="p-6 border-t border-gray-100">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl font-medium transition group">
                <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Ganti Akun
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-y-auto w-full relative">
        <header class="bg-white/80 backdrop-blur-md px-6 py-4 flex justify-between items-center sticky top-0 z-10 shadow-sm md:shadow-none md:border-b md:border-gray-100">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <button id="mobileMenuBtn" class="md:hidden p-2 -ml-2 text-gray-600 hover:text-blue-600 focus:outline-none transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="relative w-full max-w-xs sm:block">
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Kehadiran</h2>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="export_excel.php" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2 px-4 md:px-5 rounded-full flex items-center gap-2 shadow-sm transition">
                    <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span class="md:hidden">Export</span>
                    <span class="hidden md:inline">Export Log</span>
                </a>
            </div>
        </header>

        <div class="p-4 md:p-8 space-y-6">
            <div class="flex flex-wrap md:flex-nowrap justify-between items-center gap-4 mb-2">
                <div class="flex gap-3 w-full md:w-auto">
                    <select class="flex-1 md:flex-none bg-white border border-gray-200 rounded-lg px-4 py-2 text-sm font-medium focus:outline-none focus:border-blue-500 shadow-sm">
                        <option>Semua Bulan</option>
                        <option>Juli 2026</option>
                        <option>Agustus 2026</option>
                    </select>
                    <select class="flex-1 md:flex-none bg-white border border-gray-200 rounded-lg px-4 py-2 text-sm font-medium focus:outline-none focus:border-blue-500 shadow-sm">
                        <option>Semua Status</option>
                        <option>Hadir</option>
                        <option>Sakit/Izin</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-sm">
                                <th class="py-4 px-6 font-semibold text-gray-500 rounded-tl-3xl">Tanggal</th>
                                <th class="py-4 px-6 font-semibold text-gray-500">Jam Masuk</th>
                                <th class="py-4 px-6 font-semibold text-gray-500">Jam Keluar</th>
                                <th class="py-4 px-6 font-semibold text-gray-500">Total Jam</th>
                                <th class="py-4 px-6 font-semibold text-gray-500">Status</th>
                                <th class="py-4 px-6 font-semibold text-gray-500 rounded-tr-3xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            
                            <?php 
                            if ($query_riwayat->num_rows > 0) {
                                while($row = $query_riwayat->fetch_assoc()): 
                                    // Format Tanggal
                                    $tgl_format = date('d M Y', strtotime($row['tanggal']));
                                    $hari = hariIndo($row['tanggal']);
                                    
                                    // Format Jam Masuk
                                    $jam_masuk = date('H:i', strtotime($row['waktu_masuk']));
                                    
                                    // Kalkulasi Jam Keluar & Total Jam
                                    if (!empty($row['waktu_keluar'])) {
                                        $jam_keluar = date('H:i', strtotime($row['waktu_keluar']));
                                        $selisih = strtotime($row['waktu_keluar']) - strtotime($row['waktu_masuk']);
                                        $total_jam = round($selisih / 3600, 1) . ' Jam';
                                    } else {
                                        $jam_keluar = '<span class="text-gray-400">Belum Checkout</span>';
                                        $total_jam = '-';
                                    }

                                    // Badge Status
                                    $badge_class = "bg-blue-100 text-blue-700"; // Default Hadir
                                    if ($row['status'] == 'Sakit') $badge_class = "bg-amber-100 text-amber-700";
                                    if ($row['status'] == 'Izin') $badge_class = "bg-rose-100 text-rose-700";
                            ?>
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-gray-800"><?php echo $tgl_format; ?></p>
                                        <p class="text-xs text-gray-400"><?php echo $hari; ?></p>
                                    </td>
                                    <td class="py-4 px-6 font-medium text-gray-800"><?php echo $jam_masuk; ?></td>
                                    <td class="py-4 px-6 font-medium text-gray-800"><?php echo $jam_keluar; ?></td>
                                    <td class="py-4 px-6 font-medium text-gray-800"><?php echo $total_jam; ?></td>
                                    <td class="py-4 px-6">
                                        <span class="py-1 px-3 rounded-full text-xs font-bold <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <button class="text-gray-400 hover:text-blue-600 transition">Detail</button>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            } else {
                            ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 font-medium">
                                        Belum ada data kehadiran.
                                    </td>
                                </tr>
                            <?php } ?>
                            
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-t border-gray-100 flex justify-between items-center text-sm text-gray-500">
                    <p>Total: <?php echo $query_riwayat->num_rows; ?> data absen</p>
                </div>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const closeSidebarBtn = document.getElementById('closeSidebarBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            const toggleSidebar = () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            };

            mobileMenuBtn.addEventListener('click', toggleSidebar);
            closeSidebarBtn.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);
        });
    </script>

</body>
</html>