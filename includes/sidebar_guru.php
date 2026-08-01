<?php
$cur = basename($_SERVER['PHP_SELF']);
$u   = currentUser();
?>
<button class="mobile-topbar-toggle" onclick="toggleSidebarMobile()" aria-label="Buka menu">&#9776;</button>
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebarMobile()"></div>

<div class="sidebar" id="mainSidebar">
    <div class="sidebar-header">
        <h2>&#128104;&#8205;&#127979; Guru</h2>
        <button class="sidebar-toggle-btn" onclick="toggleSidebarCollapse()" title="Ciutkan/lebarkan menu">&#8644;</button>
    </div>

    <div class="sidebar-profile">
        <div class="profile-square">
            <?php if (!empty($u['foto'])): ?>
                <img src="../<?= htmlspecialchars($u['foto']) ?>" alt="Foto Profil">
            <?php else: ?>
                <?= htmlspecialchars(inisialNama($u['nama'])) ?>
            <?php endif; ?>
        </div>
        <div class="side-username"><?= htmlspecialchars($u['nama']) ?></div>
        <div class="side-role">Guru</div>
        <div class="side-schoolname"><?= htmlspecialchars(NAMA_SEKOLAH) ?></div>
        <a href="profil.php" class="edit-profil-link">&#9998;&#65039; Edit Profil</a>
    </div>

    <nav>
        <a href="dashboard.php" class="nav-link <?= $cur === 'dashboard.php' ? 'active' : '' ?>"><span>&#128202;</span><span class="side-label">Dashboard</span></a>
        <a href="buat_sesi.php" class="nav-link <?= $cur === 'buat_sesi.php' ? 'active' : '' ?>"><span>&#10133;</span><span class="side-label">Buat Sesi Absen</span></a>
        <a href="riwayat_absen.php" class="nav-link <?= $cur === 'riwayat_absen.php' ? 'active' : '' ?>"><span>&#128196;</span><span class="side-label">Riwayat Absensi</span></a>
        <a href="../logout.php" class="nav-link logout-link"><span>&#128682;</span><span class="side-label">Logout</span></a>
    </nav>
</div>
<script src="../assets/js/app.js"></script>
