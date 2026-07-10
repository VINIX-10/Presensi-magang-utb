<?php
// Mendeteksi halaman apa yang sedang dibuka sekarang
$current_page = basename($_SERVER['PHP_SELF']);
?>

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
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition <?php echo ($current_page == 'index.php') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-50'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </a>
            
            <a href="time-management.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?php echo ($current_page == 'time-management.php') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-50'; ?>">
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