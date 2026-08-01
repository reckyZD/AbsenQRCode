<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');
$msg = '';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.nama_lengkap, u.status AS status_akun, u.created_at, k.nama_kelas
    FROM siswa s
    JOIN users u ON u.id = s.user_id
    LEFT JOIN kelas k ON k.id = s.kelas_id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    header('Location: kelola_siswa.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $passBaru = trim($_POST['password_baru']);
    if (strlen($passBaru) < 4) {
        $msg = ['error', 'Password minimal 4 karakter.'];
    } else {
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
            ->execute([password_hash($passBaru, PASSWORD_DEFAULT), $siswa['user_id']]);
        $msg = ['success', "Password berhasil direset menjadi: $passBaru"];
    }
}

if (isset($_GET['toggle'])) {
    $statusBaru = $siswa['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
    $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$statusBaru, $siswa['user_id']]);
    header('Location: lihat_siswa.php?id=' . $id);
    exit;
}

// Riwayat absen ringkas (10 terakhir, gabung masuk & pulang)
$riwayat = $pdo->prepare("
    SELECT sa.tanggal, sa.jenis, sa.mapel, a.waktu_absen, a.status
    FROM absensi a
    JOIN sesi_absen sa ON sa.id = a.sesi_id
    WHERE a.siswa_id = ?
    ORDER BY a.waktu_absen DESC
    LIMIT 10
");
$riwayat->execute([$id]);
$riwayat = $riwayat->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Siswa</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Detail Siswa</h1>
            <a class="btn btn-outline" href="kelola_siswa.php">&larr; Kembali</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg[0] ?>"><?= htmlspecialchars($msg[1]) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Informasi Akun</h3>
            <table>
                <tr><th style="width:180px">Nama Lengkap</th><td><?= htmlspecialchars($siswa['nama_lengkap']) ?></td></tr>
                <tr><th>Username</th><td><?= htmlspecialchars($siswa['username']) ?></td></tr>
                <tr><th>NIS</th><td><?= htmlspecialchars($siswa['nis'] ?: '-') ?></td></tr>
                <tr><th>Kelas</th><td><?= htmlspecialchars($siswa['nama_kelas'] ?: '-') ?></td></tr>
                <tr><th>Status Akun</th><td><span class="status-<?= $siswa['status_akun'] === 'aktif' ? 'aktif' : 'telat' ?>"><?= ucfirst($siswa['status_akun']) ?></span></td></tr>
                <tr><th>Terdaftar Sejak</th><td><?= $siswa['created_at'] ?></td></tr>
            </table>
            <div style="margin-top:14px;">
                <a class="btn btn-outline" href="?id=<?= $id ?>&toggle=1" onclick="return confirm('Ubah status akun ini?')">
                    <?= $siswa['status_akun'] === 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>
                </a>
            </div>
        </div>

        <div class="card" style="max-width:420px;">
            <h3>Reset Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="text" name="password_baru" required minlength="4" placeholder="Masukkan password baru">
                </div>
                <button class="btn" type="submit" name="reset_password">Reset Password</button>
            </form>
        </div>

        <div class="card">
            <h3>10 Riwayat Absen Terakhir</h3>
            <table>
                <tr><th>Tanggal</th><th>Jenis</th><th>Mapel</th><th>Waktu</th><th>Status</th></tr>
                <?php if (!$riwayat): ?><tr><td colspan="5">Belum ada riwayat.</td></tr><?php endif; ?>
                <?php foreach ($riwayat as $r): ?>
                <tr>
                    <td><?= $r['tanggal'] ?></td>
                    <td><?= $r['jenis'] === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' ?></td>
                    <td><?= htmlspecialchars($r['mapel']) ?></td>
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
