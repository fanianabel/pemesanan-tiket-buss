<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID pemesanan tidak ditemukan.";
    exit;
}

$pemesanan_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$query = "
    SELECT p.id AS pemesanan_id, p.jumlah_tiket, p.total_harga, p.status,
           j.lokasi_keberangkatan, j.tujuan, j.tanggal_keberangkatan, j.jam_berangkat, j.jam_tiba,
           u.nama AS nama_user
    FROM pemesanan p
    JOIN jadwal_bus j ON p.jadwal_id = j.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ? AND p.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $pemesanan_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Tiket tidak ditemukan atau Anda tidak memiliki akses.";
    exit;
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tiket</title>
    <link rel="stylesheet" href="css/stylesTiket.css">
</head>
<body>

<div class="tiket">
    <h2>Tiket Bus Putri Transway</h2>
    <table>
        <tr>
            <th>Nama Penumpang</th>
            <td><?= htmlspecialchars($data['nama_user']) ?></td>
        </tr>
        <tr>
            <th>Rute</th>
            <td><?= htmlspecialchars($data['lokasi_keberangkatan']) ?> → <?= htmlspecialchars($data['tujuan']) ?></td>
        </tr>
        <tr>
            <th>Tanggal Keberangkatan</th>
            <td><?= htmlspecialchars($data['tanggal_keberangkatan']) ?></td>
        </tr>
        <tr>
            <th>Jam</th>
            <td><?= htmlspecialchars($data['jam_berangkat']) ?> - <?= htmlspecialchars($data['jam_tiba']) ?></td>
        </tr>
        <tr>
            <th>Jumlah Tiket</th>
            <td><?= $data['jumlah_tiket'] ?></td>
        </tr>
        <tr>
            <th>Total Harga</th>
            <td>Rp<?= number_format($data['total_harga'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= ucwords($data['status']) ?></td>
        </tr>
    </table>

    <div class="print-btn">
        <button onclick="window.print()">🖨️ Cetak Tiket</button>
    </div>
</div>

</body>
</html>
