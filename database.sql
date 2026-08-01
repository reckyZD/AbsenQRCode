-- ============================================
-- DATABASE: db_absensi_qr
-- Aplikasi Absensi QR Code (Admin, Guru, Siswa)
-- ============================================

CREATE DATABASE IF NOT EXISTS db_absensi_qr;
USE db_absensi_qr;

-- Tabel akun (dipakai untuk login semua role)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','guru','siswa') NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel kelas
CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL
);

-- Data guru (relasi ke users)
CREATE TABLE guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nip VARCHAR(30),
    mapel VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Data siswa (relasi ke users & kelas)
CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nis VARCHAR(30),
    kelas_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL
);

-- Sesi absen dibuat oleh guru, berisi token unik untuk QR
CREATE TABLE sesi_absen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    kelas_id INT NOT NULL,
    jenis ENUM('masuk','pulang') NOT NULL DEFAULT 'masuk',
    mapel VARCHAR(50),
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('aktif','selesai') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
);
-- Catatan: QR code sebuah sesi hanya berlaku 15 menit sejak created_at
-- (divalidasi di siswa/proses_absen.php), terlepas dari kapan jam_selesai.

-- Rekap kehadiran siswa per sesi
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesi_id INT NOT NULL,
    siswa_id INT NOT NULL,
    waktu_absen DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('hadir','telat') DEFAULT 'hadir',
    FOREIGN KEY (sesi_id) REFERENCES sesi_absen(id) ON DELETE CASCADE,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    UNIQUE KEY unique_absen (sesi_id, siswa_id)
);

-- Data contoh kelas
INSERT INTO kelas (nama_kelas) VALUES ('X IPA 1'), ('XI IPA 1');

-- Catatan:
-- Akun user (admin, guru, siswa) TIDAK diisi manual di sini karena
-- password wajib di-hash pakai password_hash() PHP.
-- Jalankan file seed.php sekali lewat browser untuk membuat akun contoh:
--   admin  / admin123
--   guru1  / guru123
--   siswa1 / siswa123
