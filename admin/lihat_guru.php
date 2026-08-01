<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');
$msg = '';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT g.*, u.username, u.nama_lengkap, u.status AS status_akun, u.created_at
    FROM guru g JOIN users u ON u.id = g.user_id
    WHERE g.id = ?
");
$stmt->execute([$id]);
$guru = $stmt->fetch();

if (!$guru) {
    header('Location: kelola_guru.php');
    exit;
}

// Reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $passBaru = trim($_POST['password_baru']);
    if (strlen($passBaru) < 4) {
        $msg = ['error', 'Password minimal 4 karakter.'];
    } else {
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
            ->execute([password_hash($passBaru, PASSWORD_DEFAULT), $guru['user_id']]);
        $msg = ['success', "Password berhasil direset menjadi: $passBaru"];
    }
}

// Aktif / nonaktifkan akun
if (isset($_GET['toggle'])) {
    $statusBaru = $guru['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
    $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$statusBaru, $guru['user_id']]);
    header('Location: lihat_guru.php?id=' . $id);
    exit;
}

// Statistik sesi yang dibuat guru ini
$totalSesi = $pdo->prepare("SELECT COUNT(*) FROM sesi_absen WHERE guru_id = ?");
$totalSesi->execute([$id]);
$totalSesi = $totalSesi->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Guru</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Detail Guru</h1>
            <a class="btn btn-outline" href="kelola_guru.php">&larr; Kembali</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg[0] ?>"><?= htmlspecialchars($msg[1]) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Informasi Akun</h3>
            <table>
                <tr><th style="width:180px">Nama Lengkap</th><td><?= htmlspecialchars($guru['nama_lengkap']) ?></td></tr>
                <tr><th>Username</th><td><?= htmlspecialchars($guru['username']) ?></td></tr>
                <tr><th>NIP</th><td><?= htmlspecialchars($guru['nip'] ?: '-') ?></td></tr>
                <tr><th>Mata Pelajaran</th><td><?= htmlspecialchars($guru['mapel'] ?: '-') ?></td></tr>
                <tr><th>Status Akun</th><td><span class="status-<?= $guru['status_akun'] === 'aktif' ? 'aktif' : 'telat' ?>"><?= ucfirst($guru['status_akun']) ?></span></td></tr>
                <tr><th>Terdaftar Sejak</th><td><?= $guru['created_at'] ?></td></tr>
                <tr><th>Total Sesi Dibuat</th><td><?= $totalSesi ?> sesi</td></tr>
            </table>
            <div style="margin-top:14px;">
                <a class="btn btn-outline" href="?id=<?= $id ?>&toggle=1" onclick="return confirm('Ubah status akun ini?')">
                    <?= $guru['status_akun'] === 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>
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
    </div>
</div>
</body>
</html>
