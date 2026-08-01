-- ============================================
-- MIGRATION: jalankan ini jika database Anda dibuat SEBELUM update ini
-- (jika baru install dari database.sql versi terbaru, TIDAK PERLU jalankan ini)
-- ============================================
USE db_absensi_qr;

ALTER TABLE sesi_absen
    ADD COLUMN jenis ENUM('masuk','pulang') NOT NULL DEFAULT 'masuk' AFTER kelas_id;
