<?php
session_start();

// 1. Cek apakah user sudah login
if (!isset($_SESSION['nama_user'])) {
    header("Location: login.php"); // Jika belum, paksa pindah ke login
    exit;
}

// 2. Cek Timeout (15 Menit = 900 Detik)
$timeout_duration = 20; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php"); // Jika lebih dari 15 menit, paksa login ulang
    exit;
}

// 3. Perbarui waktu aktivitas terakhir agar 15 menit terhitung dari aksi terbaru
$_SESSION['last_activity'] = time();
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
<body class="flex h-screen overflow-hidden text-gray-800">

    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col justify-between hidden md:flex">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-10 text-blue-600">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                <h1 class="text-xl font-bold tracking-wide">UTB Tracker</h1>
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

    <main class="flex-1 flex flex-col overflow-y-auto w-full">
        <header class="bg-white/80 backdrop-blur-md px-8 py-4 flex justify-between items-center sticky top-0 z-10 border-b border-gray-100">
            <div class="relative w-96">
                <h2 class="text-xl font-bold text-gray-800">Riwayat Kehadiran</h2>
            </div>
            <div class="flex items-center gap-4">
                <button class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2 px-5 rounded-full flex items-center gap-2 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Log
                </button>
            </div>
        </header>

        <div class="p-8 space-y-6">
            
            <div class="flex flex-wrap md:flex-nowrap justify-between items-center gap-4 mb-2">
                <div class="flex gap-3">
                    <select class="bg-white border border-gray-200 rounded-lg px-4 py-2 text-sm font-medium focus:outline-none focus:border-blue-500 shadow-sm">
                        <option>Semua Bulan</option>
                        <option>Juli 2026</option>
                        <option>Agustus 2026</option>
                    </select>
                    <select class="bg-white border border-gray-200 rounded-lg px-4 py-2 text-sm font-medium focus:outline-none focus:border-blue-500 shadow-sm">
                        <option>Semua Status</option>
                        <option>Hadir</option>
                        <option>Sakit/Izin</option>
                        <option>Alpa</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
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
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-gray-800">09 Jul 2026</p>
                                    <p class="text-xs text-gray-400">Kamis</p>
                                </td>
                                <td class="py-4 px-6 font-medium text-gray-800">07:25</td>
                                <td class="py-4 px-6 text-gray-400 font-medium">Belum Checkout</td>
                                <td class="py-4 px-6 font-medium">-</td>
                                <td class="py-4 px-6">
                                    <span class="bg-blue-100 text-blue-700 py-1 px-3 rounded-full text-xs font-bold">Hadir</span>
                                </td>
                                <td class="py-4 px-6">
                                    <button class="text-gray-400 hover:text-blue-600 transition">Detail</button>
                                </td>
                            </tr>
                            
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-gray-800">08 Jul 2026</p>
                                    <p class="text-xs text-gray-400">Rabu</p>
                                </td>
                                <td class="py-4 px-6 font-medium text-gray-800">07:30</td>
                                <td class="py-4 px-6 font-medium text-gray-800">17:05</td>
                                <td class="py-4 px-6 font-medium text-gray-800">9.5 Jam</td>
                                <td class="py-4 px-6">
                                    <span class="bg-blue-100 text-blue-700 py-1 px-3 rounded-full text-xs font-bold">Hadir</span>
                                </td>
                                <td class="py-4 px-6">
                                    <button class="text-gray-400 hover:text-blue-600 transition">Detail</button>
                                </td>
                            </tr>

                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-gray-800">07 Jul 2026</p>
                                    <p class="text-xs text-gray-400">Selasa</p>
                                </td>
                                <td class="py-4 px-6 font-medium text-red-500">08:15 (Late)</td>
                                <td class="py-4 px-6 font-medium text-gray-800">17:00</td>
                                <td class="py-4 px-6 font-medium text-gray-800">8.75 Jam</td>
                                <td class="py-4 px-6">
                                    <span class="bg-emerald-100 text-emerald-700 py-1 px-3 rounded-full text-xs font-bold">Terlambat</span>
                                </td>
                                <td class="py-4 px-6">
                                    <button class="text-gray-400 hover:text-blue-600 transition">Detail</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-t border-gray-100 flex justify-between items-center text-sm text-gray-500">
                    <p>Menampilkan 1-3 dari 3 data</p>
                    <div class="flex gap-2">
                        <button class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50">Prev</button>
                        <button class="px-3 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded font-bold">1</button>
                        <button class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50">Next</button>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>