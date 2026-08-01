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
   Jika Anda **sudah pernah install versi sebelumnya**, cukup jalankan **migration_v2.sql**
   (menambahkan kolom `jenis` di tabel `sesi_absen`) — tidak perlu install ulang dari nol.
3. Cek `config/database.php`, sesuaikan `DB_USER` / `DB_PASS` bila perlu (default XAMPP: user `root`, password kosong).
4. Buka browser: `http://localhost/absensi_qr/seed.php` — ini akan membuat 3 akun contoh:
   - `admin` / `admin123`
   - `guru1` / `guru123`
   - `siswa1` / `siswa123`
5. **Hapus file `seed.php`** setelah selesai (supaya tidak bisa dijalankan ulang oleh orang lain).
6. Akses `http://localhost/absensi_qr/index.php` untuk login.

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

## ongoing Pengembangan Lanjutan ()
- Tambah validasi geolokasi agar siswa hanya bisa absen di area sekolah.
- Tambah notifikasi WA/email ke orang tua saat siswa tercatat "telat" atau tidak hadir.
- Tambah export laporan ke Excel/PDF.
