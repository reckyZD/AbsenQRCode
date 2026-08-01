<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('guru');
$user = currentUser();
$guruId = $user['ref_id'];

$kelasList = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
$jenis = $_GET['jenis'] ?? 'masuk';
if (!in_array($jenis, ['masuk', 'pulang'])) $jenis = 'masuk';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kelasId    = $_POST['kelas_id'];
    $jenisPost  = $_POST['jenis'] === 'pulang' ? 'pulang' : 'masuk';
    $mapel      = trim($_POST['mapel']);
    $jamMulai   = $_POST['jam_mulai'];
    $jamSelesai = $_POST['jam_selesai'];
    $token      = bin2hex(random_bytes(16)); // token unik untuk isi QR, berlaku 15 menit sejak dibuat

    $stmt = $pdo->prepare("
        INSERT INTO sesi_absen (guru_id, kelas_id, jenis, mapel, tanggal, jam_mulai, jam_selesai, token, status)
        VALUES (?,?,?,?,CURDATE(),?,?,?, 'aktif')
    ");
    $stmt->execute([$guruId, $kelasId, $jenisPost, $mapel, $jamMulai, $jamSelesai, $token]);
    $sesiId = $pdo->lastInsertId();

    header('Location: lihat_qr.php?id=' . $sesiId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Sesi Absen</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_guru.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Buat Sesi Absen Baru</h1></div>

        <div class="card" style="max-width:480px;">
            <div class="alert alert-success" style="background:#dbeafe; color:#1e40af;">
                QR code yang dihasilkan hanya <b>berlaku selama 15 menit</b> sejak dibuat.
                Setelah itu siswa tidak bisa scan lagi walaupun sesi belum ditutup manual.
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Jenis Absen</label>
                    <select name="jenis" required>
                        <option value="masuk" <?= $jenis === 'masuk' ? 'selected' : '' ?>>Absen Masuk</option>
                        <option value="pulang" <?= $jenis === 'pulang' ? 'selected' : '' ?>>Absen Pulang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas_id" required>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran / Keterangan</label>
                    <input type="text" name="mapel" required placeholder="Contoh: Matematika, atau 'Pulang Sekolah'">
                </div>
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="jam_mulai" required>
                </div>
                <div class="form-group">
                    <label>Jam Selesai</label>
                    <input type="time" name="jam_selesai" required>
                </div>
                <button class="btn btn-block" type="submit">Buat Sesi & Tampilkan QR</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
