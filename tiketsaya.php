<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil nama user
$namaUser = null;
$stmtUser = $conn->prepare("SELECT nama FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$stmtUser->bind_result($nama);
if ($stmtUser->fetch()) {
    $namaUser = $nama;
}
$stmtUser->close();

// Ambil tiket yang telah dipesan oleh user
$tiketSaya = [];
$stmt = $conn->prepare("SELECT p.*, t.tanggal, t.waktu_berangkat, t.waktu_tiba, ka.nama_kota AS asal, kt.nama_kota AS tujuan
                        FROM pemesanan p
                        JOIN tiket t ON p.tiket_id = t.id
                        JOIN kota ka ON t.kota_asal_id = ka.id
                        JOIN kota kt ON t.kota_tujuan_id = kt.id
                        WHERE p.user_id = ? ORDER BY p.id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tiketSaya[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tiket Saya</title>
  <link rel="stylesheet" href="css/stylesTiketSaya.css">
</head>
<body>
<header>
  <h1>Putri Transway</h1>
  <nav>
    <a href="index.php">Beranda</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<section class="ticket-list">
  <h2>Tiket Anda, <?= htmlspecialchars($namaUser) ?></h2>

  <?php if (count($tiketSaya) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Asal</th>
          <th>Tujuan</th>
          <th>Tanggal</th>
          <th>Waktu</th>
          <th>Jumlah Tiket</th>
          <th>Total Harga</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tiketSaya as $tiket): ?>
          <tr>
            <td><?= htmlspecialchars($tiket['asal']) ?></td>
            <td><?= htmlspecialchars($tiket['tujuan']) ?></td>
            <td><?= htmlspecialchars($tiket['tanggal']) ?></td>
            <td><?= htmlspecialchars($tiket['waktu_berangkat']) ?> - <?= htmlspecialchars($tiket['waktu_tiba']) ?></td>
            <td><?= $tiket['jumlah_tiket'] ?></td>
            <td>Rp<?= number_format($tiket['total_harga'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($tiket['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Anda belum memesan tiket.</p>
  <?php endif; ?>
</section>

<footer>
  <p>© 2025 Putri Transway</p>
</footer>
</body>
</html>