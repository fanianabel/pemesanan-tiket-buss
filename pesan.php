<?php
session_start();
include 'koneksi.php';


// Ambil data kota
$kotaList = [];
$resultKota = $conn->query("SELECT nama_kota FROM kota");
while ($row = $resultKota->fetch_assoc()) {
    $kotaList[] = $row['nama_kota'];
}

$namaUser = null;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT nama FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $nama);
        if (mysqli_stmt_fetch($stmt)) {
            $namaUser = $nama;
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Query gagal: " . mysqli_error($conn);
    }
}

// Jika form pencarian dikirim
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $asal = $_POST['lokasi_asal'];
//     $tujuan = $_POST['lokasi_tujuan'];
//     $tanggal = $_POST['tanggal'];
//     $jumlahTiket = intval($_POST['jumlah_tiket']);
//     $_SESSION['jumlah_tiket'] = $jumlahTiket;

//     $stmt = $conn->prepare("SELECT * FROM jadwal_bus WHERE lokasi_keberangkatan = ? AND tujuan = ? AND tanggal_keberangkatan = ?");
//     $stmt->bind_param("sss", $asal, $tujuan, $tanggal);
//     $stmt->execute();
//     $resultJadwal = $stmt->get_result();
// }


// $hasil_pencarian = [];

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $tanggal = $_POST['tanggal'];
//     $jumlah = (int)$_POST['jumlah'];
//     $lokasi = $_POST['lokasi'];
//     $tujuan = $_POST['tujuan'];

//     $_SESSION['jumlah_tiket'] = $jumlah;

//     $stmt = $conn->prepare("SELECT t.*, ka.nama_kota AS asal, kt.nama_kota AS tujuan
//         FROM tiket t
//         JOIN kota ka ON t.kota_asal_id = ka.id
//         JOIN kota kt ON t.kota_tujuan_id = kt.id
//         WHERE t.tanggal = ? AND ka.nama_kota = ? AND kt.nama_kota = ? AND t.stok >= ?
//         ORDER BY t.waktu_berangkat
//     ");
//     $stmt->bind_param("sssi", $tanggal, $lokasi, $tujuan, $jumlah);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     while ($row = $result->fetch_assoc()) {
//         $hasil_pencarian[] = $row;
//     }
//     $stmt->close();
// }


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $tanggal = $_POST['tanggal'];
//     $jumlah = (int)$_POST['jumlah'];
//     $lokasi = $_POST['lokasi'];
//     $tujuan = $_POST['tujuan'];

//     // Validasi tanggal
//     if (strtotime($tanggal) < strtotime(date('Y-m-d'))) {
//         echo "<p style='text-align:center; color:red;'>Tanggal keberangkatan tidak boleh di masa lalu.</p>";
//         exit;
//     }

//     $_SESSION['jumlah_tiket'] = $jumlah;

// $stmt = $conn->prepare("SELECT t.*, ka.nama_kota AS asal, kt.nama_kota AS tujuan
//     FROM tiket t
//     JOIN kota ka ON t.kota_asal_id = ka.id
//     JOIN kota kt ON t.kota_tujuan_id = kt.id
//     WHERE t.tanggal = ? AND t.tanggal >= CURDATE()
//       AND ka.nama_kota = ? AND kt.nama_kota = ? AND t.stok >= ?
//     ORDER BY t.waktu_berangkat
// ");

//     $stmt->bind_param("sssi", $tanggal, $lokasi, $tujuan, $jumlah);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     while ($row = $result->fetch_assoc()) {
//         $hasil_pencarian[] = $row;
//     }
//     $stmt->close();
// }


$hasil_pencarian = [];
$pencarian_kadaluwarsa = false; // flag default

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jumlah = (int)$_POST['jumlah'];
    $lokasi = $_POST['lokasi'];
    $tujuan = $_POST['tujuan'];

    $_SESSION['jumlah_tiket'] = $jumlah;

    // Cek apakah tanggal yang dimasukkan sudah lewat
    if ($tanggal < date('Y-m-d')) {
        $pencarian_kadaluwarsa = true;
    } else {
        $stmt = $conn->prepare("SELECT t.*, ka.nama_kota AS asal, kt.nama_kota AS tujuan
            FROM tiket t
            JOIN kota ka ON t.kota_asal_id = ka.id
            JOIN kota kt ON t.kota_tujuan_id = kt.id
            WHERE t.tanggal = ? 
              AND t.tanggal >= CURDATE()
              AND ka.nama_kota = ? 
              AND kt.nama_kota = ? 
              AND t.stok >= ?
            ORDER BY t.waktu_berangkat
        ");
        $stmt->bind_param("sssi", $tanggal, $lokasi, $tujuan, $jumlah);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $hasil_pencarian[] = $row;
        }
        $stmt->close();
    }
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pesan Bus</title>
  <link rel="stylesheet" href="css/stylespesan.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
