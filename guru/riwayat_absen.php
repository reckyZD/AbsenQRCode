<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('guru');
$guruId = currentUser()['ref_id'];

$stmt = $pdo->prepare("
    SELECT s.*, k.nama_kelas, (SELECT COUNT(*) FROM absensi a WHERE a.sesi_id = s.id) AS jumlah_hadir
    FROM sesi_absen s JOIN kelas k ON k.id = s.kelas_id
    WHERE s.guru_id = ?
    ORDER BY s.tanggal DESC, s.jam_mulai DESC
");
$stmt->execute([$guruId]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Absensi</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_guru.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Riwayat Sesi Absen</h1></div>
        <div class="card">
            <table>
                <tr><th>Tanggal</th><th>Jenis</th><th>Kelas</th><th>Mapel</th><th>Jam</th><th>Hadir</th><th>Status</th><th>Aksi</th></tr>
                <?php if (!$riwayat): ?><tr><td colspan="8">Belum ada riwayat sesi.</td></tr><?php endif; ?>
                <?php foreach ($riwayat as $r): ?>
                <tr>
                    <td><?= $r['tanggal'] ?></td>
                    <td><?= $r['jenis'] === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' ?></td>
                    <td><?= htmlspecialchars($r['nama_kelas']) ?></td>
                    <td><?= htmlspecialchars($r['mapel']) ?></td>
                    <td><?= substr($r['jam_mulai'],0,5) ?> - <?= substr($r['jam_selesai'],0,5) ?></td>
                    <td><?= $r['jumlah_hadir'] ?> siswa</td>
                    <td><span class="status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td><a href="lihat_qr.php?id=<?= $r['id'] ?>">Lihat Detail</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
