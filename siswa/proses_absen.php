<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('siswa');
header('Content-Type: application/json');

$user    = currentUser();
$siswaId = $user['ref_id'];
$token   = trim($_POST['token'] ?? '');

function respond(bool $success, string $message): void
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($token === '') {
    respond(false, 'QR tidak terbaca.');
}

// Cari sesi berdasarkan token
$stmt = $pdo->prepare("SELECT * FROM sesi_absen WHERE token = ?");
$stmt->execute([$token]);
$sesi = $stmt->fetch();

if (!$sesi) {
    respond(false, 'QR tidak valid.');
}

if ($sesi['status'] !== 'aktif') {
    respond(false, 'Sesi absen sudah ditutup.');
}

if ($sesi['tanggal'] !== date('Y-m-d')) {
    respond(false, 'Sesi absen ini bukan untuk hari ini.');
}

// QR hanya berlaku 15 menit sejak sesi dibuat
$dibuat     = new DateTime($sesi['created_at']);
$kadaluarsa = (clone $dibuat)->modify('+15 minutes');
$sekarang   = new DateTime();
if ($sekarang > $kadaluarsa) {
    respond(false, 'QR sudah kadaluarsa (berlaku hanya 15 menit sejak dibuat). Minta guru membuat sesi baru.');
}

// Pastikan siswa satu kelas dengan sesi tsb
$cekKelas = $pdo->prepare("SELECT kelas_id FROM siswa WHERE id = ?");
$cekKelas->execute([$siswaId]);
$kelasSiswa = $cekKelas->fetchColumn();

if ($kelasSiswa != $sesi['kelas_id']) {
    respond(false, 'QR ini bukan untuk kelas Anda.');
}

// Cek sudah absen atau belum di sesi ini
$cekAbsen = $pdo->prepare("SELECT id FROM absensi WHERE sesi_id = ? AND siswa_id = ?");
$cekAbsen->execute([$sesi['id'], $siswaId]);
if ($cekAbsen->fetch()) {
    $label = $sesi['jenis'] === 'masuk' ? 'absen masuk' : 'absen pulang';
    respond(false, "Anda sudah $label di sesi ini.");
}

// Untuk absen PULANG khusus hari ini, wajib sudah absen MASUK dulu di kelas yang sama
if ($sesi['jenis'] === 'pulang') {
    $cekMasuk = $pdo->prepare("
        SELECT a.id FROM absensi a
        JOIN sesi_absen sa ON sa.id = a.sesi_id
        WHERE a.siswa_id = ? AND sa.jenis = 'masuk' AND sa.tanggal = ? AND sa.kelas_id = ?
    ");
    $cekMasuk->execute([$siswaId, $sesi['tanggal'], $sesi['kelas_id']]);
    if (!$cekMasuk->fetch()) {
        respond(false, 'Anda belum tercatat absen masuk hari ini, tidak bisa absen pulang.');
    }
}

// Tentukan status:
// - Absen masuk: hadir/telat berdasarkan toleransi 15 menit dari jam_mulai sesi
// - Absen pulang: selalu tercatat 'hadir' (checked out)
if ($sesi['jenis'] === 'masuk') {
    $batasTelat = new DateTime($sesi['tanggal'] . ' ' . $sesi['jam_mulai']);
    $batasTelat->modify('+15 minutes');
    $status = $sekarang > $batasTelat ? 'telat' : 'hadir';
} else {
    $status = 'hadir';
}

$insert = $pdo->prepare("INSERT INTO absensi (sesi_id, siswa_id, status) VALUES (?,?,?)");
$insert->execute([$sesi['id'], $siswaId, $status]);

$labelJenis = $sesi['jenis'] === 'masuk' ? 'Absen masuk' : 'Absen pulang';
respond(true, "$labelJenis berhasil! Status: " . ucfirst($status));
