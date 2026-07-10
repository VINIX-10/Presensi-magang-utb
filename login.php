<?php
session_start();
require 'koneksi.php';
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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Absensi Magang</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex h-screen items-center justify-center bg-[#F4F7FE] text-gray-800 select-none">

    <div class="w-full max-w-3xl p-8 relative">

        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-3 text-blue-600 mb-4">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
                <h1 class="text-3xl font-bold tracking-wide">UTB Tracker</h1>
            </div>
            <p class="text-gray-500 font-medium">Sistem Absensi Magang Mahasiswa</p>
        </div>

        <div id="step-select-account" class="transition-all duration-500 ease-in-out">
            <h2 class="text-xl font-bold text-center mb-8 text-gray-700">Siapa yang sedang absen?</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div onclick="showPinForm('Alvin Nurfaiz')" class="glass-card rounded-3xl p-8 shadow-sm border border-white cursor-pointer hover:shadow-lg hover:-translate-y-1 transition text-center group">
                    <img src="https://ui-avatars.com/api/?name=Alvin+Nurfaiz&background=ebf4ff&color=2563eb&size=128" alt="Alvin" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-white shadow-sm group-hover:border-blue-100 transition">
                    <h3 class="text-xl font-bold text-gray-800">Alvin Nurfaiz</h3>
                    <p class="text-blue-600 font-semibold text-sm mt-1">232101111</p>
                    <p class="text-gray-400 text-xs mt-2 font-medium">TiF 23 CNS J</p>
                </div>

                <div onclick="showPinForm('M. Yusman Bayuga')" class="glass-card rounded-3xl p-8 shadow-sm border border-white cursor-pointer hover:shadow-lg hover:-translate-y-1 transition text-center group">
                    <img src="https://ui-avatars.com/api/?name=Muhammad+Yusman+Bayuga&background=ecfdf5&color=059669&size=128" alt="Bayuga" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-white shadow-sm group-hover:border-emerald-100 transition">
                    <h3 class="text-xl font-bold text-gray-800">M. Yusman Bayuga</h3>
                    <p class="text-emerald-600 font-semibold text-sm mt-1">232101145</p>
                    <p class="text-gray-400 text-xs mt-2 font-medium">TiF 23 CiD G</p>
                </div>
            </div>
        </div>

        <div id="step-pin-entry" class="hidden transition-all duration-500 ease-in-out absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md mt-16">
            <div class="glass-card rounded-3xl p-8 shadow-lg border border-white text-center">

                <button onclick="backToSelect()" class="absolute top-6 left-6 text-gray-400 hover:text-gray-700 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </button>

                <h2 class="text-lg font-bold text-gray-800 mb-2">Masukkan PIN</h2>
                <p id="selected-name" class="text-blue-600 font-semibold mb-8">Nama User</p>

                <form id="form-login" method="POST" action="login.php">
                    <input type="hidden" name="nama_user" id="input-nama-user">

                    <div class="relative flex justify-center items-center gap-6 mb-10 w-max mx-auto">
                        <input type="tel" name="pin" id="real-pin-input" maxlength="4" autocomplete="off" class="absolute inset-0 w-full h-full opacity-0 cursor-text z-10">

                        <div class="w-6 h-8 flex items-center justify-center">
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-300"></div><span class="pin-digit hidden text-4xl font-bold"></span>
                        </div>
                        <div class="w-6 h-8 flex items-center justify-center">
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-300"></div><span class="pin-digit hidden text-4xl font-bold"></span>
                        </div>
                        <div class="w-6 h-8 flex items-center justify-center">
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-300"></div><span class="pin-digit hidden text-4xl font-bold"></span>
                        </div>
                        <div class="w-6 h-8 flex items-center justify-center">
                            <div class="pin-dot w-4 h-4 rounded-full bg-gray-300"></div><span class="pin-digit hidden text-4xl font-bold"></span>
                        </div>

                        <button type="button" id="eye-btn" class="absolute -right-16 z-20 p-2 text-gray-400 hover:text-gray-600 transition touch-none cursor-pointer">
                            <svg id="eye-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>

                    <button type="button" onclick="loginSimulasi()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-200 transition">
                        Buka Dashboard
                    </button>
                </form>
            </div>
        </div>

    </div>

    <?php include 'alert.php'; ?>

    <script>
        const stepSelect = document.getElementById('step-select-account');
        const stepPin = document.getElementById('step-pin-entry');
        const selectedNameLabel = document.getElementById('selected-name');
        const inputNamaUser = document.getElementById('input-nama-user');

        const pinInput = document.getElementById('real-pin-input');
        const dots = document.querySelectorAll('.pin-dot');
        const digits = document.querySelectorAll('.pin-digit');
        const eyeBtn = document.getElementById('eye-btn');
        let isRevealed = false;

        function showPinForm(name) {
            stepSelect.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            setTimeout(() => {
                stepSelect.classList.add('hidden');

                selectedNameLabel.innerText = name;
                inputNamaUser.value = name;

                stepPin.classList.remove('hidden');
                setTimeout(() => {
                    stepPin.classList.remove('opacity-0', 'scale-95');
                    stepPin.classList.add('opacity-100', 'scale-100');
                    pinInput.value = '';
                    renderPin();
                    pinInput.focus();
                }, 50);
            }, 300);
        }

        function backToSelect() {
            stepPin.classList.remove('opacity-100', 'scale-100');
            stepPin.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                stepPin.classList.add('hidden');
                stepSelect.classList.remove('hidden');
                setTimeout(() => {
                    stepSelect.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                }, 50);
            }, 300);
        }

        function renderPin() {
            const val = pinInput.value;
            for (let i = 0; i < 4; i++) {
                if (i < val.length) {
                    if (isRevealed) {
                        dots[i].classList.add('hidden');
                        digits[i].classList.remove('hidden');
                        digits[i].innerText = val[i];
                    } else {
                        dots[i].classList.remove('hidden', 'bg-gray-300');
                        dots[i].classList.add('bg-gray-800');
                        digits[i].classList.add('hidden');
                    }
                } else {
                    dots[i].classList.remove('hidden', 'bg-gray-800');
                    dots[i].classList.add('bg-gray-300');
                    digits[i].classList.add('hidden');
                }
            }
        }

        pinInput.addEventListener('input', () => {
            pinInput.value = pinInput.value.replace(/[^0-9]/g, '').slice(0, 4);
            renderPin();
        });

        const showPin = (e) => {
            e.preventDefault();
            isRevealed = true;
            renderPin();
            eyeBtn.classList.add('text-blue-500');
        }
        const hidePin = (e) => {
            e.preventDefault();
            isRevealed = false;
            renderPin();
            eyeBtn.classList.remove('text-blue-500');
            pinInput.focus();
        }

        eyeBtn.addEventListener('mousedown', showPin);
        eyeBtn.addEventListener('mouseup', hidePin);
        eyeBtn.addEventListener('mouseleave', hidePin);
        eyeBtn.addEventListener('touchstart', showPin);
        eyeBtn.addEventListener('touchend', hidePin);
        eyeBtn.addEventListener('touchcancel', hidePin);

        function loginSimulasi() {
            if (pinInput.value.length === 4) {
                document.getElementById('form-login').submit();
            } else {
                // Memunculkan SweetAlert untuk validasi frontend (Opsional, agar senada dengan backend)
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Lengkapi 4 digit PIN kamu!',
                        confirmButtonColor: '#2563eb'
                    });
                } else {
                    alert("Lengkapi 4 digit PIN!");
                }
                pinInput.focus();
            }
        }
    </script>
</body>

</html>