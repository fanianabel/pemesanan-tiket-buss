<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil riwayat pemesanan user
$query = "
    SELECT p.id AS pemesanan_id, p.jumlah_tiket, p.total_harga, p.metode_pembayaran, p.status,
           j.lokasi_keberangkatan, j.tujuan, j.tanggal_keberangkatan, j.jam_berangkat, j.jam_tiba
    FROM pemesanan p
    JOIN jadwal_bus j ON p.jadwal_id = j.id
    WHERE p.user_id = ?
    ORDER BY p.id DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pemesananList = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Pemesanan</title>
  <link rel="stylesheet" href="css/stylesCetakTiket.css">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    table, th, td {
      border: 1px solid #ccc;
    }
    th, td {
      padding: 10px;
      text-align: center;
    }
    .status-belum { color: orange; font-weight: bold; }
    .status-verifikasi { color: blue; font-weight: bold; }
    .status-dibayar { color: green; font-weight: bold; }
    .status-lain { color: red; }
  </style>
</head>
<body>
  <header>
    <h1>Putri Transway</h1>
    <nav>
      <a href="index.php">Beranda</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <section>
    <h2>Riwayat Pemesanan Anda</h2>
    <?php if (count($pemesananList) > 0): ?>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Rute</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Jumlah Tiket</th>
            <th>Total Harga</th>
            <th>Metode Pembayaran</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pemesananList as $index => $data): ?>
            <?php
              $status = strtolower($data['status']);
              $metode = strtolower($data['metode_pembayaran']);
              $statusText = "";
              $statusClass = "";

              if ($metode === 'cod' && $status === 'belum dibayar') {
                  $statusText = "Belum Dibayar (COD)";
                  $statusClass = "status-belum";
              } elseif ($status === 'menunggu verifikasi') {
                  $statusText = "Menunggu Verifikasi Admin";
                  $statusClass = "status-verifikasi";
              } elseif ($status === 'dibayar') {
                  $statusText = "Dibayar";
                  $statusClass = "status-dibayar";
              } else {
                  $statusText = $status;
                  $statusClass = "status-lain";
              }
            ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($data['lokasi_keberangkatan']) ?> → <?= htmlspecialchars($data['tujuan']) ?></td>
              <td><?= htmlspecialchars($data['tanggal_keberangkatan']) ?></td>
              <td><?= htmlspecialchars($data['jam_berangkat']) ?> - <?= htmlspecialchars($data['jam_tiba']) ?></td>
              <td><?= $data['jumlah_tiket'] ?></td>
              <td>Rp<?= number_format($data['total_harga'], 0, ',', '.') ?></td>
              <td><?= htmlspecialchars(ucwords($metode)) ?></td>
              <td class="<?= $statusClass ?>"><?= $statusText ?></td>
              <td>
                <?php if ($status === 'dibayar'): ?>
                  <a href="tiket.php?id=<?= $data['pemesanan_id'] ?>" target="_blank">Lihat Tiket</a>
                <?php else: ?>
                  <em>Belum tersedia</em>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>Anda belum memiliki riwayat pemesanan.</p>
    <?php endif; ?>
  </section>

  <footer>
    <p>© 2025 Putri Transway</p>
  </footer>
</body>
</html>