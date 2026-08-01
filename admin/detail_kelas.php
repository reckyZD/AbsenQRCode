<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');
$msg = '';

$kelasId = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ?");
$stmt->execute([$kelasId]);
$kelas = $stmt->fetch();

if (!$kelas) {
    header('Location: kelola_kelas.php');
    exit;
}

// Tambah siswa BARU langsung ke kelas ini
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_baru'])) {
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama_lengkap']);
    $nis      = trim($_POST['nis']);
    $pass     = trim($_POST['password']);

    $cek = $pdo->prepare("SELECT id FROM users WHERE username=?");
    $cek->execute([$username]);
    if ($cek->fetch()) {
        $msg = ['error', 'Username sudah dipakai.'];
    } else {
        $pdo->prepare("INSERT INTO users (username,password,role,nama_lengkap) VALUES (?,?,?,?)")
            ->execute([$username, password_hash($pass, PASSWORD_DEFAULT), 'siswa', $nama]);
        $uid = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO siswa (user_id,nis,kelas_id) VALUES (?,?,?)")->execute([$uid, $nis, $kelasId]);
        $msg = ['success', 'Siswa baru berhasil ditambahkan ke kelas ini.'];
    }
}

// Pindahkan siswa yang SUDAH ADA (belum punya kelas / dari kelas lain) ke kelas ini
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_existing'])) {
    $siswaId = $_POST['siswa_id'];
    if ($siswaId !== '') {
        $pdo->prepare("UPDATE siswa SET kelas_id = ? WHERE id = ?")->execute([$kelasId, $siswaId]);
        $msg = ['success', 'Siswa berhasil dipindahkan ke kelas ini.'];
    }
}

// Hapus siswa DARI KELAS INI (bukan hapus akun, cuma lepas dari kelas)
if (isset($_GET['keluarkan'])) {
    $pdo->prepare("UPDATE siswa SET kelas_id = NULL WHERE id = ? AND kelas_id = ?")
        ->execute([$_GET['keluarkan'], $kelasId]);
    header('Location: detail_kelas.php?id=' . $kelasId);
    exit;
}

$anggota = $pdo->prepare("
    SELECT s.id, u.nama_lengkap, s.nis, u.username
    FROM siswa s JOIN users u ON u.id = s.user_id
    WHERE s.kelas_id = ? ORDER BY u.nama_lengkap
");
$anggota->execute([$kelasId]);
$anggota = $anggota->fetchAll();

// Siswa lain yang belum masuk kelas ini (kelas_id NULL atau kelas lain), untuk dipindahkan
$siswaLain = $pdo->prepare("
    SELECT s.id, u.nama_lengkap, k.nama_kelas
    FROM siswa s JOIN users u ON u.id = s.user_id
    LEFT JOIN kelas k ON k.id = s.kelas_id
    WHERE s.kelas_id IS NULL OR s.kelas_id != ?
    ORDER BY u.nama_lengkap
");
$siswaLain->execute([$kelasId]);
$siswaLain = $siswaLain->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Kelas</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Kelas <?= htmlspecialchars($kelas['nama_kelas']) ?></h1>
            <a class="btn btn-outline" href="kelola_kelas.php">&larr; Kembali</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg[0] ?>"><?= htmlspecialchars($msg[1]) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Daftar Siswa (<?= count($anggota) ?>)</h3>
            <table>
                <tr><th>Nama</th><th>NIS</th><th>Username</th><th>Aksi</th></tr>
                <?php if (!$anggota): ?><tr><td colspan="4">Belum ada siswa di kelas ini.</td></tr><?php endif; ?>
                <?php foreach ($anggota as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($a['nis']) ?></td>
                    <td><?= htmlspecialchars($a['username']) ?></td>
                    <td>
                        <a href="lihat_siswa.php?id=<?= $a['id'] ?>">Lihat</a> |
                        <a href="?id=<?= $kelasId ?>&keluarkan=<?= $a['id'] ?>"
                           onclick="return confirm('Keluarkan siswa ini dari kelas? (akun tidak dihapus)')"
                           style="color:#dc2626">Keluarkan</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="card" style="max-width:520px;">
            <h3>Tambah Siswa Baru ke Kelas Ini</h3>
            <form method="POST">
                <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password</label><input type="text" name="password" required></div>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required></div>
                <div class="form-group"><label>NIS</label><input type="text" name="nis"></div>
                <button class="btn" type="submit" name="tambah_baru">Tambah Siswa</button>
            </form>
        </div>

        <div class="card" style="max-width:520px;">
            <h3>Pindahkan Siswa yang Sudah Ada</h3>
            <?php if (!$siswaLain): ?>
                <p>Semua siswa sudah berada di kelas ini.</p>
            <?php else: ?>
            <form method="POST" style="display:flex; gap:10px; align-items:end;">
                <div class="form-group" style="flex:1">
                    <label>Pilih Siswa</label>
                    <select name="siswa_id" required>
                        <option value="">-- pilih siswa --</option>
                        <?php foreach ($siswaLain as $s): ?>
                            <option value="<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['nama_lengkap']) ?>
                                (<?= htmlspecialchars($s['nama_kelas'] ?? 'belum ada kelas') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn" type="submit" name="tambah_existing">Pindahkan ke Sini</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
