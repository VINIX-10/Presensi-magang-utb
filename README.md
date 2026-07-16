# UTB Tracker - Sistem Informasi Logbook & Absensi Magang

![UTB Tracker Landing Page](assets/picture/Wireframe_Presensi_Magang_UTB%20%281%29_pages-to-jpg-0001.jpg)

UTB Tracker adalah aplikasi berbasis web untuk pencatatan waktu kehadiran (presensi) dan manajemen aktivitas harian (logbook) bagi mahasiswa magang Universitas Teknologi Bandung (UTB). Aplikasi ini dirancang dengan antarmuka yang modern, responsif, dan mudah digunakan untuk memantau progres magang secara real-time.

---

## 🚀 Fitur Utama & Tampilan Antarmuka

Aplikasi ini memiliki beberapa modul utama yang saling terintegrasi:

### 1. Sistem Autentikasi Berbasis PIN
Login yang cepat dan aman menggunakan sistem kartu akun dan PIN 4-digit, dilengkapi dengan proteksi *brute-force* (lockout otomatis jika salah PIN 3 kali).

![Halaman Login & PIN](assets/picture/Wireframe_Presensi_Magang_UTB%20%281%29_pages-to-jpg-0002.jpg)

### 2. Dashboard & Real-time Tracking
Menampilkan statistik kehadiran (Hadir, Terlambat, Izin/Sakit, Sisa Hari Kerja) dan grafik *Line Chart* interaktif untuk memantau tren kehadiran mahasiswa setiap bulannya. Mahasiswa dapat melakukan absen masuk dan pulang langsung dari halaman ini.

![Dashboard Utama](assets/picture/Wireframe_Presensi_Magang_UTB%20%281%29_pages-to-jpg-0003.jpg)

### 3. Riwayat Kehadiran & Manajemen Logbook
Tabel riwayat absensi yang dilengkapi dengan fitur *Live Search*. Mahasiswa dapat menambahkan atau mengedit catatan kegiatan harian (logbook) melalui modal interaktif tanpa harus berpindah halaman.

![Modal Logbook Harian](assets/picture/Wireframe_Presensi_Magang_UTB%20%281%29_pages-to-jpg-0004.jpg)

### 4. Time Management & Milestone (Kalender Agenda)
Modul untuk menyinkronkan target bulanan industri dengan agenda akademik kampus. Pengguna dapat menambahkan agenda mandiri ke dalam kalender dan mengelola status *milestone* (Pending, Berjalan, Selesai).

![Modal Time Management](assets/picture/Wireframe_Presensi_Magang_UTB%20%281%29_pages-to-jpg-0005.jpg)

### 5. Export Laporan (Excel/CSV)
Sistem otomatis menghasilkan rekapitulasi data absensi beserta logbook ke dalam format file `.csv` yang kompatibel dengan Microsoft Excel dan siap disetorkan kepada dosen pembimbing atau mentor industri.

---

## 🛠️ Teknologi yang Digunakan

*   **Frontend:** HTML5, Tailwind CSS (via CDN), Vanilla JavaScript, Chart.js, SweetAlert2
*   **Backend:** PHP (Native)
*   **Database:** MySQL / MariaDB
*   **Keamanan:** Password Hashing (Bcrypt), CSRF Token Protection, Anti XSS (htmlspecialchars)

---

## 📂 Struktur Direktori

```text
vinix-10-presensi-magang-utb/
├── export_excel.php           # Logika export data ke CSV
├── index.php                  # Halaman Utama / Dashboard
├── landing_page_utama.html    # Halaman Landing (Entry Point)
├── login.php                  # Halaman Autentikasi PIN
├── logout.php                 # Logika penghancur sesi
├── time-management.php        # Halaman Kalender & Milestone
├── assets/                    
│   ├── picture/               # Direktori gambar dokumentasi & wireframe
│   ├── script.js              # Script JS (Chart, Countdown, dll)
│   └── style.css              # Custom styling tambahan
├── components/                
│   ├── alert.php              # Komponen SweetAlert global
│   ├── sidebar.php            # Navigasi samping
│   └── topbar.php             # Navigasi atas (termasuk fitur pencarian)
├── config/                    
│   ├── koneksi.php            # Kredensial Database
│   └── sesi.php               # Manajemen Session & CSRF
├── database/                  
│   └── absensi_db.sql         # File dump database (Struktur & Data Dummy)
└── proses/                    
    ├── proses_dashboard.php   # Otak backend untuk index.php
    ├── proses_login.php       # Otak backend autentikasi
    └── proses_time_management.php # Otak backend CRUD Kalender
```

---

## ⚙️ Panduan Instalasi (Local Development)

1. **Persiapan Server Lokal:** Pastikan Anda telah menginstal web server lokal seperti **XAMPP**, **Laragon**, atau **MAMP**.
2. **Clone Repository:**
   ```bash
   git clone https://github.com/username-kamu/vinix-10-presensi-magang-utb.git
   ```
   *Atau download ZIP dan ekstrak folder tersebut ke direktori `htdocs` (XAMPP) atau `www` (Laragon).*
3. **Konfigurasi Database:**
   * Buka phpMyAdmin (biasanya di `http://localhost/phpmyadmin`) atau HeidiSQL.
   * Buat database baru dengan nama `absensi_db`.
   * Import file `database/absensi_db.sql` ke dalam database tersebut.
4. **Penyesuaian Koneksi:**
   Buka file `config/koneksi.php` dan pastikan kredensialnya sudah sesuai dengan server lokal Anda (secara default disesuaikan untuk Laragon dengan password kosong).
5. **Jalankan Aplikasi:**
   Buka browser dan akses URL: `http://localhost/vinix-10-presensi-magang-utb/landing_page_utama.html`

### Kredensial Default:
*   Pilih salah satu profil mahasiswa yang tersedia (misal: **Alvin Nurfaiz**)
*   **PIN Default:** `1234`

---

## 👨‍💻 Pengembang

Dikembangkan oleh:
*   **Alvin Nurfaiz** (NIM: 232101111 - Computer and Network Security)
*   **M. Yusman Bayuga** (NIM: 232101145 - Creative Interactive Design)

Universitas Teknologi Bandung (UTB) © 2026