header {
      background-color: #8c1f1f;
      color: white;
      padding: 20px;
      border-radius: 0 0 10px 10px;
      margin-bottom: 20px;
    }

    header h1 {
      margin: 0;
    }

    nav {
      margin-top: 10px;
    }

    nav a {
      color: white;
      margin-right: 15px;
      text-decoration: none;
      font-weight: 600;
    }

    nav a:hover {
      text-decoration: underline;
    }

    p.welcome-msg {
      color: white;
      margin-top: 5px;
      font-weight: 600;
    }
  </style>
</head>
<body>
<header>
  <h1>Putri Transway</h1>
  <nav>
    <a href="index.php">Beranda</a>
    <?php if ($namaUser): ?>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
      <a href="login.html">Login</a>
      <a href="register.php">Daftar</a>
    <?php endif; ?>
  </nav>
<body>

<div class="booking-header">
  <div class="background-map"></div>
  <div class="header-content">
    <div class="header-left">
      <h1>Pesan tiket bus sekarang 🚌</h1>
    </div>
  </div>
</div>

  <?php if ($namaUser): ?>
    <p class="welcome-msg">Pesan tiket BUS mu, <strong><?= htmlspecialchars($namaUser); ?></strong>!</p>
  <?php endif; ?>
</header>




<section id="search-section">
    <h3>Cari Bus Anda</h3>
    <form method="POST" class="search-form">
        <div class="search-group">
            <label for="tanggal">Tanggal Keberangkatan</label>
            <input type="date" id="tanggal" name="tanggal" required />
        </div>
        <div class="search-group">
            <label for="jumlah">Jumlah Penumpang</label>
            <input type="number" id="jumlah" name="jumlah" min="1" required />
        </div>
        <div class="search-group">
            <label for="lokasi">Lokasi Keberangkatan</label>
            <select id="lokasi" name="lokasi" required>
                <option value="">Pilih Lokasi</option>
                <option value="Jakarta">Jakarta</option>
                <option value="Bandung">Bandung</option>
                <option value="Surabaya">Surabaya</option>
                <option value="Yogyakarta">Yogyakarta</option>
                <option value="Semarang">Semarang</option>
            </select>
        </div>
        <div class="search-group">
            <label for="tujuan">Tujuan</label>
            <select id="tujuan" name="tujuan" required>
                <option value="">Pilih Tujuan</option>
                <option value="Jakarta">Jakarta</option>
                <option value="Bandung">Bandung</option>
                <option value="Surabaya">Surabaya</option>
                <option value="Yogyakarta">Yogyakarta</option>
                <option value="Semarang">Semarang</option>
            </select>
        </div>
        <div class="search-submit">
            <button type="submit">Cari Bus</button>
        </div>
    </form>
</section>

<?php if (!empty($hasil_pencarian)): ?>
<section class="table-container">
    <h3>Hasil Pencarian Bus</h3>
    <table>
        <thead>
            <tr>
                <th>Asal</th>
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
            <?php foreach ($hasil_pencarian as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['asal']) ?></td>
                    <td><?= htmlspecialchars($row['tujuan']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['waktu_berangkat']) ?></td>
                    <td><?= htmlspecialchars($row['waktu_tiba']) ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><?= $row['stok'] ?></td>
                    <td>
                        <form method="POST" action="pembayaran.php">
                            <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="jumlah" value="<?= $jumlah ?>">
                            <input type="hidden" name="total" value="<?= $jumlah * $row['harga'] ?>">
                            <button type="submit">Pesan</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<p style="text-align: center; color: red;">Tidak ada bus yang tersedia sesuai pencarian Anda.</p>
<?php endif; ?>


</div>
 <footer>
        <p>© 2025 Putri Transway</p>
    </footer>

</body>
</html>
