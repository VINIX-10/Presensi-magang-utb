<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Jika sudah ada session aktif, langsung lempar ke dashboard
if (isset($_SESSION['nama_user'])) {
    header("Location: index.php");
    exit;
}

$pesan_alert = "";

// Tangkap request jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = trim($_POST['nama_user']);
    $pin = trim($_POST['pin']);

    // Ambil data user beserta data failed_attempts dan lockout_time
    $stmt = $conn->prepare("SELECT id, nama_user, pin, failed_attempts, lockout_time FROM users WHERE nama_user = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id_db = $row['id'];
        $waktu_sekarang = date('Y-m-d H:i:s');

        // 1. CEK STATUS LOCKOUT (Apakah akun sedang diblokir?)
        if ($row['lockout_time'] != NULL && $row['lockout_time'] > $waktu_sekarang) {
            // Hitung sisa menit
            $sisa_waktu = strtotime($row['lockout_time']) - strtotime($waktu_sekarang);
            $sisa_menit = ceil($sisa_waktu / 60);
            $pesan_alert = "Akun terkunci karena terlalu banyak percobaan salah! Silakan coba lagi dalam $sisa_menit menit.";
        } else {
            // 2. JIKA TIDAK DIBLOKIR, VERIFIKASI PIN
            if (password_verify($pin, $row['pin'])) {

<<<<<<< HEAD
=======
                // Menghancurkan session ID lama dan membuat yang baru yang sangat acak
                session_regenerate_id(true);

>>>>>>> e885a344886c6808010dbc1bcb5e7e2e843945f6
                // JIKA PIN BENAR: Reset failed_attempts menjadi 0 dan hapus lockout_time
                $reset_stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE id = ?");
                $reset_stmt->bind_param("i", $user_id_db);
                $reset_stmt->execute();

                // Buat sesi login
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nama_user'] = $row['nama_user'];
                $_SESSION['last_activity'] = time();

                header("Location: index.php");
                exit;
            } else {
                // JIKA PIN SALAH: Tambah failed_attempts
                $attempts = $row['failed_attempts'] + 1;

                if ($attempts >= 3) {
                    // Blokir selama 5 menit ke depan jika sudah 3x salah
                    $lockout_until = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                    $lock_stmt = $conn->prepare("UPDATE users SET failed_attempts = ?, lockout_time = ? WHERE id = ?");
                    $lock_stmt->bind_param("isi", $attempts, $lockout_until, $user_id_db);
                    $lock_stmt->execute();

                    $pesan_alert = "Gagal Login! Kamu sudah salah 3 kali. Akun dikunci selama 5 menit.";
                } else {
                    // Update jumlah percobaan gagal
                    $update_stmt = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                    $update_stmt->bind_param("ii", $attempts, $user_id_db);
                    $update_stmt->execute();

                    $sisa_kesempatan = 3 - $attempts;
                    $pesan_alert = "PIN yang dimasukkan salah! Sisa kesempatan: $sisa_kesempatan kali.";
                }
            }
        }
    } else {
        $pesan_alert = "Gagal Login! User tidak ditemukan.";
    }
}
<<<<<<< HEAD
?>
=======
>>>>>>> e885a344886c6808010dbc1bcb5e7e2e843945f6
