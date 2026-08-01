-- ============================================
-- MIGRATION v3: tambah kolom foto profil untuk semua role
-- Jalankan ini kalau database sudah pernah diinstall sebelumnya.
-- ============================================
USE db_absensi_qr;

ALTER TABLE users
    ADD COLUMN foto_profil VARCHAR(255) DEFAULT NULL AFTER nama_lengkap;
