<?php require 'proses/proses_time_management.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Management - Absensi Magang</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex h-screen overflow-hidden text-gray-800 bg-[#F4F7FE]">

    <?php include 'components/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto w-full relative">
        <?php include 'components/topbar.php'; ?>

        <div class="p-4 md:p-8 space-y-6">
            <div class="flex flex-wrap md:flex-nowrap justify-between items-center gap-4 mb-2">
                <div class="flex gap-3 w-full md:w-auto">
                    <select class="flex-1 md:flex-none bg-white border border-gray-200 rounded-lg px-4 py-2 text-sm font-medium focus:outline-none shadow-sm">
                        <option>Semua Bulan</option>
                        <option>Juli 2026</option>
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
                                while ($row = $query_riwayat->fetch_assoc()):
                                    $tgl_format = date('d M Y', strtotime($row['tanggal']));
                                    $hari = hariIndo($row['tanggal']);

                                    // Logika Pintar untuk Hadir & Lembur vs Sakit & Izin
                                    if (in_array($row['status'], ['Hadir', 'Lembur'])) {
                                        $jam_masuk = date('H:i', strtotime($row['waktu_masuk']));
                                        if (!empty($row['waktu_keluar'])) {
                                            $jam_keluar_text = date('H:i', strtotime($row['waktu_keluar']));
                                            $selisih = strtotime($row['waktu_keluar']) - strtotime($row['waktu_masuk']);
                                            $total_jam = round($selisih / 3600, 1) . ' Jam';
                                        } else {
                                            $jam_keluar_text = '<span class="text-gray-400 italic">Belum Checkout</span>';
                                            $total_jam = '-';
                                        }
                                    } else {
                                        $jam_masuk = date('H:i', strtotime($row['waktu_masuk']));
                                        $jam_keluar_text = '<span class="text-gray-300 font-bold">-</span>';
                                        $total_jam = '<span class="text-gray-300 font-bold">-</span>';
                                    }

                                    // Sistem Pewarnaan Badge Status
                                    $badge_class = "bg-blue-100 text-blue-700";
                                    if ($row['status'] == 'Lembur') $badge_class = "bg-purple-100 text-purple-700";
                                    if ($row['status'] == 'Sakit') $badge_class = "bg-amber-100 text-amber-700";
                                    if ($row['status'] == 'Izin') $badge_class = "bg-rose-100 text-rose-700";
                            ?>
                                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                        <td class="py-4 px-6">
                                            <p class="font-bold text-gray-800"><?php echo $tgl_format; ?></p>
                                            <p class="text-xs text-gray-400"><?php echo $hari; ?></p>
                                        </td>
                                        <td class="py-4 px-6 font-medium text-gray-800"><?php echo $jam_masuk; ?></td>
                                        <td class="py-4 px-6 font-medium text-gray-800"><?php echo $jam_keluar_text; ?></td>
                                        <td class="py-4 px-6 font-medium text-gray-800"><?php echo $total_jam; ?></td>
                                        <td class="py-4 px-6">
                                            <span class="py-1 px-3 rounded-full text-xs font-bold <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <button onclick="bukaModalLogbook(
                                            '<?php echo $row['id']; ?>', 
                                            '<?php echo $tgl_format; ?> (<?php echo $hari; ?>)', 
                                            '<?php echo $jam_masuk; ?>', 
                                            '<?php echo (!empty($row['waktu_keluar']) || !in_array($row['status'], ['Hadir', 'Lembur'])) ? strip_tags($jam_keluar_text) : '-'; ?>', 
                                            '<?php echo htmlspecialchars($row['catatan'] ?? ''); ?>'
                                        )" class="bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold py-1.5 px-4 rounded-xl transition">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 font-medium">Belum ada data kehadiran.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100 text-sm text-gray-500">
                    <p>Total: <?php echo $query_riwayat->num_rows; ?> data absen</p>
                </div>
            </div>
        </div>
    </main>

    <div id="logbookModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 p-4 backdrop-blur-sm transition-all">
        <div class="bg-white rounded-3xl w-full max-w-lg p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-300" id="modalBox">

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Detail & Logbook Harian</h3>
                <button onclick="tutupModalLogbook()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-3 gap-2 bg-gray-50 p-4 rounded-2xl text-center text-xs font-semibold mb-6">
                <div><span class="text-gray-400 block mb-0.5">Tanggal</span><span id="md-tanggal" class="text-gray-800 font-bold">-</span></div>
                <div><span class="text-gray-400 block mb-0.5">Jam Masuk</span><span id="md-masuk" class="text-gray-700 font-bold">-</span></div>
                <div><span class="text-gray-400 block mb-0.5">Jam Keluar</span><span id="md-keluar" class="text-gray-700 font-bold">-</span></div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="id_kehadiran" id="md-id">
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-500 mb-2">Catatan Aktivitas / Kegiatan Magang</label>
                    <textarea name="catatan_kerja" id="md-catatan" rows="4" placeholder="Tuliskan tugas atau kendala yang kamu kerjakan hari ini..." class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400 resize-none font-medium text-gray-700"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="tutupModalLogbook()" class="w-1/3 border border-gray-200 hover:bg-gray-50 text-gray-500 font-bold py-3 rounded-xl transition text-sm">
                        Batal
                    </button>
                    <button type="submit" name="simpan_logbook" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-100 transition text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

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

            if (mobileMenuBtn && closeSidebarBtn && sidebarOverlay) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
                closeSidebarBtn.addEventListener('click', toggleSidebar);
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
        });

        const modal = document.getElementById('logbookModal');
        const modalBox = document.getElementById('modalBox');

        function bukaModalLogbook(id, tanggal, masuk, keluar, catatan) {
            document.getElementById('md-id').value = id;
            document.getElementById('md-tanggal').innerText = tanggal;
            document.getElementById('md-masuk').innerText = masuk;
            document.getElementById('md-keluar').innerText = keluar;
            document.getElementById('md-catatan').value = catatan;

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalBox.classList.remove('scale-95', 'opacity-0');
                modalBox.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function tutupModalLogbook() {
            modalBox.classList.remove('scale-100', 'opacity-100');
            modalBox.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }
    </script>

    <?php include 'components/alert.php'; ?>
</body>

</html>