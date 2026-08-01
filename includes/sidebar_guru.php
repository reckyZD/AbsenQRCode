<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar">
    <h2>👨‍🏫 Panel Guru</h2>
    <a href="dashboard.php" class="<?= $cur === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="buat_sesi.php" class="<?= $cur === 'buat_sesi.php' ? 'active' : '' ?>">➕ Buat Sesi Absen</a>
    <a href="riwayat_absen.php" class="<?= $cur === 'riwayat_absen.php' ? 'active' : '' ?>">📄 Riwayat Absensi</a>
    <a href="../logout.php" style="color:#f87171;">🚪 Logout</a>
</div>
