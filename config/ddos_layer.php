<?php
// ==========================================================
// IP-BASED RATE LIMITER & PENALTY BOX (ANTI-DDOS LAYER 7)
// ==========================================================
$batas_request = 20;        // Maksimal request (Bisa diganti ke 3 untuk testing)
$jeda_waktu = 10;           // Dalam rentang 10 detik
$waktu_hukuman = 300;       // HUKUMAN: Diblokir 300 detik (5 menit)

// 1. TANGKAP IP ADDRESS PENGUNJUNG (MENEMBUS PROXY/CLOUDFLARE HOSTING)
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // Terkadang X_FORWARDED_FOR mengembalikan banyak IP, kita ambil yang pertama (IP asli)
    $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip_address = trim($ip_list[0]);
} else {
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

// Ubah IP menjadi hash MD5 agar nama file aman dan rapi
$nama_file = md5($ip_address) . '.json';

// Tentukan lokasi folder untuk menyimpan catatan IP
$dir_log = __DIR__ . '/ddos_logs/';
$file_path = $dir_log . $nama_file;

// 2. BUAT FOLDER OTOMATIS JIKA BELUM ADA
if (!is_dir($dir_log)) {
    mkdir($dir_log, 0777, true);
}

// --- FITUR BARU: TUKANG SAPU (GARBAGE COLLECTOR) ---
// Membersihkan file log IP yang sudah berumur lebih dari 1 hari (86400 detik).
// Berjalan secara acak (probabilitas 5%) agar tidak membebani server setiap kali di-load.
if (rand(1, 100) <= 5) {
    $files = glob($dir_log . '*.json');
    $waktu_sekarang = time();
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file) && ($waktu_sekarang - filemtime($file) > 86400)) {
                unlink($file); 
            }
        }
    }
}
// ---------------------------------------------------

// 3. BACA CATATAN IP DARI FILE JSON
if (file_exists($file_path)) {
    // Ambil log yang sudah ada
    $log_data = json_decode(file_get_contents($file_path), true);
} else {
    // Jika IP baru pertama kali datang, buat data awal
    $log_data = [
        'request_count' => 0,
        'waktu_awal' => time(),
        'banned_until' => 0
    ];
}

$waktu_sekarang = time();

// 4. EKSEKUSI HUKUMAN (JIKA MASIH DALAM MASA BLOKIR)
if ($log_data['banned_until'] > $waktu_sekarang) {
    $sisa_waktu = $log_data['banned_until'] - $waktu_sekarang;
    header('HTTP/1.1 429 Too Many Requests');
    die("<h1 style='font-family: sans-serif; text-align: center; margin-top: 20%; color: #ef4444;'>
            ⚠️ Akses Diblokir! (Error 429) <br>
            <span style='font-size: 16px; color: #6b7280;'>
                IP Anda (<b>{$ip_address}</b>) terdeteksi melakukan spam. <br>
                Silakan tunggu <b>{$sisa_waktu} detik</b> lagi untuk mengakses web ini.
            </span>
         </h1>");
}

// 5. HITUNG JEDA WAKTU UNTUK PENGUNJUNG NORMAL
$waktu_berlalu = $waktu_sekarang - $log_data['waktu_awal'];

if ($waktu_berlalu < $jeda_waktu) {
    $log_data['request_count']++;
    
    // JIKA NGE-SPAM MELEBIHI BATAS -> PENJARA 5 MENIT!
    if ($log_data['request_count'] > $batas_request) {
        $log_data['banned_until'] = $waktu_sekarang + $waktu_hukuman;
        
        // Simpan palu hakim ke file sebelum memutus koneksi
        file_put_contents($file_path, json_encode($log_data));
        
        header('HTTP/1.1 429 Too Many Requests');
        die("<h1 style='font-family: sans-serif; text-align: center; margin-top: 20%; color: #ef4444;'>
                ⚠️ Terdeteksi Serangan! (Error 429) <br>
                <span style='font-size: 16px; color: #6b7280;'>IP Anda diblokir selama 5 menit tanpa ampun.</span>
             </h1>");
    }
} else {
    // Jika jeda waktu aman (lebih dari 10 detik tanpa melanggar), reset ulang hitungan
    $log_data['request_count'] = 1;
    $log_data['waktu_awal'] = $waktu_sekarang;
}

// 6. SIMPAN LOG AKTIVITAS TERBARU KE FILE
file_put_contents($file_path, json_encode($log_data));
?>