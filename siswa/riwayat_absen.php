<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('siswa');
$siswaId = currentUser()['ref_id'];

$stmt = $pdo->prepare("
    SELECT a.waktu_absen, a.status, sa.jenis, sa.mapel, sa.tanggal, k.nama_kelas, u.nama_lengkap AS nama_guru
    FROM absensi a
    JOIN sesi_absen sa ON sa.id = a.sesi_id
    JOIN kelas k ON k.id = sa.kelas_id
    JOIN guru g ON g.id = sa.guru_id
    JOIN users u ON u.id = g.user_id
    WHERE a.siswa_id = ?
    ORDER BY a.waktu_absen DESC
");
$stmt->execute([$siswaId]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Absen Saya</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_siswa.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Riwayat Absen Saya</h1></div>
        <div class="card">
            <table>
                <tr><th>Tanggal</th><th>Jenis</th><th>Mapel</th><th>Guru</th><th>Waktu Absen</th><th>Status</th></tr>
                <?php if (!$riwayat): ?><tr><td colspan="6">Belum ada riwayat absen.</td></tr><?php endif; ?>
                <?php foreach ($riwayat as $r): ?>
                <tr>
                    <td><?= $r['tanggal'] ?></td>
                    <td><?= $r['jenis'] === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' ?></td>
                    <td><?= htmlspecialchars($r['mapel']) ?></td>
                    <td><?= htmlspecialchars($r['nama_guru']) ?></td>
                    <td><?= $r['waktu_absen'] ?></td>
                    <td><span class="status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
