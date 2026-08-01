<?php
// ============================================
// JALANKAN FILE INI SEKALI LEWAT BROWSER
// untuk membuat akun contoh admin, guru, siswa.
// Setelah berhasil, HAPUS file ini dari server.
// ============================================
require_once 'config/database.php';

try {
    $pdo->beginTransaction();

    // Admin
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?,?,?,?)");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'Administrator']);

    // Guru
    $stmt->execute(['guru1', password_hash('guru123', PASSWORD_DEFAULT), 'guru', 'Budi Santoso, S.Pd']);
    $guruUserId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO guru (user_id, nip, mapel) VALUES (?,?,?)")
        ->execute([$guruUserId, '198501012010011001', 'Matematika']);

    // Siswa
    $stmt->execute(['siswa1', password_hash('siswa123', PASSWORD_DEFAULT), 'siswa', 'Andi Pratama']);
    $siswaUserId = $pdo->lastInsertId();
    $kelasId = $pdo->query("SELECT id FROM kelas ORDER BY id LIMIT 1")->fetchColumn();
    $pdo->prepare("INSERT INTO siswa (user_id, nis, kelas_id) VALUES (?,?,?)")
        ->execute([$siswaUserId, '2024001', $kelasId]);

    $pdo->commit();
    echo "<h2 style='font-family:Arial'>Seed berhasil!</h2>";
    echo "<p style='font-family:Arial'>Akun contoh:<br>
        admin / admin123<br>
        guru1 / guru123<br>
        siswa1 / siswa123</p>
        <p style='font-family:Arial;color:red'><b>Hapus file seed.php ini sekarang.</b></p>
        <a href='index.php'>Ke halaman login</a>";
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Gagal seed (mungkin sudah pernah dijalankan): " . $e->getMessage());
}
