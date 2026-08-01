<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar">
    <h2>⚙️ Admin Panel</h2>
    <a href="dashboard.php" class="<?= $cur === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="kelola_guru.php" class="<?= $cur === 'kelola_guru.php' ? 'active' : '' ?>">👨‍🏫 Kelola Guru</a>
    <a href="kelola_siswa.php" class="<?= $cur === 'kelola_siswa.php' ? 'active' : '' ?>">🎓 Kelola Siswa</a>
    <a href="kelola_kelas.php" class="<?= $cur === 'kelola_kelas.php' ? 'active' : '' ?>">🏫 Kelola Kelas</a>
    <a href="laporan.php" class="<?= $cur === 'laporan.php' ? 'active' : '' ?>">📄 Laporan Absensi</a>
    <a href="../logout.php" style="color:#f87171;">🚪 Logout</a>
</div>
