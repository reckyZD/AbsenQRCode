<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');
$user = currentUser();
$msg = '';

// Tambah guru baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama_lengkap']);
    $nip      = trim($_POST['nip']);
    $mapel    = trim($_POST['mapel']);
    $pass     = trim($_POST['password']);

    $cek = $pdo->prepare("SELECT id FROM users WHERE username=?");
    $cek->execute([$username]);
    if ($cek->fetch()) {
        $msg = "Username sudah dipakai.";
    } else {
        $pdo->prepare("INSERT INTO users (username,password,role,nama_lengkap) VALUES (?,?,?,?)")
            ->execute([$username, password_hash($pass, PASSWORD_DEFAULT), 'guru', $nama]);
        $uid = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO guru (user_id,nip,mapel) VALUES (?,?,?)")->execute([$uid, $nip, $mapel]);
        $msg = "Guru berhasil ditambahkan.";
    }
}

// Hapus guru
if (isset($_GET['hapus'])) {
    $uid = $pdo->prepare("SELECT user_id FROM guru WHERE id=?");
    $uid->execute([$_GET['hapus']]);
    $u = $uid->fetchColumn();
    if ($u) $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$u]); // cascade hapus guru juga
    $msg = "Guru berhasil dihapus.";
}

$listGuru = $pdo->query("
    SELECT g.id, u.username, u.nama_lengkap, g.nip, g.mapel
    FROM guru g JOIN users u ON u.id = g.user_id ORDER BY u.nama_lengkap
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Guru</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Kelola Guru</h1></div>
        <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

        <div class="card">
            <h3>Tambah Guru Baru</h3>
            <form method="POST">
                <div class="stat-grid">
                    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                    <div class="form-group"><label>Password</label><input type="text" name="password" required></div>
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required></div>
                    <div class="form-group"><label>NIP</label><input type="text" name="nip"></div>
                    <div class="form-group"><label>Mata Pelajaran</label><input type="text" name="mapel"></div>
                </div>
                <button class="btn" type="submit" name="tambah">Simpan Guru</button>
            </form>
        </div>

        <div class="card">
            <h3>Daftar Guru</h3>
            <table>
                <tr><th>Username</th><th>Nama</th><th>NIP</th><th>Mapel</th><th>Aksi</th></tr>
                <?php foreach ($listGuru as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['username']) ?></td>
                    <td><?= htmlspecialchars($g['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($g['nip']) ?></td>
                    <td><?= htmlspecialchars($g['mapel']) ?></td>
                    <td>
                        <a href="lihat_guru.php?id=<?= $g['id'] ?>">Lihat</a> |
                        <a href="?hapus=<?= $g['id'] ?>" onclick="return confirm('Hapus guru ini?')" style="color:#dc2626">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
