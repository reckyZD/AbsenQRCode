<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('guru');
$user = currentUser();
$guruId = $user['ref_id'];

// Tutup sesi
if (isset($_GET['tutup'])) {
    $pdo->prepare("UPDATE sesi_absen SET status='selesai' WHERE id=? AND guru_id=?")
        ->execute([$_GET['tutup'], $guruId]);
    header('Location: dashboard.php');
    exit;
}

$sesiAktif = $pdo->prepare("
    SELECT s.*, k.nama_kelas, (SELECT COUNT(*) FROM absensi a WHERE a.sesi_id = s.id) AS jumlah_hadir
    FROM sesi_absen s JOIN kelas k ON k.id = s.kelas_id
    WHERE s.guru_id = ? AND s.status = 'aktif'
    ORDER BY s.created_at DESC
");
$sesiAktif->execute([$guruId]);
$sesiAktif = $sesiAktif->fetchAll();

$totalSesi = $pdo->prepare("SELECT COUNT(*) FROM sesi_absen WHERE guru_id=?");
$totalSesi->execute([$guruId]);
$totalSesi = $totalSesi->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Guru</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_guru.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Dashboard Guru</h1>
            <div>Halo, <b><?= htmlspecialchars($user['nama']) ?></b> <span class="badge-role">GURU</span></div>
        </div>

        <div class="stat-grid">
            <div class="stat-box"><div class="num"><?= count($sesiAktif) ?></div><div class="label">Sesi Aktif Sekarang</div></div>
            <div class="stat-box"><div class="num"><?= $totalSesi ?></div><div class="label">Total Sesi Dibuat</div></div>
        </div>

        <div class="card" style="display:flex; gap:14px;">
            <a class="btn" href="buat_sesi.php?jenis=masuk">➕ Buat Sesi Absen Masuk</a>
            <a class="btn btn-outline" href="buat_sesi.php?jenis=pulang">➕ Buat Sesi Absen Pulang</a>
        </div>

        <div class="card">
            <h3>Sesi Absen Aktif</h3>
            <?php if (!$sesiAktif): ?>
                <p>Belum ada sesi aktif. Buat sesi baru di atas untuk mulai absensi.</p>
            <?php endif; ?>
            <?php foreach ($sesiAktif as $s):
                $dibuat = new DateTime($s['created_at']);
                $kadaluarsa = (clone $dibuat)->modify('+15 minutes');
                $qrHidup = (new DateTime()) < $kadaluarsa;
                $labelJenis = $s['jenis'] === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';
            ?>
                <div class="card" style="background:#f8fafc; box-shadow:none; border:1px solid #e5e7eb;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span class="badge-role" style="background:<?= $s['jenis'] === 'masuk' ? '#2563eb' : '#7c3aed' ?>"><?= $labelJenis ?></span>
                            <b><?= htmlspecialchars($s['mapel']) ?></b> — <?= htmlspecialchars($s['nama_kelas']) ?><br>
                            <small>
                                Jam <?= substr($s['jam_mulai'],0,5) ?> - <?= substr($s['jam_selesai'],0,5) ?> |
                                Sudah absen: <?= $s['jumlah_hadir'] ?> siswa |
                                QR: <?= $qrHidup ? '<span style="color:#15803d">masih berlaku</span>' : '<span style="color:#b91c1c">kadaluarsa (15 menit)</span>' ?>
                            </small>
                        </div>
                        <div>
                            <a class="btn" href="lihat_qr.php?id=<?= $s['id'] ?>">Tampilkan QR</a>
                            <a class="btn btn-outline" href="?tutup=<?= $s['id'] ?>" onclick="return confirm('Tutup sesi ini?')">Tutup Sesi</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
