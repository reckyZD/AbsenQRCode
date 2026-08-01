<?php
// ============================================
// PROSES LOGIN
// Alur: cek username -> cek password -> cek role -> redirect dashboard
// ============================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: index.php?error=user_notfound');
    exit;
}

// 1) CEK USERNAME
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php?error=user_notfound');
    exit;
}

if ($user['status'] === 'nonaktif') {
    header('Location: index.php?error=nonaktif');
    exit;
}

// 2) CEK PASSWORD
if (!password_verify($password, $user['password'])) {
    header('Location: index.php?error=wrong_password');
    exit;
}

// 3) CEK ROLE + ambil id referensi (guru/siswa) untuk dipakai di dashboard
$refId = null;
if ($user['role'] === 'guru') {
    $q = $pdo->prepare("SELECT id FROM guru WHERE user_id = ?");
    $q->execute([$user['id']]);
    $refId = $q->fetchColumn();
} elseif ($user['role'] === 'siswa') {
    $q = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ?");
    $q->execute([$user['id']]);
    $refId = $q->fetchColumn();
}

// Simpan session
$_SESSION['user_id']      = $user['id'];
$_SESSION['username']     = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['role']         = $user['role'];
$_SESSION['ref_id']       = $refId;

// 4) REDIRECT SESUAI ROLE
switch ($user['role']) {
    case 'admin':
        header('Location: admin/dashboard.php');
        break;
    case 'guru':
        header('Location: guru/dashboard.php');
        break;
    case 'siswa':
        header('Location: siswa/dashboard.php');
        break;
    default:
        header('Location: index.php?error=akses_ditolak');
}
exit;
