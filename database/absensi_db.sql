-- Membuat database jika belum ada
CREATE DATABASE IF NOT EXISTS absensi_db;
USE absensi_db;

-- --------------------------------------------------------
-- 1. Struktur dari tabel `users` 'pengguna'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_user` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `konsentrasi` varchar(100) NOT NULL,
  `pin` varchar(255) NOT NULL, -- Diperbesar ke 255 karakter untuk menampung hash Bcrypt yang aman
  `failed_attempts` int(11) NOT NULL DEFAULT 0, -- Kolom untuk mencatat jumlah kesalahan input PIN berturut-turut
  `lockout_time` datetime DEFAULT NULL, -- Kolom untuk mencatat batas akhir waktu pemblokiran akun
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Memasukkan data awal (Default User) ke tabel `users`
-- Catatan: Ganti teks dalam kurung siku dengan string hash Bcrypt 
-- yang dihasilkan oleh file generator PIN milikmu.
-- default password 1234
-- --------------------------------------------------------
INSERT INTO `users` (`nama_user`, `nim`, `kelas`, `konsentrasi`, `pin`, `failed_attempts`, `lockout_time`) VALUES
('Alvin Nurfaiz', '232101111', 'TiF 23 CNS J', 'Computer and Network Security', '$2y$10$771J.TR8EnLcoC2arj3gM.POiqGua6aT8J/c2naogZ8xB8ptOeQkG', 0, NULL),
('M. Yusman Bayuga', '232101145', 'TiF 23 CiD G', 'Creative Interactive Design', '2y$10$771J.TR8EnLcoC2arj3gM.POiqGua6aT8J/c2naogZ8xB8ptOeQkG', 0, NULL);

-- --------------------------------------------------------
-- 3. Struktur dari tabel `kehadiran`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kehadiran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` time NOT NULL,
  `waktu_keluar` time DEFAULT NULL, 
  `status` enum('Hadir','Sakit','Izin','Lembur') NOT NULL, -- Ditambahkan opsi 'Lembur' untuk absensi akhir pekan
  `catatan` text DEFAULT NULL, -- Kolom logbook harian aktivitas magang
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_kehadiran` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;