<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}

include('db.php');

$tiket = mysqli_query($conn, "
    SELECT t.*, ka.nama_kota AS asal, kt.nama_kota AS tujuan
    FROM tiket t
    JOIN kota ka ON t.kota_asal_id = ka.id
    JOIN kota kt ON t.kota_tujuan_id = kt.id
    ORDER BY t.tanggal DESC, t.waktu_berangkat ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tiket - Putri Transway</title>
    <link rel="stylesheet" href="css/styleDashboardAdmin.css">
</head>
<body>
<header>
    <h1>Laporan Tiket - Putri Transway</h1>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="laporan.php">Laporan</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>Data Tiket Tersedia</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Keberangkatan</th>
                    <th>Tujuan</th>
                    <th>Tanggal</th>
                    <th>Waktu Berangkat</th>
                    <th>Waktu Tiba</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($tiket)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['asal']); ?></td>
                        <td><?= htmlspecialchars($row['tujuan']); ?></td>
                        <td><?= htmlspecialchars($row['tanggal']); ?></td>
                        <td><?= htmlspecialchars($row['waktu_berangkat']); ?></td>
                        <td><?= htmlspecialchars($row['waktu_tiba']); ?></td>
                        <td><?= $row['stok']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<footer>
    <p>© 2025 Putri Transway</p>
</footer>
</body>
</html>