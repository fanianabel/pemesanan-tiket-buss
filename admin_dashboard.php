<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}

include('db.php');

if (isset($_GET['hapus_id'])) {
    $hapus_id = (int)$_GET['hapus_id'];
    $conn->query("DELETE FROM tiket WHERE id = $hapus_id");
    header("Location: admin_dashboard.php");
    exit;
}

$kota_result = mysqli_query($conn, "SELECT id, nama_kota FROM kota");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $waktu_berangkat = $_POST['waktu_berangkat'];
    $waktu_tiba = $_POST['waktu_tiba'];
    $kota_asal_id = (int)$_POST['kota_asal_id'];
    $kota_tujuan_id = (int)$_POST['kota_tujuan_id'];
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];

    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE tiket SET tanggal=?, waktu_berangkat=?, waktu_tiba=?, kota_asal_id=?, kota_tujuan_id=?, stok=?, harga=? WHERE id=?");
        $stmt->bind_param("sssiiiii", $tanggal, $waktu_berangkat, $waktu_tiba, $kota_asal_id, $kota_tujuan_id, $stok, $harga, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO tiket (tanggal, waktu_berangkat, waktu_tiba, kota_asal_id, kota_tujuan_id, stok, harga) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiii", $tanggal, $waktu_berangkat, $waktu_tiba, $kota_asal_id, $kota_tujuan_id, $stok, $harga);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit;
}

$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $edit_query = $conn->query("SELECT * FROM tiket WHERE id = $edit_id");
    $edit_data = $edit_query->fetch_assoc();
}

$tiket = mysqli_query($conn, "
    SELECT t.*, ka.nama_kota AS asal, kt.nama_kota AS tujuan
    FROM tiket t
    JOIN kota ka ON t.kota_asal_id = ka.id
    JOIN kota kt ON t.kota_tujuan_id = kt.id
    ORDER BY ka.nama_kota, kt.nama_kota, t.tanggal, t.waktu_berangkat
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Putri Transway</title>
    <link rel="stylesheet" href="css/styleDashboardAdmin.css" />
</head>
<body>
<header>
    <h1>Admin - Manajemen Armada Putri Transway</h1>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="verifikasi_pembayaran.php" class="btn">Verifikasi Pembayaran</a>
        <a href="laporan.php">Laporan</a>
        <a href="logout.php">Logout</a>

    </nav>
</header>

<section>
    <h2><?php echo $edit_data ? "Edit Tiket" : "Tambah Tiket Baru"; ?></h2>
    <form method="POST" action="admin_dashboard.php">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>

        <label for="kota_asal_id">Kota Keberangkatan:</label>
        <select name="kota_asal_id" id="kota_asal_id" required>
            <option value="">-- Pilih Kota Asal --</option>
            <?php
            mysqli_data_seek($kota_result, 0);
            while ($kota = mysqli_fetch_assoc($kota_result)) {
                $selected = ($edit_data && $edit_data['kota_asal_id'] == $kota['id']) ? 'selected' : '';
                echo "<option value='{$kota['id']}' $selected>" . htmlspecialchars($kota['nama_kota']) . "</option>";
            }
            ?>
        </select>

        <label for="kota_tujuan_id">Kota Tujuan:</label>
        <select name="kota_tujuan_id" id="kota_tujuan_id" required>
            <option value="">-- Pilih Kota Tujuan --</option>
            <?php
            mysqli_data_seek($kota_result, 0);
            while ($kota = mysqli_fetch_assoc($kota_result)) {
                $selected = ($edit_data && $edit_data['kota_tujuan_id'] == $kota['id']) ? 'selected' : '';
                echo "<option value='{$kota['id']}' $selected>" . htmlspecialchars($kota['nama_kota']) . "</option>";
            }
            ?>
        </select>

        <label for="tanggal">Tanggal Keberangkatan:</label>
        <input type="date" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($edit_data['tanggal'] ?? ''); ?>" required>

        <!-- <label for="waktu_berangkat">Waktu Keberangkatan:</label>
        <input type="time" id="waktu_berangkat" name="waktu_berangkat" value="<?php echo htmlspecialchars($edit_data['waktu_berangkat'] ?? ''); ?>" required>

        <label for="waktu_tiba">Waktu Tiba:</label>
        <input type="time" id="waktu_tiba" name="waktu_tiba" value="<?php echo htmlspecialchars($edit_data['waktu_tiba'] ?? ''); ?>" required> -->

        <label for="waktu_berangkat">Waktu Keberangkatan:</label>
<input type="time" id="waktu_berangkat" name="waktu_berangkat"
    value="<?php echo isset($edit_data['waktu_berangkat']) ? htmlspecialchars(date('H:i', strtotime($edit_data['waktu_berangkat']))) : ''; ?>"
    required>

<label for="waktu_tiba">Waktu Tiba:</label>
<input type="time" id="waktu_tiba" name="waktu_tiba"
    value="<?php echo isset($edit_data['waktu_tiba']) ? htmlspecialchars(date('H:i', strtotime($edit_data['waktu_tiba']))) : ''; ?>"
    required>


        <label for="harga">Harga Tiket (Rp):</label>
        <input type="number" id="harga" name="harga" min="0" value="<?php echo htmlspecialchars($edit_data['harga'] ?? ''); ?>" required>


        <label for="stok">Jumlah/Stok Tiket:</label>
        <input type="number" id="stok" name="stok" min="1" value="<?php echo htmlspecialchars($edit_data['stok'] ?? ''); ?>" required>

        <button type="submit"><?php echo $edit_data ? "Update Tiket" : "Tambah Tiket"; ?></button>
    </form>

    <h2>Stok Tiket Tersedia</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Keberangkatan</th>
                    <th>Tujuan</th>
                    <th>Tanggal</th>
                    <th>Waktu Berangkat</th>
                    <th>Waktu Tiba</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($tiket)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['asal']); ?></td>
                        <td><?php echo htmlspecialchars($row['tujuan']); ?></td>
                        <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                        <td><?php echo htmlspecialchars($row['waktu_berangkat']); ?></td>
                        <td><?php echo htmlspecialchars($row['waktu_tiba']); ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['stok']; ?></td>
                        <td>
                            <a href="admin_dashboard.php?edit_id=<?php echo $row['id']; ?>" class="btn edit">Edit</a>
                            <a href="admin_dashboard.php?hapus_id=<?php echo $row['id']; ?>" class="btn hapus" onclick="return confirm('Yakin ingin menghapus tiket ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<footer>
    <p>© 2025 Putri Transway</p>
</footer>
</body>
</html>