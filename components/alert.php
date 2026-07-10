<?php if (!empty($pesan_alert)): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let pesan = "<?php echo $pesan_alert; ?>";
            let tipeIcon = "success"; // Default icon adalah centang hijau
            let judul = "Berhasil!";

            // Logika deteksi kata kunci untuk mengubah icon
            if (pesan.toLowerCase().includes("gagal") || pesan.toLowerCase().includes("salah")) {
                tipeIcon = "error";
                judul = "Oops, Terjadi Kesalahan!";
            } else if (pesan.toLowerCase().includes("lembur") || pesan.toLowerCase().includes("pulang")) {
                tipeIcon = "info";
                judul = "Informasi";
            }

            // Memunculkan Pop-up Modern
            Swal.fire({
                title: judul,
                text: pesan,
                icon: tipeIcon,
                confirmButtonColor: '#2563eb', // Warna biru khas Tailwind (blue-600)
                confirmButtonText: 'Oke, Mengerti',
                backdrop: `rgba(0,0,0,0.4)`, // Efek layar belakang sedikit gelap
                timer: 4000, // Otomatis hilang dalam 4 detik
                timerProgressBar: true
            });
        });
    </script>
<?php endif; ?>