<?php 
// Memanggil otak backend hasil "wiretuck"
require 'proses/proses_time_management.php'; 

// =========================================================================
// SAFE FALLBACK: Mengamankan array dinamis milestone jika belum didefinisikan di backend
// =========================================================================
if (!isset($milestone_list)) {
    $milestone_list = [
        '07' => [
            'judul' => 'Milestone 1 (Juli)', 
            'status' => 'Selesai', 
            'operasional' => 'Penginputan & rekapitulasi data harian finansial Uker Sumedang ke Excel.', 
            'it' => 'Analisis kelemahan sistem absen fisik pemagang & perancangan basis data Tracker.'
        ],
        '08' => [
            'judul' => 'Milestone 2 (Agustus)', 
            'status' => 'Berjalan', 
            'operasional' => 'Monitoring akuisisi produk digital (Brimo, Qlola, QRIS) dan validasi leads Brispot.', 
            'it' => 'Desain UI/UX dashboard desktop serta sinkronisasi penataan kolom logbook agar sesuai output Excel.'
        ],
        '09' => [
            'judul' => 'Milestone 3 (September)', 
            'status' => 'Pending', 
            'operasional' => 'Evaluasi berkala alokasi Dana Talangan Brilink dan volume transaksi Uker.', 
            'it' => 'Implementasi koding CRUD agenda mandiri kalender dan pengujian fungsi unduh file rekapitulasi.'
        ],
        '10' => [
            'judul' => 'Milestone 4 (Oktober)', 
            'status' => 'Pending', 
            'operasional' => 'Penyusunan laporan akhir magang, dokumentasi kode program, serta serah terima sistem.', 
            'it' => 'Final deployment sistem absensi magang ke server produksi Laragon.'
        ]
    ];
}
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
                
                <button onclick="bukaModalCRUD('create', '', '', 'Industri', '', '08:00', '12', '')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-blue-200 transition text-sm flex items-center gap-2">
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
                            <h2 class="text-xl font-bold text-gray-800"><?= $nama_bulan_indo[$bulan_aktif] ?? 'Bulan'; ?></h2>
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
                            $tanggal_format = sprintf('%04d-%02d-%02d', $tahun_aktif, $bulan_aktif, $d);
                            
                            if (isset($agenda_list[$d])): 
                                $agenda = $agenda_list[$d];
                                
                                $warna = 'gray'; $icon = '📅';
                                if ($agenda['kategori'] == 'Industri') { $warna = 'emerald'; $icon = '💼'; }
                                elseif ($agenda['kategori'] == 'Campus') { $warna = 'blue'; $icon = '🎓'; }
                                elseif ($agenda['kategori'] == 'Lembur') { $warna = 'purple'; $icon = '⚡'; }

                                $jam_tampil = isset($agenda['waktu']) ? substr($agenda['waktu'], 0, 5) : '08:00';
                                $offset_tampil = $agenda['pengingat_offset'] ?? '12';
                        ?>
                            <div class="min-h-[90px] bg-white border-2 border-<?= $warna; ?>-400 shadow-sm rounded-2xl p-2 flex flex-col justify-between cursor-pointer hover:shadow transition" 
                                 onclick="bukaModalCRUD('edit', '<?= $agenda['id']; ?>', '<?= addslashes($agenda['judul']); ?>', '<?= $agenda['kategori']; ?>', '<?= $agenda['tanggal']; ?>', '<?= $jam_tampil; ?>', '<?= $offset_tampil; ?>', '<?= addslashes($agenda['deskripsi']); ?>')">
                                <span class="font-bold text-sm text-<?= $warna; ?>-600"><?= $d; ?></span>
                                <span class="bg-<?= $warna; ?>-50 text-<?= $warna; ?>-700 text-[10px] font-bold py-1 px-1.5 rounded-lg block truncate"><?= $icon; ?> <?= htmlspecialchars($agenda['judul']); ?></span>
                            </div>

                        <?php else: ?>
                            <div class="min-h-[90px] bg-white border border-gray-100 rounded-2xl p-2 relative flex flex-col justify-between hover:border-blue-300 transition cursor-pointer"
                                 onclick="bukaModalCRUD('create', '', '', 'Industri', '<?= $tanggal_format; ?>', '08:00', '12', '')">
                                <span class="font-bold text-sm text-gray-400"><?= $d; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-6">Target Milestone Magang</h2>
                    <div class="relative pl-6 border-l-2 border-gray-100 space-y-8">
                        
                        <?php foreach ($milestone_list as $bln_key => $ms): 
                            $tag_warna = 'gray';
                            if ($ms['status'] === 'Selesai') $tag_warna = 'emerald';
                            elseif ($ms['status'] === 'Berjalan') $tag_warna = 'blue';
                        ?>
                            <div class="relative group">
                                <?php if ($ms['status'] === 'Selesai'): ?>
                                    <span class="absolute -left-[33px] top-0 bg-emerald-500 text-white p-1 rounded-full border-4 border-white z-10">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </span>
                                <?php elseif ($ms['status'] === 'Berjalan'): ?>
                                    <span class="absolute -left-[30px] top-0 bg-blue-600 w-4 h-4 rounded-full border-4 border-white animate-pulse z-10"></span>
                                <?php else: ?>
                                    <span class="absolute -left-[30px] top-0 bg-gray-200 w-4 h-4 rounded-full border-4 border-white z-10"></span>
                                <?php endif; ?>

                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h3 class="font-bold text-sm <?= $ms['status'] === 'Pending' ? 'text-gray-400' : 'text-gray-800'; ?>"><?= htmlspecialchars($ms['judul']); ?></h3>
                                        <p class="text-xs text-<?= $tag_warna; ?>-600 font-semibold mb-1">Status: <?= htmlspecialchars($ms['status']); ?></p>
                                    </div>
                                    
                                    <button onclick="bukaModalMilestone('<?= $bln_key; ?>', '<?= addslashes($ms['judul']); ?>', '<?= $ms['status']; ?>', '<?= addslashes($ms['operasional']); ?>', '<?= addslashes($ms['it']); ?>')" 
                                            class="text-gray-400 hover:text-blue-600 p-2 rounded-xl hover:bg-gray-50 transition opacity-100 lg:opacity-0 lg:group-hover:opacity-100 focus:opacity-100" title="Ubah Milestone">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="text-xs <?= $ms['status'] === 'Pending' ? 'text-gray-400' : 'text-gray-500'; ?> space-y-1 bg-gray-50 p-2.5 rounded-xl mt-1">
                                    <p><strong>Operasional:</strong> <?= htmlspecialchars($ms['operasional']); ?></p>
                                    <p><strong>Inisiatif IT:</strong> <?= htmlspecialchars($ms['it']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- MODAL CRUD AGENDA MANDIRI -->
    <div id="crudModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 p-4 backdrop-blur-sm transition-all">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-300" id="modalBox">
            
            <div class="flex justify-between items-center mb-5">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Kelola Agenda Mandiri</h3>
                <button onclick="tutupModalCRUD()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="" id="formAgenda">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
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
                                <option value="Campaign">Kampus</option>
                                <option value="Lembur">Lembur</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Tanggal</label>
                            <input type="date" name="tanggal_agenda" id="in-tanggal" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium">
                        </div>
                    </div>

                    <!-- PENGATURAN JAM MULAI & JEDA OFFSET BARU -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Jam Mulai Agenda</label>
                            <input type="time" name="waktu_agenda" id="in-waktu" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Ingatkan Saya</label>
                            <select name="pengingat_offset" id="in-offset" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium">
                                <option value="1">1 Jam Sebelum Agenda</option>
                                <option value="12">12 Jam Sebelum (H-1)</option>
                                <option value="20">20 Jam Sebelum Simulasi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Deskripsi Kegiatan</label>
                        <textarea name="deskripsi_agenda" id="in-deskripsi" rows="3" placeholder="Tuliskan detail deskripsi kegiatan agenda..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400 resize-none text-gray-700 font-medium"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-50">
                    <button type="submit" name="hapus_agenda" id="btnDelete" class="hidden bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold py-2.5 px-4 rounded-xl transition text-sm" onclick="return confirm('Yakin ingin menghapus agenda ini?');">
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

    <!-- MODAL EDIT TARGET MILESTONE -->
    <div id="milestoneModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 p-4 backdrop-blur-sm transition-all">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-300" id="milestoneModalBox">
            
            <div class="flex justify-between items-center mb-5">
                <h3 id="milestoneModalTitle" class="text-lg font-bold text-gray-800">Ubah Target Milestone</h3>
                <button onclick="tutupModalMilestone()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="" id="formMilestone">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="bulan_key" id="ms-bulan-key" value="">

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Status Progres Milestone</label>
                        <select name="status_milestone" id="ms-status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium">
                            <option value="Pending">Pending</option>
                            <option value="Berjalan">Berjalan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Deskripsi Target Operasional / Finansial</label>
                        <textarea name="operasional_milestone" id="ms-operasional" rows="3" required placeholder="Contoh: Rekapitulasi target nominal Tabungan/Giro..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Deskripsi Target Pengembangan IT</label>
                        <textarea name="it_milestone" id="ms-it" rows="3" required placeholder="Contoh: Implementasi modul database & security..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:border-blue-500 text-gray-700 font-medium"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                    <button type="button" onclick="tutupModalMilestone()" class="border border-gray-200 hover:bg-gray-50 text-gray-500 font-bold py-2.5 px-4 rounded-xl transition text-sm">
                        Batal
                    </button>
                    <button type="submit" name="ubah_milestone" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-blue-100 transition text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // SCRIPT AGENDA MODAL CONTROLLER
        const modal = document.getElementById('crudModal');
        const modalBox = document.getElementById('modalBox');

        // SINKRONISASI PENGATURAN DEFAULT JAM MULAI & OFFSET
        function bukaModalCRUD(mode, id = '', judul = '', kategori = 'Industri', tanggal = '', waktu = '08:00', offset = '12', deskripsi = '') {
            document.getElementById('actionInput').value = mode;
            document.getElementById('idAgendaInput').value = id;
            document.getElementById('in-judul').value = judul;
            document.getElementById('in-kategori').value = kategori;
            document.getElementById('in-tanggal').value = tanggal;
            document.getElementById('in-waktu').value = waktu;
            document.getElementById('in-offset').value = offset;
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

        // SCRIPT MILESTONE MODAL CONTROLLER
        const milestoneModal = document.getElementById('milestoneModal');
        const milestoneModalBox = document.getElementById('milestoneModalBox');

        function bukaModalMilestone(bulanKey, judul, status, operasional, it) {
            document.getElementById('ms-bulan-key').value = bulanKey;
            document.getElementById('milestoneModalTitle').innerText = "Ubah " + judul;
            document.getElementById('ms-status').value = status;
            document.getElementById('ms-operasional').value = operasional;
            document.getElementById('ms-it').value = it;

            milestoneModal.classList.remove('hidden');
            setTimeout(() => {
                milestoneModalBox.classList.remove('scale-95', 'opacity-0');
                milestoneModalBox.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function tutupModalMilestone() {
            milestoneModalBox.classList.remove('scale-100', 'opacity-100');
            milestoneModalBox.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                milestoneModal.classList.add('hidden');
            }, 200);
        }

        // SCRIPT SIDEBAR MOBILE
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const closeSidebarBtn = document.getElementById('closeSidebarBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            const toggleSidebar = () => {
                if(sidebar && sidebarOverlay) {
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
    </script>

    <!-- ========================================================================= -->
    <!-- POP-UP REMINDER PENGINGAT H-1 AGENDA DENGAN FITUR ANTI-REPEATED POPUP     -->
    <!-- ========================================================================= -->
    <?php if (isset($agenda_besok) && $agenda_besok): ?>
    <div id="reminderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-all hidden">
        <div class="bg-white rounded-3xl w-full max-w-sm p-6 shadow-2xl border border-amber-100 transform transition-all scale-100 opacity-100">
            
            <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-amber-200 animate-bounce">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <div class="text-center mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Pengingat Agenda Besok!</h3>
                
                <p class="text-xs text-amber-600 font-semibold bg-amber-50 inline-block px-3 py-1 rounded-full mb-3">
                    ⏰ Mulai Jam <?= date('H:i', strtotime($agenda_besok['waktu'])); ?> WIB 
                    <span class="block text-[10px] text-amber-500 font-normal mt-0.5">
                        (Diingatkan <?= $agenda_besok['offset']; ?> jam sebelum acara)
                    </span>
                </p>
                
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-left">
                    <p class="text-sm font-bold text-gray-700"><?= htmlspecialchars($agenda_besok['judul']); ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($agenda_besok['deskripsi'] ?: 'Tidak ada deskripsi tambahan.'); ?></p>
                </div>
            </div>

            <button onclick="tutupReminderPermanen('<?= $agenda_besok['tanggal']; ?>')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-100 transition text-sm">
                Saya Mengerti
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tanggalAgenda = '<?= $agenda_besok['tanggal']; ?>';
            const statusDitutup = localStorage.getItem('reminder_dismissed_' + tanggalAgenda);
            
            if (!statusDitutup) {
                document.getElementById('reminderModal').classList.remove('hidden');
            }
        });

        function tutupReminderPermanen(tanggal) {
            localStorage.setItem('reminder_dismissed_' + tanggal, 'true');
            document.getElementById('reminderModal').classList.add('hidden');
        }
    </script>
    <?php endif; ?>

    <?php include 'components/alert.php'; ?>
</body>

</html>