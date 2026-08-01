<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama_lengkap']);
    $nis      = trim($_POST['nis']);
    $kelasId  = $_POST['kelas_id'];
    $pass     = trim($_POST['password']);

    $cek = $pdo->prepare("SELECT id FROM users WHERE username=?");
    $cek->execute([$username]);
    if ($cek->fetch()) {
        $msg = "Username sudah dipakai.";
    } else {
        $pdo->prepare("INSERT INTO users (username,password,role,nama_lengkap) VALUES (?,?,?,?)")
            ->execute([$username, password_hash($pass, PASSWORD_DEFAULT), 'siswa', $nama]);
        $uid = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO siswa (user_id,nis,kelas_id) VALUES (?,?,?)")->execute([$uid, $nis, $kelasId]);
        $msg = "Siswa berhasil ditambahkan.";
    }
}

if (isset($_GET['hapus'])) {
    $uid = $pdo->prepare("SELECT user_id FROM siswa WHERE id=?");
    $uid->execute([$_GET['hapus']]);
    $u = $uid->fetchColumn();
    if ($u) $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$u]);
    $msg = "Siswa berhasil dihapus.";
}

$kelasList = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
$listSiswa = $pdo->query("
    SELECT s.id, u.username, u.nama_lengkap, s.nis, k.nama_kelas
    FROM siswa s
    JOIN users u ON u.id = s.user_id
    LEFT JOIN kelas k ON k.id = s.kelas_id
    ORDER BY u.nama_lengkap
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Siswa</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Kelola Siswa</h1></div>
        <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

        <div class="card">
            <h3>Tambah Siswa Baru</h3>
            <form method="POST">
                <div class="stat-grid">
                    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                    <div class="form-group"><label>Password</label><input type="text" name="password" required></div>
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required></div>
                    <div class="form-group"><label>NIS</label><input type="text" name="nis"></div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" required>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button class="btn" type="submit" name="tambah">Simpan Siswa</button>
            </form>
        </div>

        <div class="card">
            <h3>Daftar Siswa</h3>
            <table>
                <tr><th>Username</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Aksi</th></tr>
                <?php foreach ($listSiswa as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['username']) ?></td>
                    <td><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($s['nis']) ?></td>
                    <td><?= htmlspecialchars($s['nama_kelas'] ?? '-') ?></td>
                    <td>
                        <a href="lihat_siswa.php?id=<?= $s['id'] ?>">Lihat</a> |
                        <a href="?hapus=<?= $s['id'] ?>" onclick="return confirm('Hapus siswa ini?')" style="color:#dc2626">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
