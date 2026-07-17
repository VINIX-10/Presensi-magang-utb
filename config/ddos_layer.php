<?php
// Pastikan sesi sudah berjalan untuk melacak user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================
// PENGATURAN RATE LIMITING (ANTI-FLOOD / L7 DDOS MITIGATION)
// ==========================================================
$batas_request = 17; // Maksimal reload halaman
$jeda_waktu = 10;    // Dalam rentang 10 detik

if (!isset($_SESSION['request_count'])) {
    // Kunjungan pertama
    $_SESSION['request_count'] = 1;
    $_SESSION['waktu_request_pertama'] = time();
} else {
    // Tambah hitungan setiap kali halaman dimuat
    $_SESSION['request_count']++;
    
    // Hitung berapa detik yang sudah berlalu sejak request pertama
    $waktu_berlalu = time() - $_SESSION['waktu_request_pertama'];
    
    if ($waktu_berlalu < $jeda_waktu) {
        // Jika dalam 10 detik dia reload lebih dari 15 kali -> BLOKIR!
        if ($_SESSION['request_count'] > $batas_request) {
            
            // Berikan header error 429 (Terlalu Banyak Request) agar bot kebingungan
            header('HTTP/1.1 429 Too Many Requests');
            
            // DIE() akan mematikan proses PHP saat itu juga. 
            // Server tidak perlu capek meload HTML, CSS, atau Database.
            die("<h1 style='font-family: sans-serif; text-align: center; margin-top: 20%; color: #ef4444;'>
                    ⚠️ Akses Diblokir Sementara (Error 429) <br>
                    <span style='font-size: 16px; color: #6b7280;'>Terdeteksi aktivitas tidak wajar (Spam/DDoS). Harap tunggu beberapa detik sebelum mencoba lagi.</span>
                 </h1>");
        }
    } else {
        // Jika sudah lewat 10 detik dan aman, reset ulang hitungannya dari nol
        $_SESSION['request_count'] = 1;
        $_SESSION['waktu_request_pertama'] = time();
    }
}
?>