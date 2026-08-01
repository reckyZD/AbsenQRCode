<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('siswa');
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Scan Absen</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_siswa.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Scan QR Absen</h1>
            <div>Halo, <b><?= htmlspecialchars($user['nama']) ?></b> <span class="badge-role">SISWA</span></div>
        </div>

        <div class="card qr-box">
            <p style="margin-bottom:14px;">Arahkan kamera ke QR code yang ditampilkan guru di kelas.</p>
            <div id="qr-reader"></div>
            <div id="hasil-scan" style="margin-top:16px; font-weight:600;"></div>
        </div>
    </div>
</div>

<script>
const hasilEl = document.getElementById('hasil-scan');
let sedangProses = false;

function tampilkanPesan(teks, warna) {
    hasilEl.style.color = warna;
    hasilEl.textContent = teks;
}

function onScanSuccess(decodedText) {
    if (sedangProses) return;
    sedangProses = true;

    fetch('proses_absen.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'token=' + encodeURIComponent(decodedText)
    })
    .then(r => r.json())
    .then(res => {
        tampilkanPesan(res.message, res.success ? '#15803d' : '#b91c1c');
        // beri jeda sebelum boleh scan lagi (mencegah dobel-submit QR yang sama)
        setTimeout(() => { sedangProses = false; }, 3000);
    })
    .catch(() => {
        tampilkanPesan('Terjadi kesalahan, coba lagi.', '#b91c1c');
        sedangProses = false;
    });
}

const html5QrCode = new Html5Qrcode("qr-reader");
html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 240 },
    onScanSuccess
).catch(err => {
    tampilkanPesan('Tidak bisa mengakses kamera: ' + err, '#b91c1c');
});
</script>
</body>
</html>
