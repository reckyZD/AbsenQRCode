<?php
if (session_status() === PHP_SESSION_NONE) session_start();

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
<title>Login - Absensi QR Code</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h1>Absensi QR Code</h1>
        <p class="sub">Silakan login sesuai akun Anda</p>

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
</body>
</html>
