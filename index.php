<?php
session_start();
require 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['nama_user'])) {
    header("Location: login.php");
    exit;
}

$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();

$user_id = $_SESSION['user_id'];
$query_user = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user_data = $query_user->fetch_assoc();

$pesan_alert = "";
$tanggal_hari_ini = date('Y-m-d');
$waktu_sekarang = date('H:i:s');

// LOGIKA DETEKSI HARI LIBUR (1 = Senin ... 6 = Sabtu, 7 = Minggu)
$hari_ini_angka = date('N');
$is_weekend = ($hari_ini_angka >= 6) ? true : false;

// PROSES SUBMIT ABSEN (MASUK / PULANG / LEMBUR)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Jika Submit Standar ATAU Submit Lembur
    if (isset($_POST['submit_masuk']) || isset($_POST['submit_lembur'])) {
        $status = isset($_POST['submit_lembur']) ? 'Lembur' : $_POST['status'];

        $cek_absen = $conn->query("SELECT id FROM kehadiran WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
        if ($cek_absen->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO kehadiran (user_id, tanggal, waktu_masuk, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $tanggal_hari_ini, $waktu_sekarang, $status);
            if ($stmt->execute()) {
                $pesan_alert = ($status == 'Lembur') ? "Selamat lembur! Semangat kerjanya." : "Absen MASUK berhasil dicatat!";
            } else {
                $pesan_alert = "Gagal menyimpan absensi masuk!";
            }
        }
    } elseif (isset($_POST['submit_pulang'])) {
        $update = $conn->query("UPDATE kehadiran SET waktu_keluar = '$waktu_sekarang' WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
        if ($update) {
            $pesan_alert = "Absen PULANG berhasil dicatat! Selamat beristirahat.";
        }
    }
}

// CEK STATUS ABSENSI HARI INI
$query_absen_hari_ini = $conn->query("SELECT * FROM kehadiran WHERE user_id = '$user_id' AND tanggal = '$tanggal_hari_ini'");
$data_absen_hari_ini = $query_absen_hari_ini->fetch_assoc();

// STATISTIK KARTU (Hadir & Lembur dihitung sebagai Total Attendance)
$stat_hadir = $conn->query("SELECT COUNT(id) as total FROM kehadiran WHERE user_id = '$user_id' AND status IN ('Hadir', 'Lembur')")->fetch_assoc()['total'];
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

<body class="flex h-screen overflow-hidden text-gray-800 bg-[#F4F7FE]">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto w-full relative">
        
        <?php include 'topbar.php'; ?>

        <div class="p-4 md:p-8 space-y-6">

            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-sm border border-white">
                <h2 class="text-lg font-bold mb-5">Student Details</h2>
                <div class="flex flex-wrap md:flex-nowrap items-center gap-6">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_data['nama_user']); ?>&background=ebf4ff&color=2563eb&size=128" alt="Profile" class="w-20 h-20 rounded-full shadow-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 w-full text-sm">
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
                            <p class="font-bold text-base"><?php echo htmlspecialchars($user_data['konsentrasi']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Periode Magang</p>
                            <p class="font-bold text-base">8 Jul - 8 Okt 2026</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6">
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-sm border border-white lg:col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold">Monthly Rate</h2>
                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold">2026</span>
                    </div>
                    <div class="h-64 relative w-full">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                    <h2 class="text-lg font-bold mb-6">Action Today</h2>

                    <?php if (!$data_absen_hari_ini): ?>
                        
                        <?php if ($is_weekend): ?>
                            <div class="text-center py-4">
                                <div class="w-16 h-16 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-2">Yeay, Akhir Pekan!</h3>
                                <p class="text-gray-500 text-sm mb-6">Hari ini libur magang. Selamat beristirahat dan nikmati waktumu.</p>
                                
                                <form method="POST" action="">
                                    <button type="submit" name="submit_lembur" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-purple-200 transition text-sm">
                                        Saya Ada Jadwal Lembur
                                    </button>
                                </form>
                            </div>

                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-2">Tanggal</label>
                                        <input type="text" value="<?php echo date('d M Y'); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-500" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-2">Status</label>
                                        <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                                            <option value="Hadir">Hadir</option>
                                            <option value="Sakit">Sakit</option>
                                            <option value="Izin">Izin</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" name="submit_masuk" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-200 transition mt-6">
                                    Kirim Laporan Hari Ini
                                </button>
                            </form>
                        <?php endif; ?>

                    <?php elseif (empty($data_absen_hari_ini['waktu_keluar']) && in_array($data_absen_hari_ini['status'], ['Hadir', 'Lembur'])): ?>
                        <div class="text-center py-4">
                            <?php $tema_warna = ($data_absen_hari_ini['status'] == 'Lembur') ? 'purple' : 'blue'; ?>
                            
                            <div class="w-16 h-16 bg-<?php echo $tema_warna; ?>-50 text-<?php echo $tema_warna; ?>-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">Kamu sudah absen <?php echo strtolower($data_absen_hari_ini['status']); ?> pada:</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-1 mb-6"><?php echo date('H:i', strtotime($data_absen_hari_ini['waktu_masuk'])); ?></h3>

                            <form method="POST" action="">
                                <button type="submit" name="submit_pulang" class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-rose-200 transition">
                                    Absen Pulang Sekarang
                                </button>
                            </form>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-6">
                            <?php if (in_array($data_absen_hari_ini['status'], ['Hadir', 'Lembur'])): ?>
                                <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Tugas Selesai!</h3>
                                <p class="text-gray-500 text-sm">Kamu sudah menyelesaikan absensi hari ini.</p>
                                <div class="mt-6 flex justify-center gap-4 text-sm font-semibold">
                                    <div class="bg-gray-50 px-4 py-2 rounded-lg"><span class="text-gray-400 block text-xs">Masuk</span><?php echo date('H:i', strtotime($data_absen_hari_ini['waktu_masuk'])); ?></div>
                                    <div class="bg-gray-50 px-4 py-2 rounded-lg"><span class="text-gray-400 block text-xs">Pulang</span><?php echo date('H:i', strtotime($data_absen_hari_ini['waktu_keluar'])); ?></div>
                                </div>
                            <?php else: ?>
                                <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Status: <?php echo $data_absen_hari_ini['status']; ?></h3>
                                <p class="text-gray-500 text-sm">Laporan ketidakhadiranmu hari ini sudah tercatat.</p>
                                <div class="mt-6 inline-block bg-gray-50 px-4 py-2 rounded-lg text-sm font-semibold">
                                    <span class="text-gray-400 block text-xs">Waktu Lapor</span>
                                    <?php echo date('H:i', strtotime($data_absen_hari_ini['waktu_masuk'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <script src="script.js"></script>
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

            if(mobileMenuBtn && closeSidebarBtn && sidebarOverlay) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
                closeSidebarBtn.addEventListener('click', toggleSidebar);
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>

    <?php if (!empty($pesan_alert)): ?>
        <script>alert("<?php echo $pesan_alert; ?>");</script>
    <?php endif; ?>

</body>
</html>