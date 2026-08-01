<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_kelas']);
    if ($nama !== '') {
        $pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)")->execute([$nama]);
        $msg = "Kelas berhasil ditambahkan.";
    }
}
if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM kelas WHERE id=?")->execute([$_GET['hapus']]);
    $msg = "Kelas berhasil dihapus.";
}

$kelasList = $pdo->query("
    SELECT k.*, (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) AS jumlah_siswa
    FROM kelas k ORDER BY nama_kelas
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kelola Kelas</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Kelola Kelas</h1></div>
        <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

        <div class="card">
            <h3>Tambah Kelas</h3>
            <form method="POST" style="display:flex; gap:10px; align-items:end;">
                <div class="form-group" style="flex:1"><label>Nama Kelas</label><input type="text" name="nama_kelas" required></div>
                <button class="btn" type="submit" name="tambah">Simpan</button>
            </form>
        </div>

        <div class="card">
            <h3>Daftar Kelas</h3>
            <table>
                <tr><th>Nama Kelas</th><th>Jumlah Siswa</th><th>Aksi</th></tr>
                <?php foreach ($kelasList as $k): ?>
                <tr>
                    <td><?= htmlspecialchars($k['nama_kelas']) ?></td>
                    <td><?= $k['jumlah_siswa'] ?> siswa</td>
                    <td>
                        <a href="detail_kelas.php?id=<?= $k['id'] ?>">Lihat</a> |
                        <a href="?hapus=<?= $k['id'] ?>" onclick="return confirm('Hapus kelas ini?')" style="color:#dc2626">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
