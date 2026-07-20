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
  `pin` varchar(255) NOT NULL, 
  `failed_attempts` int(11) NOT NULL DEFAULT 0, 
  `lockout_time` datetime DEFAULT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Memasukkan data awal (Default User) ke tabel `users`
-- default password 1234
-- --------------------------------------------------------
INSERT INTO `users` (`nama_user`, `nim`, `kelas`, `konsentrasi`, `pin`, `failed_attempts`, `lockout_time`) VALUES
('Alvin Nurfaiz', '232101111', 'TiF 23 CNS J', 'Computer and Network Security', '$2y$10$771J.TR8EnLcoC2arj3gM.POiqGua6aT8J/c2naogZ8xB8ptOeQkG', 0, NULL),
('M. Yusman Bayuga', '232101145', 'TiF 23 CiD G', 'Creative Interactive Design', '$2y$10$771J.TR8EnLcoC2arj3gM.POiqGua6aT8J/c2naogZ8xB8ptOeQkG', 0, NULL);

-- --------------------------------------------------------
-- 3. Struktur dari tabel `kehadiran`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kehadiran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` time NOT NULL,
  `waktu_keluar` time DEFAULT NULL, 
  `status` enum('Hadir','Sakit','Izin','Lembur') NOT NULL, 
  `catatan` text DEFAULT NULL, 
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_kehadiran` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Struktur dari tabel `agenda` (SUDAH DIREVISI)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` enum('Industri','Kampus','Lembur') NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL, -- [BARU] Untuk menyimpan jam mulai agenda (08:00, dll)
  `pengingat_offset` int(11) NOT NULL DEFAULT 12, -- [BARU] Untuk menyimpan jeda pengingat (1, 12, atau 20)
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_agenda` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Struktur dari tabel `milestones`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bulan_key` varchar(2) NOT NULL, 
  `judul` varchar(50) NOT NULL,
  `status` enum('Pending','Berjalan','Selesai') NOT NULL DEFAULT 'Pending',
  `operasional` text DEFAULT NULL,
  `it` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_milestone` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;