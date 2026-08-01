<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('guru');
$user = currentUser();
$guruId = $user['ref_id'];

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT s.*, k.nama_kelas FROM sesi_absen s
    JOIN kelas k ON k.id = s.kelas_id
    WHERE s.id = ? AND s.guru_id = ?
");
$stmt->execute([$id, $guruId]);
$sesi = $stmt->fetch();

if (!$sesi) {
    header('Location: dashboard.php');
    exit;
}

// Hitung sisa waktu berlaku QR (15 menit sejak dibuat)
$dibuat       = new DateTime($sesi['created_at']);
$kadaluarsa   = (clone $dibuat)->modify('+15 minutes');
$sekarang     = new DateTime();
$sisaDetik    = $kadaluarsa->getTimestamp() - $sekarang->getTimestamp();
$qrMasihHidup = $sisaDetik > 0 && $sesi['status'] === 'aktif';

$labelJenis = $sesi['jenis'] === 'masuk' ? 'Absen Masuk' : 'Absen Pulang';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QR Sesi Absen</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_guru.php'; ?>
    <div class="main">
        <div class="topbar"><h1><?= $labelJenis ?> — <?= htmlspecialchars($sesi['mapel']) ?> (<?= htmlspecialchars($sesi['nama_kelas']) ?>)</h1></div>

        <div class="card qr-box">
            <?php if ($qrMasihHidup): ?>
                <p style="margin-bottom:14px;">Siswa memindai QR di bawah ini untuk <?= strtolower($labelJenis) ?>.</p>
                <div id="qrcode" style="display:inline-block; padding:16px; background:#fff; border-radius:12px; border:1px solid #eee;"></div>
                <p style="margin-top:14px; font-size:20px; font-weight:700; color:#b45309;">
                    Sisa waktu: <span id="countdown"></span>
                </p>
            <?php else: ?>
                <div id="qrcode" style="display:none;"></div>
                <div class="alert alert-error" style="display:inline-block;">
                    QR sudah <b>kadaluarsa</b> (berlaku hanya 15 menit). Buat sesi baru jika masih diperlukan.
                </div>
                <div style="margin-top:14px;">
                    <a class="btn" href="buat_sesi.php?jenis=<?= $sesi['jenis'] ?>">Buat Sesi Baru</a>
                </div>
            <?php endif; ?>
            <p style="margin-top:14px; color:#666; font-size:13px;">
                Jam <?= substr($sesi['jam_mulai'],0,5) ?> - <?= substr($sesi['jam_selesai'],0,5) ?> |
                Status Sesi: <span class="status-<?= $sesi['status'] ?>"><?= ucfirst($sesi['status']) ?></span>
            </p>
        </div>

        <div class="card">
            <h3>Daftar <?= $sesi['jenis'] === 'masuk' ? 'Hadir' : 'Sudah Pulang' ?> (update otomatis)</h3>
            <table id="tabel-hadir">
                <tr><th>Nama Siswa</th><th>NIS</th><th>Waktu Absen</th><th>Status</th></tr>
            </table>
        </div>
    </div>
</div>

<script>
<?php if ($qrMasihHidup): ?>
// Isi QR berupa token unik sesi ini -> dibaca siswa lewat scanner
new QRCode(document.getElementById("qrcode"), {
    text: "<?= $sesi['token'] ?>",
    width: 240,
    height: 240
});

// Countdown 15 menit
let sisaDetik = <?= $sisaDetik ?>;
const countdownEl = document.getElementById('countdown');
function tickCountdown() {
    if (sisaDetik <= 0) {
        location.reload(); // reload supaya tampil status kadaluarsa
        return;
    }
    const m = Math.floor(sisaDetik / 60);
    const s = sisaDetik % 60;
    countdownEl.textContent = m + ':' + String(s).padStart(2, '0');
    sisaDetik--;
}
tickCountdown();
setInterval(tickCountdown, 1000);
<?php endif; ?>

// Polling daftar hadir tiap 4 detik
function muatHadir() {
    fetch('ambil_hadir.php?sesi_id=<?= $sesi['id'] ?>')
        .then(r => r.json())
        .then(data => {
            let rows = '<tr><th>Nama Siswa</th><th>NIS</th><th>Waktu Absen</th><th>Status</th></tr>';
            if (data.length === 0) {
                rows += '<tr><td colspan="4">Belum ada siswa yang absen.</td></tr>';
            }
            data.forEach(d => {
                rows += `<tr><td>${d.nama_lengkap}</td><td>${d.nis}</td><td>${d.waktu_absen}</td>
                         <td><span class="status-${d.status}">${d.status}</span></td></tr>`;
            });
            document.getElementById('tabel-hadir').innerHTML = rows;
        });
}
muatHadir();
setInterval(muatHadir, 4000);
</script>
</body>
</html>
