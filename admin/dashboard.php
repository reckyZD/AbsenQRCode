<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');
$user = currentUser();

$totalGuru  = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn();
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$totalKelas = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
$sesiAktif  = $pdo->query("SELECT COUNT(*) FROM sesi_absen WHERE status='aktif'")->fetchColumn();

$hariIni = $pdo->query("
    SELECT s.mapel, k.nama_kelas, g.mapel AS guru_mapel, u.nama_lengkap AS nama_guru,
           s.jam_mulai, s.jam_selesai, s.status,
           (SELECT COUNT(*) FROM absensi a WHERE a.sesi_id = s.id) AS jumlah_hadir
    FROM sesi_absen s
    JOIN kelas k ON k.id = s.kelas_id
    JOIN guru g ON g.id = s.guru_id
    JOIN users u ON u.id = g.user_id
    WHERE s.tanggal = CURDATE()
    ORDER BY s.jam_mulai DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Dashboard Admin</h1>
            <div class="topbar-right">
                <div class="topbar-avatar">
                    <?php if (!empty($user['foto'])): ?>
                        <img src="../<?= htmlspecialchars($user['foto']) ?>" alt="">
                    <?php else: ?>
                        <?= htmlspecialchars(inisialNama($user['nama'])) ?>
                    <?php endif; ?>
                </div>
                <div>Halo, <b><?= htmlspecialchars($user['nama']) ?></b> <span class="badge-role">ADMIN</span></div>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-box"><div class="num"><?= $totalGuru ?></div><div class="label">Total Guru</div></div>
            <div class="stat-box"><div class="num"><?= $totalSiswa ?></div><div class="label">Total Siswa</div></div>
            <div class="stat-box"><div class="num"><?= $totalKelas ?></div><div class="label">Total Kelas</div></div>
            <div class="stat-box"><div class="num"><?= $sesiAktif ?></div><div class="label">Sesi Absen Aktif</div></div>
        </div>

        <div class="card">
            <h3>Sesi Absensi Hari Ini</h3>
            <table>
                <tr><th>Mapel</th><th>Kelas</th><th>Guru</th><th>Jam</th><th>Hadir</th><th>Status</th></tr>
                <?php if (!$hariIni): ?>
                    <tr><td colspan="6">Belum ada sesi absen hari ini.</td></tr>
                <?php endif; ?>
                <?php foreach ($hariIni as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['mapel']) ?></td>
                    <td><?= htmlspecialchars($r['nama_kelas']) ?></td>
                    <td><?= htmlspecialchars($r['nama_guru']) ?></td>
                    <td><?= substr($r['jam_mulai'],0,5) ?> - <?= substr($r['jam_selesai'],0,5) ?></td>
                    <td><?= $r['jumlah_hadir'] ?> siswa</td>
                    <td><span class="status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
