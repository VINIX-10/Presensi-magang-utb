<?php
// Memanggil file otak backend (Logika, Query, dan Pengecekan Sesi) 
require 'proses/proses_dashboard.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi Magang</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="flex h-screen overflow-hidden text-gray-800 bg-[#F4F7FE]">

    <?php include 'components/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto w-full relative">

        <?php include 'components/topbar.php'; ?>

        <div class="p-4 md:p-8 space-y-6">

            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-sm border border-white">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                    <h2 class="text-lg font-bold text-gray-800">Detail Mahasiswa</h2>
                </div>

                <div class="flex flex-wrap md:flex-nowrap items-center gap-6">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_data['nama_user']); ?>&background=ebf4ff&color=2563eb&size=128" alt="Profile" class="w-20 h-20 rounded-full shadow-sm">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 md:gap-6 w-full text-sm">
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Nama Mahasiswa</p>
                            <p class="font-bold text-base text-gray-800"><?php echo htmlspecialchars($user_data['nama_user']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">NIM / Kelas</p>
                            <p class="font-bold text-base text-gray-800"><?php echo htmlspecialchars($user_data['nim']); ?> / <?php echo htmlspecialchars($user_data['kelas']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Konsentrasi</p>
                            <p class="font-bold text-base text-gray-800"><?php echo htmlspecialchars($user_data['konsentrasi']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Tempat Magang</p>
                            <p class="font-bold text-base text-gray-800"><?php echo htmlspecialchars($user_data['tempat_magang'] ?? 'Menara BRI Bandung'); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 font-medium mb-1">Periode Magang</p>
                            <p class="font-bold text-base text-gray-800">8 Jul - 8 Okt 2026</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-blue-500 text-white rounded-3xl p-6 shadow-lg shadow-blue-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-blue-100 font-medium text-sm mb-1">Hadir & Lembur</p>
                        <h3 class="text-4xl font-bold"><?php echo $stat_hadir; ?> <span class="text-lg font-medium text-blue-200">Hari</span></h3>
                    </div>
                </div>
                <div class="bg-emerald-400 text-white rounded-3xl p-6 shadow-lg shadow-emerald-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-emerald-100 font-medium text-sm mb-1">Kehadiran Terlambat</p>
                        <h3 class="text-4xl font-bold"><?php echo $stat_terlambat; ?> <span class="text-lg font-medium text-emerald-100">Hari</span></h3>
                    </div>
                </div>
                <div class="bg-amber-400 text-white rounded-3xl p-6 shadow-lg shadow-amber-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-amber-100 font-medium text-sm mb-1">Izin / Sakit</p>
                        <h3 class="text-4xl font-bold"><?php echo $stat_izin; ?> <span class="text-lg font-medium text-amber-100">Hari</span></h3>
                    </div>
                </div>
                <div class="bg-rose-400 text-white rounded-3xl p-6 shadow-lg shadow-rose-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-rose-100 font-medium text-sm mb-1">Sisa Hari Kerja</p>
                        <h3 class="text-4xl font-bold" id="remainingDays">- <span class="text-lg font-medium text-rose-100">Hari</span></h3>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-sm border border-white lg:col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-gray-800">Grafik Kehadiran</h2>
                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold">2026</span>
                    </div>
                    <div class="h-64 relative w-full">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                    <h2 class="text-lg font-bold mb-6 text-gray-800">Aksi Hari Ini</h2>

                    <?php if (!$data_absen_hari_ini): ?>
                        <?php if ($is_weekend): ?>
                            <div class="text-center py-4">
                                <div class="w-16 h-16 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-2">Yeay, Akhir Pekan!</h3>
                                <p class="text-gray-500 text-sm mb-6">Hari ini libur magang. Selamat beristirahat dan nikmati waktumu.</p>
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" name="submit_lembur" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-purple-200 transition text-sm">
                                        Saya Ada Jadwal Lembur
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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

            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-sm border border-white">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-gray-800">Riwayat Kehadiran</h2>
                    <p class="text-sm text-gray-400">Daftar presensi dan catatan logbook Anda selama periode magang.</p>
                </div>

                <div class="overflow-x-auto w-full rounded-2xl border border-gray-100">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-100">
                                <th class="p-4 w-12 text-center">No</th>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4 hidden md:table-cell">Hari</th>
                                <th class="p-4">Jam Masuk</th>
                                <th class="p-4">Jam Keluar</th>
                                <th class="p-4 hidden sm:table-cell">Total Jam</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4">Logbook</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php
                            // RENDER DINAMIS DARI DATABASE LANGSUNG
                            $no = 1;
                            $query_riwayat = $conn->query("SELECT * FROM kehadiran WHERE user_id = '{$_SESSION['user_id']}' ORDER BY tanggal DESC");

                            if ($query_riwayat && $query_riwayat->num_rows > 0):
                                while ($row = $query_riwayat->fetch_assoc()):

                                    // Set Warna Status
                                    $status_color = 'bg-gray-100 text-gray-600';
                                    if ($row['status'] == 'Hadir' || $row['status'] == 'Lembur') $status_color = 'bg-emerald-50 text-emerald-600 font-medium';
                                    elseif ($row['status'] == 'Izin') $status_color = 'bg-amber-50 text-amber-600 font-medium';
                                    elseif ($row['status'] == 'Sakit') $status_color = 'bg-rose-50 text-rose-600 font-medium';

                                    // Format Hari Indonesia
                                    $hari_inggris = date('l', strtotime($row['tanggal']));
                                    $daftar_hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                                    $hari_indo = $daftar_hari[$hari_inggris];

                                    // Perhitungan Total Jam
                                    $jam_masuk = $row['waktu_masuk'] ? date('H:i', strtotime($row['waktu_masuk'])) : '-';
                                    $jam_keluar = $row['waktu_keluar'] ? date('H:i', strtotime($row['waktu_keluar'])) : '-';
                                    $total_jam = '-';
                                    if ($row['waktu_masuk'] && $row['waktu_keluar']) {
                                        $diff = strtotime($row['waktu_keluar']) - strtotime($row['waktu_masuk']);
                                        $total_jam = round($diff / 3600, 1) . ' Jam';
                                    }

                                    // Filter Catatan Kosong
                                    $catatan = empty($row['catatan']) ? '-' : htmlspecialchars($row['catatan']);
                            ?>
                                    <tr class="hover:bg-gray-50/50 transition border-b border-gray-50 text-sm text-gray-700">
                                        <td class="p-4 w-12 text-center text-gray-400"><?php echo $no++; ?></td>
                                        <td class="p-4 font-medium"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                        <td class="p-4 hidden md:table-cell text-gray-500"><?php echo $hari_indo; ?></td>
                                        <td class="p-4 text-gray-600"><?php echo $jam_masuk; ?></td>
                                        <td class="p-4 text-gray-600"><?php echo $jam_keluar; ?></td>
                                        <td class="p-4 hidden sm:table-cell font-medium text-gray-700"><?php echo $total_jam; ?></td>
                                        <td class="p-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs <?php echo $status_color; ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-gray-500 truncate max-w-[150px] md:max-w-xs">
                                            <?php if ($catatan != '-'): ?>
                                                <span class="inline-flex items-center gap-1 text-gray-700">📝 <?php echo $catatan; ?></span>
                                            <?php else: ?>
                                                <span class="text-gray-300">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <button onclick="openModalLogbook('<?php echo $row['id']; ?>', '<?php echo date('d M Y', strtotime($row['tanggal'])); ?>', '<?php echo $row['status']; ?>', '<?php echo addslashes($catatan); ?>')" class="text-blue-600 hover:text-blue-800 font-semibold text-xs bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg transition">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400 font-medium">Belum ada riwayat kehadiran bulan ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <div id="logbookModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Detail Kehadiran (Logbook)</h3>
                <button onclick="closeModalLogbook()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="" class="p-6 space-y-4">
                <!-- INJEKSI CSRF DAN ID KEHADIRAN -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="id_kehadiran" id="modalIdInput">

                <div class="grid grid-cols-2 gap-4 text-xs font-semibold text-gray-500">
                    <div class="bg-gray-50 p-3 rounded-xl">
                        <span class="text-gray-400 block mb-0.5 font-medium">Tanggal</span>
                        <span id="modalTanggalText" class="text-sm text-gray-800 font-bold">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl">
                        <span class="text-gray-400 block mb-0.5 font-medium">Status</span>
                        <span id="modalStatusText" class="text-sm px-2 py-0.5 rounded-full inline-block mt-0.5 font-bold">-</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Keterangan / Catatan / Alasan <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                    <textarea name="catatan" id="modalCatatanInput" rows="4" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400 text-gray-700 resize-none" placeholder="Tuliskan tugas, kendala, atau alasan ketidakhadiran..."></textarea>
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        Catatan ini akan disimpan ke database dan dimasukkan ke file CSV.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                    <button type="button" onclick="closeModalLogbook()" class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit" name="simpan_catatan" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-blue-200 transition text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dataKehadiranBulanan = <?php echo isset($json_grafik_hadir) ? $json_grafik_hadir : '[0,0,0,0,0,0,0,0,0,0,0,0]'; ?>;
    </script>

    <!-- Cache Busting agar update script.js langsung terbaca -->
    <script src="assets/script.js?v=<?php echo time(); ?>"></script>

    <script>
        // --- 1. SCRIPT SIDEBAR MOBILE (HAMBURGER MENU) ---
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const closeSidebarBtn = document.getElementById('closeSidebarBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            const toggleSidebar = () => {
                if (sidebar && sidebarOverlay) {
                    sidebar.classList.toggle('-translate-x-full');
                    sidebarOverlay.classList.toggle('hidden');
                }
            };

            if (mobileMenuBtn && closeSidebarBtn && sidebarOverlay) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
                closeSidebarBtn.addEventListener('click', toggleSidebar);
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
        });

        // --- 2. SCRIPT MODAL LOGBOOK ---
        function openModalLogbook(id, tanggal, status, catatan) {
            const modal = document.getElementById('logbookModal');
            const content = document.getElementById('modalContent');

            // Masukkan ID ke form hidden
            document.getElementById('modalIdInput').value = id;
            document.getElementById('modalTanggalText').innerText = tanggal;

            const statusText = document.getElementById('modalStatusText');
            statusText.innerText = status;
            statusText.className = "text-sm px-2 py-0.5 rounded-full inline-block mt-0.5 font-bold ";
            if (status === 'Hadir' || status === 'Lembur') statusText.className += "bg-emerald-50 text-emerald-600";
            else if (status === 'Izin') statusText.className += "bg-amber-50 text-amber-600";
            else statusText.className += "bg-rose-50 text-rose-600";

            document.getElementById('modalCatatanInput').value = catatan === '-' ? '' : catatan;

            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModalLogbook() {
            const modal = document.getElementById('logbookModal');
            const content = document.getElementById('modalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>

    <?php include 'components/alert.php'; ?>

</body>

</html>