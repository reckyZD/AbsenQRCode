# Aplikasi Absensi QR Code (PHP + MySQL)

## Alur Aplikasi
```
index.php (login)
    -> proses_login.php
        cek username -> cek password -> cek role
            -> admin -> admin/dashboard.php
            -> guru  -> guru/dashboard.php
            -> siswa -> siswa/dashboard.php
```

## Fitur

### Tampilan
- Nama sekolah **MAS DARUL HIKMAH SADANG** & Tahun Pelajaran **2026/2027** tampil di halaman
  login beserta logo (file `assets/img/logo.svg` — **ganti file ini dengan logo asli sekolah**,
  cukup timpa file dengan nama yang sama supaya tidak perlu ubah kode).
- Sidebar admin/guru/siswa punya **bingkai foto profil persegi**, nama pengguna, dan nama
  sekolah di bawahnya, serta tombol **"Edit Profil"** untuk mengunggah foto profil sendiri
  (JPG/PNG/WEBP, maks 2MB).
- **Toggle sidebar**: di HP/tablet ada tombol ☰ mengambang untuk buka/tutup menu (off-canvas).
  Di layar besar (PC), ada tombol ciutkan (⇄) di kop sidebar untuk mode ringkas ikon saja.
- Seluruh halaman **responsif** — otomatis menyesuaikan di HP, tablet, maupun PC (tabel bisa
  digeser ke samping di layar sempit, grid statistik menyesuaikan jumlah kolom, dsb).

### Admin
- Kelola guru & siswa: tambah, hapus, **lihat detail akun**, dan **reset password**.
- Kelola kelas: tambah/hapus kelas, dan di aksi "Lihat" bisa **melihat daftar siswa di kelas
  tsb, menambahkan siswa baru langsung ke kelas itu, memindahkan siswa yang sudah ada, dan
  mengeluarkan siswa dari kelas** (tanpa menghapus akunnya).
- **Rekap Absen** digabung dalam satu tabel: jam masuk, status (hadir/telat), jam pulang,
  dan status keseluruhan (Lengkap / Belum Pulang / Belum Masuk), difilter per kelas & tanggal.

### Guru
- Buat sesi **Absen Masuk** atau **Absen Pulang** per kelas/mapel.
- QR code sesi **hanya berlaku 15 menit** sejak dibuat (ada hitung mundur/countdown di
  halaman QR), setelah itu otomatis dianggap kadaluarsa walau sesi belum ditutup manual.
- Bisa melihat daftar yang sudah absen secara real-time (auto-refresh tiap 4 detik).
- Untuk sesi **Absen Pulang**, siswa wajib sudah tercatat absen masuk di hari & kelas yang
  sama sebelum bisa absen pulang.

### Siswa
- Scan QR lewat kamera HP/laptop (`html5-qrcode`).
- Absen masuk otomatis dicatat **Hadir**/**Telat** (toleransi 15 menit dari jam mulai sesi).
- Absen pulang dicatat terpisah, dan riwayat absen menampilkan jenis (masuk/pulang).

## Cara Instalasi (XAMPP / Laragon)
1. Copy folder `absensi_qr` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka phpMyAdmin, import file **database.sql** (otomatis membuat database `db_absensi_qr`).
   Jika Anda **sudah pernah install versi sebelumnya**, jalankan **migration_v2.sql** dan
   **migration_v3.sql** secara berurutan (menambahkan kolom `jenis` dan `foto_profil`) —
   tidak perlu install ulang dari nol.
3. Pastikan folder `assets/uploads/profil/` bisa ditulis oleh PHP (writable) — di hosting
   biasanya otomatis bisa, di server sendiri mungkin perlu `chmod 755` folder tsb.
4. Cek `config/database.php`, sesuaikan `DB_USER` / `DB_PASS` bila perlu (default XAMPP: user `root`, password kosong).
5. Buka browser: `http://localhost/absensi_qr/seed.php` — ini akan membuat 3 akun contoh:
   - `admin` / `admin123`
   - `guru1` / `guru123`
   - `siswa1` / `siswa123`
6. **Hapus file `seed.php`** setelah selesai (supaya tidak bisa dijalankan ulang oleh orang lain).
7. Akses `http://localhost/absensi_qr/index.php` untuk login.

## Membagikan ke Guru & Siswa Lewat ngrok (opsional)
Supaya guru/siswa bisa akses dari HP di luar WiFi laptop Anda, dan supaya kamera HP bisa
dipakai untuk scan QR (butuh HTTPS), pakai **ngrok**. Sudah disediakan 2 script agar tidak
perlu buka XAMPP Control Panel + cmd terpisah tiap hari:

1. **Sekali saja di awal:**
   - Install [ngrok](https://ngrok.com), daftar akun gratis, lalu jalankan
     `ngrok config add-authtoken TOKEN_ANDA` (token diambil dari dashboard ngrok.com).
   - Buka `start_server.bat` dengan Notepad, sesuaikan `XAMPP_DIR` dan `NGROK_DIR`
     dengan lokasi instalasi XAMPP & ngrok.exe di komputer Anda.
2. **Setiap mau dipakai:** double-click `start_server.bat`. Ini akan otomatis:
   - menjalankan Apache & MySQL,
   - menjalankan ngrok (tunnel HTTPS),
   - membuka dashboard `http://127.0.0.1:4040` yang menampilkan link publik
     (`https://xxxx.ngrok-free.app`) — tambahkan `/absensi_qr/index.php` di
     belakang link itu, lalu bagikan ke guru & siswa.
3. **Setelah selesai dipakai:** double-click `stop_server.bat` untuk mematikan
   Apache, MySQL, dan ngrok sekaligus.

Catatan:
- Laptop harus tetap menyala dan terhubung internet selama guru/siswa memakai aplikasi.
- Saat pertama kali buka link ngrok, akan muncul halaman peringatan dari ngrok — cukup
  klik **"Visit Site"** untuk lanjut ke halaman login (berlaku 7 hari per HP).
- Paket gratis ngrok: kuota 1 GB transfer data/bulan & 20.000 request/bulan, cukup untuk
  pemakaian absensi teks sehari-hari.

## Catatan Teknis
- QR code sesi berisi **token acak unik** (bukan data pribadi), dibuat dengan `random_bytes()`,
  jadi tidak bisa ditebak/di-generate ulang oleh siswa.
- QR digenerate di sisi browser (JS, library `qrcode.js` via CDN) — jadi tidak perlu install
  library PHP tambahan.
- Scan QR oleh siswa pakai library `html5-qrcode` (CDN) yang mengakses kamera lewat browser
  (butuh HTTPS atau `localhost` agar izin kamera diberikan browser).
- Struktur folder:
  ```
  absensi_qr/
    config/database.php     -> koneksi PDO
    includes/                -> auth.php, sidebar per role
    admin/                    -> dashboard, kelola guru/siswa/kelas, laporan
    guru/                      -> dashboard, buat sesi, tampilkan QR, riwayat
    siswa/                      -> dashboard (scan), proses absen, riwayat
    assets/css/style.css
    database.sql
    index.php / proses_login.php / logout.php / seed.php
  ```

## Pengembangan Lanjutan (opsional)
- Tambah validasi geolokasi agar siswa hanya bisa absen di area sekolah.
- Tambah notifikasi WA/email ke orang tua saat siswa tercatat "telat" atau tidak hadir.
- Tambah export laporan ke Excel/PDF.
