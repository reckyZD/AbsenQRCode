<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireRole('admin');

$kelasFilter = $_GET['kelas_id'] ?? '';
$tglFilter   = $_GET['tanggal'] ?? date('Y-m-d');

$sql = "
    SELECT
        sw.id AS siswa_id,
        u.nama_lengkap AS nama_siswa,
        sw.nis,
        k.nama_kelas,
        sa.tanggal,
        MAX(CASE WHEN sa.jenis = 'masuk'  THEN a.waktu_absen END) AS waktu_masuk,
        MAX(CASE WHEN sa.jenis = 'masuk'  THEN a.status      END) AS status_masuk,
        MAX(CASE WHEN sa.jenis = 'pulang' THEN a.waktu_absen END) AS waktu_pulang
    FROM absensi a
    JOIN siswa sw ON sw.id = a.siswa_id
    JOIN users u ON u.id = sw.user_id
    JOIN sesi_absen sa ON sa.id = a.sesi_id
    JOIN kelas k ON k.id = sa.kelas_id
    WHERE 1=1
";
$params = [];
if ($kelasFilter !== '') { $sql .= " AND k.id = ?"; $params[] = $kelasFilter; }
if ($tglFilter !== '')   { $sql .= " AND sa.tanggal = ?"; $params[] = $tglFilter; }
$sql .= " GROUP BY sw.id, sa.tanggal ORDER BY u.nama_lengkap ASC LIMIT 300";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Absen Masuk &amp; Pulang</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include '../includes/sidebar_admin.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Rekap Absen Masuk &amp; Pulang</h1></div>

        <div class="card">
            <form method="GET" style="display:flex; gap:14px; align-items:end;">
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas_id">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $kelasFilter == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($tglFilter) ?>">
                </div>
                <button class="btn" type="submit">Filter</button>
                <a class="btn btn-outline" href="laporan.php">Reset</a>
            </form>
        </div>

        <div class="card">
            <h3>Hasil (<?= count($data) ?> siswa) — Tanggal <?= htmlspecialchars($tglFilter) ?></h3>
            <table>
                <tr>
                    <th>Nama</th><th>NIS</th><th>Kelas</th>
                    <th>Jam Masuk</th><th>Status Masuk</th>
                    <th>Jam Pulang</th><th>Status</th>
                </tr>
                <?php if (!$data): ?><tr><td colspan="7">Tidak ada data absen di tanggal ini.</td></tr><?php endif; ?>
                <?php foreach ($data as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nama_siswa']) ?></td>
                    <td><?= htmlspecialchars($d['nis']) ?></td>
                    <td><?= htmlspecialchars($d['nama_kelas']) ?></td>
                    <td><?= $d['waktu_masuk'] ? date('H:i:s', strtotime($d['waktu_masuk'])) : '-' ?></td>
                    <td>
                        <?php if ($d['status_masuk']): ?>
                            <span class="status-<?= $d['status_masuk'] ?>"><?= ucfirst($d['status_masuk']) ?></span>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td><?= $d['waktu_pulang'] ? date('H:i:s', strtotime($d['waktu_pulang'])) : '-' ?></td>
                    <td>
                        <?php if ($d['waktu_masuk'] && $d['waktu_pulang']): ?>
                            <span class="status-hadir">Lengkap</span>
                        <?php elseif ($d['waktu_masuk'] && !$d['waktu_pulang']): ?>
                            <span class="status-telat">Belum Pulang</span>
                        <?php else: ?>
                            <span class="status-telat">Belum Masuk</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
