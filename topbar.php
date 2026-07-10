<header class="bg-white/80 backdrop-blur-md px-6 py-4 flex justify-between items-center sticky top-0 z-10 shadow-sm md:shadow-none md:border-b md:border-gray-100">
    <div class="flex items-center gap-4 w-full md:w-auto">
        <button id="mobileMenuBtn" class="md:hidden p-2 -ml-2 text-gray-600 hover:text-blue-600 focus:outline-none transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <div class="relative w-full max-w-xs sm:block">
            <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
                <input type="text" placeholder="Cari data..." class="w-full bg-gray-100 rounded-full py-2.5 pl-12 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <?php else: ?>
                <h2 class="text-xl font-bold text-gray-800">Riwayat Kehadiran</h2>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="flex items-center gap-4">
        <a href="export_excel.php" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2 px-4 md:px-5 rounded-full flex items-center gap-2 shadow-sm transition">
            <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span class="md:hidden">Export</span>
            <span class="hidden md:inline">Download Rekap</span>
        </a>
    </div>
</header>