<?php require 'proses/proses_time_management.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Aktivitas & Milestone - Absensi Magang</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex h-screen overflow-hidden text-gray-800 bg-[#F4F7FE]">

    <?php include 'components/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto w-full relative">
        <?php include 'components/topbar.php'; ?>

        <div class="p-4 md:p-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Kalender Aktivitas & Milestone Magang</h1>
                    <p class="text-sm text-gray-400">Sinkronisasi target bulanan industri dengan agenda akademik kampus Anda.</p>
                </div>
                
                <button onclick="bukaModalCRUD('create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-blue-200 transition text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Agenda Mandiri
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-sm border border-white lg:col-span-2">
                    
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-4">
                            <h2 class="text-xl font-bold text-gray-800"><?= $nama_bulan_indo[$bulan_aktif]; ?></h2>
                            <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold">Periode Aktif</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <select onchange="location = this.value;" class="bg-gray-50 border border-gray-200 text-sm font-semibold rounded-xl px-3 py-2 text-gray-700 focus:outline-none cursor-pointer">
                                <option value="time-management.php?bulan=07" <?= $bulan_aktif == '07' ? 'selected' : ''; ?>>Juli</option>
                                <option value="time-management.php?bulan=08" <?= $bulan_aktif == '08' ? 'selected' : ''; ?>>Agustus</option>
                                <option value="time-management.php?bulan=09" <?= $bulan_aktif == '09' ? 'selected' : ''; ?>>September</option>
                                <option value="time-management.php?bulan=10" <?= $bulan_aktif == '10' ? 'selected' : ''; ?>>Oktober</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-gray-400 mb-2">
                        <div class="py-2">SEN</div><div class="py-2">SEL</div><div class="py-2">RAB</div><div class="py-2">KAM</div><div class="py-2">JUM</div><div class="py-2 text-rose-400">SAB</div><div class="py-2 text-rose-400">MIN</div>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        
                        <?php for($i = 0; $i < $slot_kosong; $i++): ?>
                            <div class="min-h-[90px] bg-gray-50/50 rounded-2xl border border-dashed border-gray-100"></div>
                        <?php endfor; ?>

                        <?php for($d = 1; $d <= $total_hari; $d++): 
                            // Membuat format tanggal penuh YYYY-MM-DD untuk integrasi database / CRUD
                            $tanggal_format = sprintf('%04d-%02d-%02d', $tahun_aktif, $bulan_aktif, $d);
                            
                            // Contoh Integrasi Tampilan Agenda Khusus Sampel Bulan Juli
                            if ($bulan_aktif == '07' && $d == 8): ?>
                                <div class="min-h-[90px] bg-white border-2 border-emerald-400 shadow-sm rounded-2xl p-2 flex flex-col justify-between cursor-pointer hover:shadow transition" 
                                     onclick="bukaModalCRUD('edit', '1', 'Onboarding Industri', 'Industri', '2026-07-08', 'Hari Pertama Magang & Onboarding Perusahaan')">
                                    <span class="font-bold text-sm text-emerald-600">8</span>
                                    <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold py-1 px-1.5 rounded-lg block truncate">🏢 Onboarding</span>
                                </div>
                            <?php elseif ($bulan_aktif == '07' && $d == 10): ?>
                                <div class="min-h-[90px] bg-white border-2 border-blue-400 shadow-sm rounded-2xl p-2 flex flex-col justify-between cursor-pointer hover:shadow transition" 
                                     onclick="bukaModalCRUD('edit', '2', 'Sosialisasi Kampus', 'Kampus', '2026-07-10', 'Acara sosialisasi mahasiswa magang kampus')">
                                    <span class="font-bold text-sm text-blue-600">10</span>
                                    <span class="bg-blue-50 text-blue-700 text-[10px] font-bold py-1 px-1.5 rounded-lg block truncate">🎓 Sosialisasi</span>
                                </div>
                            <?php else: ?>
                                <div class="min-h-[90px] bg-white border border-gray-100 rounded-2xl p-2 relative flex flex-col justify-between hover:border-blue-300 transition cursor-pointer"
                                     onclick="bukaModalCRUD('create', '', '', 'Industri', '<?= $tanggal_format; ?>', '')">
                                    <span class="font-bold text-sm text-gray-400"><?= $d; ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-lg font-bold text-gray-800 mb-6">Target Milestone Magang</h2>
    
    <div class="relative pl-6 border-l-2 border-gray-100 space-y-8">
        
        <div class="relative">
            <span class="absolute -left-[33px] top-0 bg-emerald-500 text-white p-1 rounded-full border-4 border-white">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </span>
            <h3 class="font-bold text-sm text-gray-800">Milestone 1 (Juli)</h3>
            <p class="text-xs text-emerald-600 font-semibold mb-1">Status: Selesai</p>
            <div class="text-xs text-gray-500 space-y-1 bg-gray-50 p-2.5 rounded-xl mt-1">
                <p><strong>Operasional:</strong> Penginputan & rekapitulasi data harian finansial (Tabungan, Giro, Depo) Uker Sumedang ke Excel.</p>
                <p><strong>Inisiatif IT:</strong> Analisis kelemahan sistem absen fisik pemagang & perancangan basis data Tracker.</p>
            </div>
        </div>

        <div class="relative">
            <span class="absolute -left-[30px] top-0 bg-blue-600 w-4 h-4 rounded-full border-4 border-white animate-pulse"></span>
            <h3 class="font-bold text-sm text-gray-800">Milestone 2 (Agustus)</h3>
            <p class="text-xs text-blue-600 font-semibold mb-1">Status: Berjalan</p>
            <div class="text-xs text-gray-500 space-y-1 bg-gray-50 p-2.5 rounded-xl mt-1">
                <p><strong>Operasional:</strong> Monitoring akuisisi produk digital (Brimo, Qlola, QRIS) dan validasi leads Brispot.</p>
                <p><strong>Inisiatif IT:</strong> Desain UI/UX dashboard desktop serta sinkronisasi penataan kolom logbook agar sesuai output Excel.</p>
            </div>
        </div>

        <div class="relative">
            <span class="absolute -left-[30px] top-0 bg-gray-200 w-4 h-4 rounded-full border-4 border-white"></span>
            <h3 class="font-bold text-sm text-gray-400">Milestone 3 (September)</h3>
            <p class="text-xs text-gray-400 font-semibold mb-1">Status: Pending</p>
            <div class="text-xs text-gray-400 space-y-1 bg-gray-50 p-2.5 rounded-xl mt-1">
                <p><strong>Operasional:</strong> Evaluasi berkala alokasi Dana Talangan Brilink dan volume transaksi Uker.</p>
                <p><strong>Inisiatif IT:</strong> Implementasi koding CRUD agenda mandiri kalender dan pengujian fungsi unduh file rekapitulasi.</p>
            </div>
        </div>

        <div class="relative">
            <span class="absolute -left-[30px] top-0 bg-gray-200 w-4 h-4 rounded-full border-4 border-white"></span>
            <h3 class="font-bold text-sm text-gray-400">Milestone 4 (Oktober)</h3>
            <p class="text-xs text-gray-400 font-semibold mb-1">Status: Pending</p>
            <div class="text-xs text-gray-400 space-y-1 bg-gray-50 p-2.5 rounded-xl mt-1">
                <p><strong>Operasional:</strong> Penyerahan rekapitulasi performa retail funding triwulan kepada pimpinan unit.</p>
                <p><strong>Inisiatif IT:</strong> Penarikan final data CSV absensi sebagai lampiran sah Buku Laporan Magang Kampus.</p>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </main>

    <div id="crudModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 p-4 backdrop-blur-sm transition-all">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-300" id="modalBox">
            
            <div class="flex justify-between items-center mb-5">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Kelola Agenda Mandiri</h3>
                <button onclick="tutupModalCRUD()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="" id="formAgenda">
                <input type="hidden" name="action" id="actionInput" value="create">
                <input type="hidden" name="id_agenda" id="idAgendaInput" value="">

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nama / Judul Agenda</label>
                        <input type="text" name="judul_agenda" id="in-judul" required placeholder="Contoh: Pembuatan Wireframe" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Kategori</label>
                            <select name="kategori" id="in-kategori" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium">
                                <option value="Industri">Industri</option>
                                <option value="Kampus">Kampus</option>
                                <option value="Lembur">Lembur</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Tanggal</label>
                            <input type="date" name="tanggal_agenda" id="in-tanggal" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Deskripsi Kegiatan</label>
                        <textarea name="deskripsi_agenda" id="in-deskripsi" rows="3" placeholder="Tuliskan detail deskripsi kegiatan agenda..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400 resize-none text-gray-700 font-medium"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-50">
                    <button type="submit" name="hapus_agenda" id="btnDelete" class="hidden bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold py-2.5 px-4 rounded-xl transition text-sm">
                        Hapus
                    </button>
                    
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" onclick="tutupModalCRUD()" class="border border-gray-200 hover:bg-gray-50 text-gray-500 font-bold py-2.5 px-4 rounded-xl transition text-sm">
                            Batal
                        </button>
                        <button type="submit" name="simpan_agenda" id="btnSubmit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-blue-100 transition text-sm">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('crudModal');
        const modalBox = document.getElementById('modalBox');

        function bukaModalCRUD(mode, id = '', judul = '', kategori = 'Industri', tanggal = '', deskripsi = '') {
            document.getElementById('actionInput').value = mode;
            document.getElementById('idAgendaInput').value = id;
            document.getElementById('in-judul').value = judul;
            document.getElementById('in-kategori').value = kategori;
            document.getElementById('in-tanggal').value = tanggal;
            document.getElementById('in-deskripsi').value = deskripsi;

            const title = document.getElementById('modalTitle');
            const btnDelete = document.getElementById('btnDelete');
            const btnSubmit = document.getElementById('btnSubmit');

            if (mode === 'create') {
                title.innerText = "Tambah Agenda Mandiri";
                btnDelete.classList.add('hidden');
                btnSubmit.innerText = "Simpan Agenda";
            } else if (mode === 'edit') {
                title.innerText = "Edit / Ubah Agenda";
                btnDelete.classList.remove('hidden');
                btnSubmit.innerText = "Simpan Perubahan";
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalBox.classList.remove('scale-95', 'opacity-0');
                modalBox.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function tutupModalCRUD() {
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