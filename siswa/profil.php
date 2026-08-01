<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('siswa');
$user = currentUser();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $file = $_FILES['foto'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = ['error', 'Gagal upload foto. Coba lagi.'];
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            $msg = ['error', 'Format file harus JPG, PNG, atau WEBP.'];
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $msg = ['error', 'Ukuran file maksimal 2MB.'];
        } else {
            $namaFile = 'user_' . $user['id'] . '_' . time() . '.' . $ext;
            $tujuan   = '../assets/uploads/profil/' . $namaFile;
            if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                $pathDb = 'assets/uploads/profil/' . $namaFile;
                $pdo->prepare("UPDATE users SET foto_profil = ? WHERE id = ?")->execute([$pathDb, $user['id']]);
                $_SESSION['foto_profil'] = $pathDb;
                $msg = ['success', 'Foto profil berhasil diperbarui.'];
                $user = currentUser();
            } else {
                $msg = ['error', 'Gagal menyimpan file. Pastikan folder assets/uploads/profil bisa ditulis (writable).'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Profil</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_siswa.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Edit Profil</h1></div>
        <?php if ($msg): ?><div class="alert alert-<?= $msg[0] ?>"><?= htmlspecialchars($msg[1]) ?></div><?php endif; ?>

        <div class="card" style="max-width:420px; text-align:center;">
            <div class="profil-photo-preview">
                <?php if (!empty($user['foto'])): ?>
                    <img src="../<?= htmlspecialchars($user['foto']) ?>" alt="Foto Profil">
                <?php else: ?>
                    <?= htmlspecialchars(inisialNama($user['nama'])) ?>
                <?php endif; ?>
            </div>
            <h3 style="margin-bottom:2px;"><?= htmlspecialchars($user['nama']) ?></h3>
            <p style="color:#666; font-size:13px; margin-bottom:20px;">Siswa &middot; <?= htmlspecialchars(NAMA_SEKOLAH) ?></p>

            <form method="POST" enctype="multipart/form-data" style="text-align:left;">
                <div class="form-group">
                    <label>Ganti Foto Profil</label>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" required>
                </div>
                <button class="btn btn-block" type="submit">Simpan Foto</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
