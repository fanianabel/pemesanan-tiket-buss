<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$namaUser = null;

// Ambil nama user
$query = "SELECT nama FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $nama);
    if (mysqli_stmt_fetch($stmt)) {
        $namaUser = $nama;
    }
    mysqli_stmt_close($stmt);
}

// Validasi input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['tiket_id'], $_POST['jumlah'], $_POST['total'])) {
    header('Location: pesan.php');
    exit;
}

$tiket_id = intval($_POST['tiket_id']);
$jumlah = intval($_POST['jumlah']);
$total = intval($_POST['total']);

// Ambil detail tiket
$stmt = $conn->prepare("SELECT t.*, ka.nama_kota AS asal, kt.nama_kota AS tujuan
    FROM tiket t
    JOIN kota ka ON t.kota_asal_id = ka.id
    JOIN kota kt ON t.kota_tujuan_id = kt.id
    WHERE t.id = ?");
$stmt->bind_param("i", $tiket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Tiket tidak ditemukan.";
    exit;
}

$tiket = $result->fetch_assoc();

// Validasi stok cukup
if ($jumlah > $tiket['stok']) {
    die("Jumlah tiket melebihi stok tersedia.");
}

// Flag untuk popup
$suksesBayar = false;

if (isset($_POST['bayar'])) {
  $metode_pembayaran = $_POST['metode_pembayaran'];
  $bukti = null;

  // ✅ Tentukan status SEBELUM bind_param
  if ($metode_pembayaran === 'cod') {
      $status = 'belum_dibayar';
  } elseif ($metode_pembayaran === 'transfer' || $metode_pembayaran === 'ewallet') {
      $status = 'menunggu_verifikasi';
  } else {
      $status = 'pending';
  }

  // Upload file jika ada
  if (!empty($_FILES['bukti_pembayaran']['name'])) {
      if (!is_dir('uploads')) {
          mkdir('uploads', 0755, true);
      }
      $bukti = basename($_FILES['bukti_pembayaran']['name']);
      $bukti_tmp = $_FILES['bukti_pembayaran']['tmp_name'];
      $folder = "uploads/" . $bukti;

      if (!move_uploaded_file($bukti_tmp, $folder)) {
          die("Upload bukti pembayaran gagal.");
      }
  }

  // ✅ Baru bind_param setelah status ditentukan
  $stmtInsert = $conn->prepare("INSERT INTO pemesanan 
      (user_id, jadwal_id, jumlah_tiket, total_harga, metode_pembayaran, bukti_pembayaran, status)
      VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmtInsert->bind_param("iiissss", $user_id, $jadwal_id, $jumlah, $total, $metode_pembayaran, $bukti, $status);
  $stmtInsert->execute();

    $suksesBayar = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pembayaran</title>
  <link rel="stylesheet" href="css/stylesPembayaran.css" />
</head>
<body>

<header>
  <h1>Putri Transway</h1>
  <nav>
    <a href="index.php">Beranda</a>
    <?php if ($namaUser): ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.html">Login</a>
      <a href="register.php">Daftar</a>
    <?php endif; ?>
  </nav>
</header>

<?php if (!$suksesBayar): ?>
<section class="form-section">
  <h2>Konfirmasi Pembayaran</h2>

  <div class="departure-info">
    <p><strong>Rute:</strong> <?= htmlspecialchars($tiket['asal']) ?> → <?= htmlspecialchars($tiket['tujuan']) ?></p>
    <p><strong>Tanggal:</strong> <?= htmlspecialchars($tiket['tanggal']) ?></p>
    <p><strong>Jam Berangkat:</strong> <?= htmlspecialchars($tiket['waktu_berangkat']) ?></p>
    <p><strong>Jumlah Tiket:</strong> <?= $jumlah ?></p>
    <p><strong>Total Harga:</strong> Rp<?= number_format($total, 0, ',', '.') ?></p>
  </div>

  <form method="POST" action="pembayaran.php" enctype="multipart/form-data">
    <input type="hidden" name="tiket_id" value="<?= $tiket_id ?>">
    <input type="hidden" name="jumlah" value="<?= $jumlah ?>">
    <input type="hidden" name="total" value="<?= $total ?>">

    <label for="metode_pembayaran">Metode Pembayaran:</label>
    <select name="metode_pembayaran" id="metode_pembayaran" required>
      <option value="">-- Pilih Metode Pembayaran --</option>
      <option value="transfer">Transfer Bank</option>
      <option value="cod">Bayar di Tempat (COD)</option>
      <option value="ewallet">E-Wallet</option>
    </select>

    <label for="bukti_pembayaran">Upload Bukti Pembayaran:</label>
    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/*">

    <h4>Scan QRIS:</h4>
    <img src="img/qris.jpg" alt="QRIS Pembayaran" style="max-width: 300px;">

    <button type="submit" name="bayar" class="btn-pesan">Bayar Sekarang</button>
  </form>
</section>
<?php endif; ?>

<!-- POPUP LOADING -->
<div id="verifikasiPopup" style="
  display: <?= $suksesBayar ? 'flex' : 'none' ?>;
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(0,0,0,0.5);
  z-index: 1000;
  justify-content: center;
  align-items: center;
">
  <div style="
      background: white;
      padding: 30px;
      border-radius: 8px;
      text-align: center;
      max-width: 300px;
      margin: auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.25);
  ">
    <h3>⏳ Tunggu sebentar...</h3>
    <p>Pembayaran Anda sedang diproses</p>
  </div>
</div>

<?php if ($suksesBayar): ?>
<script>
  setTimeout(() => {
    window.location.href = 'cetak_tiket.php';
  }, 3000);
</script>
<?php endif; ?>

<footer>
  <p>© 2025 Putri Transway</p>
</footer>

<script>
  const form = document.querySelector('form');
  const popup = document.getElementById('verifikasiPopup');

  if (form) {
    form.addEventListener('submit', function () {
      popup.style.display = 'flex';
    });
  }
</script>

</body>
</html>