-- Membuat database jika belum ada
CREATE DATABASE IF NOT EXISTS absensi_db;
USE absensi_db;

-- --------------------------------------------------------
-- 1. Struktur dari tabel `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_user` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `konsentrasi` varchar(100) NOT NULL,
  `pin` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Memasukkan data awal (Default User) ke tabel `users`
-- Sesuai dengan tampilan di halaman login.php
-- PIN default diatur menjadi: 1234
-- --------------------------------------------------------
INSERT INTO `users` (`nama_user`, `nim`, `kelas`, `konsentrasi`, `pin`) VALUES
('Alvin Nurfaiz', '232101111', 'TiF 23 CNS J', 'Computer and Network Security', '1234'),
('M. Yusman Bayuga', '232101145', 'TiF 23 CiD G', 'Creative Interactive Design', '1234');

-- --------------------------------------------------------
-- 3. Struktur dari tabel `kehadiran`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kehadiran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` time NOT NULL,
  `waktu_keluar` time DEFAULT NULL, 
  `status` enum('Hadir','Sakit','Izin') NOT NULL,
  `catatan` text DEFAULT NULL, -- Kolom baru untuk menampung isi logbook harian
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_kehadiran` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;