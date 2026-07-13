<header class="bg-white/80 backdrop-blur-md px-6 py-4 flex justify-between items-center sticky top-0 z-10 shadow-sm md:shadow-none md:border-b md:border-gray-100 relative">
    
    <!-- Bagian Kiri: Hamburger Menu & Container Search -->
    <div class="flex items-center gap-4 w-full md:w-auto">
        <button id="mobileMenuBtn" class="md:hidden p-2 -ml-2 text-gray-600 hover:text-blue-600 focus:outline-none transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        
        <!-- SEARCH CONTAINER (Ajaib: Hidden di HP, tapi bisa jadi overlay saat dipanggil) -->
        <div id="searchContainer" class="hidden md:flex absolute inset-0 bg-white/95 backdrop-blur-md px-6 py-4 md:relative md:bg-transparent md:p-0 md:inset-auto w-full md:max-w-xs z-20 items-center">
            <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
                <div class="relative w-full">
                    <!-- id="searchInput" dipertahankan agar script filterTable() di index.php tetap jalan -->
                    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari tanggal, status, atau logbook..." class="w-full bg-gray-100 rounded-full py-2.5 pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 transition shadow-inner">
                    <svg class="w-4 h-4 text-gray-400 absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <!-- Tombol Batal khusus HP -->
                <button type="button" onclick="toggleMobileSearch()" class="md:hidden ml-4 text-sm font-bold text-gray-600 hover:text-rose-500 transition whitespace-nowrap">Batal</button>
            <?php else: ?>
                <h2 class="text-xl font-bold text-gray-800">Riwayat Kehadiran</h2>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bagian Kanan: Tombol Kaca Pembesar Mobile & Tombol Export -->
    <div class="flex items-center gap-3 md:gap-4">
        <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
            <!-- TOMBOL KACA PEMBESAR KHUSUS MOBILE -->
            <button onclick="toggleMobileSearch()" class="md:hidden p-2 text-gray-600 hover:text-blue-600 bg-gray-50 hover:bg-gray-100 rounded-full focus:outline-none transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
        <?php endif; ?>

        <a href="export_excel.php" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2 px-4 md:px-5 rounded-full flex items-center gap-2 shadow-sm transition">
            <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span class="md:hidden font-medium">Export</span>
            <span class="hidden md:inline">Download Rekap</span>
        </a>
    </div>

    <!-- SCRIPT TOGGLE OVERLAY SEARCH -->
    <script>
        function toggleMobileSearch() {
            const searchContainer = document.getElementById('searchContainer');
            const searchInput = document.getElementById('searchInput');
            
            // Logika Buka-Tutup
            if (searchContainer.classList.contains('hidden')) {
                // Munculkan overlay pencarian
                searchContainer.classList.remove('hidden');
                searchContainer.classList.add('flex');
                // Langsung fokus ke keyboard HP
                if (searchInput) searchInput.focus();
            } else {
                // Sembunyikan overlay
                searchContainer.classList.add('hidden');
                searchContainer.classList.remove('flex');
                // Bersihkan teks dan kembalikan tabel seperti semula
                if (searchInput) {
                    searchInput.value = '';
                    if (typeof filterTable === "function") filterTable(); 
                }
            }
        }
    </script>
</header>