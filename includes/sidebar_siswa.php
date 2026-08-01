<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar">
    <h2>🎓 Panel Siswa</h2>
    <a href="dashboard.php" class="<?= $cur === 'dashboard.php' ? 'active' : '' ?>">📷 Scan Absen</a>
    <a href="riwayat_absen.php" class="<?= $cur === 'riwayat_absen.php' ? 'active' : '' ?>">📄 Riwayat Absen Saya</a>
    <a href="../logout.php" style="color:#f87171;">🚪 Logout</a>
</div>
