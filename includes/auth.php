<?php
// ============================================
// PENJAGA SESSION LOGIN
// panggil requireRole('admin') / ('guru') / ('siswa') di paling atas
// setiap halaman dashboard supaya tidak bisa diakses tanpa login
// atau diakses oleh role yang salah.
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireRole(string $role): void
{
    // pakai path relatif (../index.php) supaya tetap benar di folder/domain manapun
    // file ini dipanggil dari admin/, guru/, atau siswa/ (satu level dari root)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../index.php?error=belum_login');
        exit;
    }
    if ($_SESSION['role'] !== $role) {
        header('Location: ../index.php?error=akses_ditolak');
        exit;
    }
}

function currentUser(): array
{
    return [
        'id'     => $_SESSION['user_id'] ?? null,
        'nama'   => $_SESSION['nama_lengkap'] ?? '',
        'role'   => $_SESSION['role'] ?? '',
        'ref_id' => $_SESSION['ref_id'] ?? null, // id di tabel guru/siswa
    ];
}
