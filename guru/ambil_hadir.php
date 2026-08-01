<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('guru');
header('Content-Type: application/json');

$sesiId = $_GET['sesi_id'] ?? 0;
$guruId = currentUser()['ref_id'];

// pastikan sesi ini milik guru yang sedang login
$cek = $pdo->prepare("SELECT id FROM sesi_absen WHERE id=? AND guru_id=?");
$cek->execute([$sesiId, $guruId]);
if (!$cek->fetch()) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.nama_lengkap, sw.nis, a.waktu_absen, a.status
    FROM absensi a
    JOIN siswa sw ON sw.id = a.siswa_id
    JOIN users u ON u.id = sw.user_id
    WHERE a.sesi_id = ?
    ORDER BY a.waktu_absen DESC
");
$stmt->execute([$sesiId]);
echo json_encode($stmt->fetchAll());
