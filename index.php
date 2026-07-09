<?php
session_start();
require 'koneksi.php'; // Memanggil koneksi database

// 1. Cek apakah user sudah login
if (!isset($_SESSION['nama_user'])) {
    header("Location: login.php"); // Jika belum, paksa pindah ke login
    exit;
}

// 2. Cek Timeout (15 Menit = 900 Detik)
$timeout_duration = 900; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php"); // Jika lebih dari 15 menit, paksa login ulang
    exit;
}

// 3. Perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();

// 4. Ambil data User dari Database berdasarkan Session
$user_id = $_SESSION['user_id'];
$query_user = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $query_user->fetch_assoc();

// 5. Proses saat tombol Submit Attendance ditekan
$pesan_alert = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_absen'])) {
    $status = $_POST['status'];
    $waktu_masuk = $_POST['waktu_masuk'];
    $tanggal = !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
    
    // Cek apakah user sudah absen hari ini untuk mencegah absen ganda
    $cek_absen = $conn->query("SELECT id FROM kehadiran WHERE user_id = '$user_id' AND tanggal = '$tanggal'");
    
    if ($cek_absen->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO kehadiran (user_id, status, waktu_masuk, tanggal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $status, $waktu_masuk, $tanggal);
        if($stmt->execute()) {
            $pesan_alert = "Absensi berhasil dicatat!";
        } else {
            $pesan_alert = "Gagal menyimpan absensi!";
        }
    } else {
        $pesan_alert = "Anda sudah melakukan absensi hari ini!";
    }
}

// 6. Hitung Statistik Absensi untuk Kartu Dashboard
$stat_hadir = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status = 'Hadir'")->fetch_assoc()['total'];
$stat_izin = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status IN ('Sakit', 'Izin')")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi Magang</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-600 rounded-xl font-semibold transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="time-management.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition">
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
        <header class="bg-white/80 backdrop-blur-md px-8 py-4 flex justify-between items-center sticky top-0 z-10">
            <div class="relative w-96">
                <input type="text" placeholder="Search." class="w-full bg-gray-100 rounded-full py-2.5 pl-12 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
           <div class="flex items-center gap-4">
                <a href="export_excel.php" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2 px-5 rounded-full flex items-center gap-2 shadow-sm transition">
                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                 Download Rekap
                 </a>
            </div>
        </header>

        <div class="p-8 space-y-6">
            
            <div class="glass-card rounded-3xl p-6 shadow-sm border border-white">
                <h2 class="text-lg font-bold mb-5">Student Details</h2>
                <div class="flex flex-wrap md:flex-nowrap items-center gap-6">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_data['nama_user']); ?>&background=ebf4ff&color=2563eb&size=128" alt="Profile" class="w-20 h-20 rounded-full shadow-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 w-full text-sm">
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Nama Mahasiswa</p>
                            <p class="font-bold text-base"><?php echo htmlspecialchars($user_data['nama_user']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">NIM / Kelas</p>
                            <p class="font-bold text-base"><?php echo htmlspecialchars($user_data['nim']); ?> / <?php echo htmlspecialchars($user_data['kelas']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Konsentrasi</p>
                            <p class="font-bold text-base">Computer and Network Security</p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Periode Magang</p>
                            <p class="font-bold text-base">8 Jul - 8 Okt 2026</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-blue-500 text-white rounded-3xl p-6 shadow-lg shadow-blue-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-blue-100 font-medium text-sm mb-1">Total Attendance</p>
                        <h3 class="text-4xl font-bold"><?php echo $stat_hadir; ?> <span class="text-lg font-medium text-blue-200">Days</span></h3>
                    </div>
                </div>
                
                <div class="bg-emerald-400 text-white rounded-3xl p-6 shadow-lg shadow-emerald-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-emerald-100 font-medium text-sm mb-1">Late Attendance</p>
                        <h3 class="text-4xl font-bold">0 <span class="text-lg font-medium text-emerald-100">Days</span></h3>
                    </div>
                </div>

                <div class="bg-amber-400 text-white rounded-3xl p-6 shadow-lg shadow-amber-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-amber-100 font-medium text-sm mb-1">Permit / Sick</p>
                        <h3 class="text-4xl font-bold"><?php echo $stat_izin; ?> <span class="text-lg font-medium text-amber-100">Days</span></h3>
                    </div>
                </div>

                <div class="bg-rose-400 text-white rounded-3xl p-6 shadow-lg shadow-rose-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-rose-100 font-medium text-sm mb-1">Remaining Days</p>
                        <h3 class="text-4xl font-bold" id="remainingDays">- <span class="text-lg font-medium text-rose-100">Days</span></h3>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="glass-card rounded-3xl p-6 shadow-sm border border-white lg:col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold">Monthly Rate</h2>
                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold">2026</span>
                    </div>
                    <div class="h-64">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                    <form method="POST" action="">
                        <div>
                            <h2 class="text-lg font-bold mb-6">Action Today</h2>
                             <div class="space-y-4">
                                <div>
                             <label class="block text-sm font-medium text-gray-500 mb-2">Tanggal</label>
                             <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 text-gray-500" readonly>
                                </div>
            
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-2">Status</label>
                                    <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                    </select>
                                </div>
            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Waktu Masuk</label>
                                <input type="time" name="waktu_masuk" value="<?php echo date('H:i'); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500" readonly>
                            </div>
                        </div>
                        </div>
                        <button type="submit" name="submit_absen" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-200 transition mt-6">
                        Submit Attendance
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="script.js"></script>

    <?php if(!empty($pesan_alert)): ?>
    <script>
        alert("<?php echo $pesan_alert; ?>");
    </script>
    <?php endif; ?>

</body>
</html>