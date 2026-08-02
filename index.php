<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/auth.php';

// Kalau sudah login, langsung lempar ke dashboard sesuai role
if (isset($_SESSION['role'])) {
    header('Location: ' . $_SESSION['role'] . '/dashboard.php');
    exit;
}

$error = $_GET['error'] ?? '';
$errorMsg = [
    'user_notfound' => 'Username tidak ditemukan.',
    'wrong_password' => 'Password salah.',
    'belum_login'    => 'Silakan login terlebih dahulu.',
    'akses_ditolak'  => 'Anda tidak punya akses ke halaman tersebut.',
    'nonaktif'       => 'Akun Anda nonaktif, hubungi admin.',
][$error] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Absensi QR Code | <?= htmlspecialchars(NAMA_SEKOLAH) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <div id="stars"></div>
    <div id="stars2"></div>
    <div id="stars3"></div>
<div class="login-wrap">
    <div class="login-box">
        <img src="assets/img/deha.svg" alt="Logo <?= htmlspecialchars(NAMA_SEKOLAH) ?>" class="login-logo">
        <h1>Absensi QR Code</h1>
        <h3><?= htmlspecialchars(NAMA_SEKOLAH) ?></h3>
        <p class="sub"> Tahun Pelajaran <?= htmlspecialchars(TAHUN_PELAJARAN) ?></p>
        <?php if ($errorMsg): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-block">Login</button>
        </form>
    </div>
</div>

</div>
</body>
</html>
