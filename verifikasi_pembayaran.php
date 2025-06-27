<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}

// Jika admin mengubah status
if (isset($_GET['verifikasi_id'])) {
    $id = (int) $_GET['verifikasi_id'];
    $status = ($_GET['set'] === 'tolak') ? 'ditolak' : 'dibayar';

    $stmt = $conn->prepare("UPDATE pemesanan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: verifikasi_pembayaran.php");
    exit;
}

// Ambil semua pemesanan yang menunggu verifikasi
// $query = "
//     SELECT 
//         p.id, u.nama, p.jumlah_tiket, p.total_harga, p.metode_pembayaran, p.bukti_pembayaran,
//         t.tanggal, t.waktu_berangkat, t.waktu_tiba,
//         ka.nama_kota AS lokasi_keberangkatan,
//         kt.nama_kota AS tujuan
//     FROM pemesanan p
//     JOIN users u ON p.user_id = u.id
//     JOIN tiket t ON p.tiket_id = t.id
//     JOIN kota ka ON t.kota_asal_id = ka.id
//     JOIN kota kt ON t.kota_tujuan_id = kt.id
//     WHERE p.status = 'menunggu_verifikasi' 
//     ORDER BY p.id DESC
// ";


// $query = "
//     SELECT 
//         p.id, u.nama, p.jumlah_tiket, p.total_harga, p.metode_pembayaran, p.bukti_pembayaran,
//         p.status
//     FROM pemesanan p
//     JOIN users u ON p.user_id = u.id
//     WHERE p.status = 'menunggu_verifikasi'
//     ORDER BY p.id DESC
// ";

$query = "
  SELECT 
    p.id, u.nama, p.jumlah_tiket, p.total_harga, 
    p.metode_pembayaran, p.bukti_pembayaran,
    j.lokasi_keberangkatan, j.tujuan,
    j.tanggal_keberangkatan AS tanggal,
    j.jam_berangkat, j.jam_tiba
  FROM pemesanan p
  JOIN users u ON p.user_id = u.id
  JOIN jadwal_bus j ON p.jadwal_id = j.id
  WHERE p.status = 'menunggu_verifikasi'
  ORDER BY p.id DESC
";




$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Pembayaran</title>
    <link rel="stylesheet" href="css/styleDashboardAdmin.css">
</head>
<body>
<header>
    <h1>Verifikasi Pembayaran - Admin</h1>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="verifikasi_pembayaran.php">Verifikasi Pembayaran</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<section>
    <h2>Daftar Pembayaran Menunggu Verifikasi</h2>
    <?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Rute</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Jumlah Tiket</th>
                <th>Total Harga</th>
                <th>Metode</th>
                <th>Bukti</th>
                <th>Aksi</th>
            </tr>
        </thead>
<tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
<td><?= htmlspecialchars($row['lokasi_keberangkatan'] ?? '-') ?> → <?= htmlspecialchars($row['tujuan'] ?? '-') ?></td>
<td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
<td><?= htmlspecialchars(($row['jam_berangkat'] ?? '-') . ' - ' . ($row['jam_tiba'] ?? '-')) ?></td>

        <td><?= $row['jumlah_tiket'] ?? '-' ?></td>
        <td>Rp<?= number_format($row['total_harga'] ?? 0, 0, ',', '.') ?></td>
        <td><?= htmlspecialchars($row['metode_pembayaran'] ?? '-') ?></td>
        <td>
            <?php if (!empty($row['bukti_pembayaran'])): ?>
                <a href="uploads/<?= urlencode($row['bukti_pembayaran']) ?>" target="_blank">Lihat</a>
            <?php else: ?>
                <em>-</em>
            <?php endif; ?>
        </td>
        <td>
            <a href="?verifikasi_id=<?= $row['id'] ?>&set=verifikasi" onclick="return confirm('Setujui pembayaran ini?')">✅ Verifikasi</a> |
            <a href="?verifikasi_id=<?= $row['id'] ?>&set=tolak" onclick="return confirm('Tolak pembayaran ini?')">❌ Tolak</a>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>


    </table>
    <?php else: ?>
        <p>Tidak ada pembayaran yang perlu diverifikasi saat ini.</p>
    <?php endif; ?>
</section>

<footer>
    <p>© 2025 Putri Transway</p>
</footer>
</body>
</html>
