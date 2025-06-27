<?php
session_start();
include 'koneksi.php';

$hasil_pencarian = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jumlah = (int)$_POST['jumlah'];
    $lokasi = $_POST['lokasi'];
    $tujuan = $_POST['tujuan'];

    $stmt = $conn->prepare("
        SELECT t.*, ka.nama_kota AS asal, kt.nama_kota AS tujuan
        FROM tiket t
        JOIN kota ka ON t.kota_asal_id = ka.id
        JOIN kota kt ON t.kota_tujuan_id = kt.id
        WHERE t.tanggal = ? AND ka.nama_kota = ? AND kt.nama_kota = ? AND t.stok >= ?
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

$logoutMessage = '';
if (isset($_GET['logout']) && isset($_GET['nama'])) {
    $logoutMessage = 'Anda telah keluar dari akun ' . htmlspecialchars($_GET['nama']);
}

$namaUser = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT nama FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($nama);
    if ($stmt->fetch()) {
        $namaUser = $nama;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Beranda - Putri Transway</title>
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        header p.welcome-msg {
            color: white;
            margin-top: 5px;
            font-weight: 600;
        }
        .table-container {
            padding: 20px;
            margin-top: 20px;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        .table-container th, .table-container td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        .table-container th {
            background-color: maroon;
            color: white;
        }
    </style>
</head>
<body>

<header>
    <h1>Putri Transway</h1>
    <nav>
        <a href="index.php">Beranda</a>
        <a href="#about">About Us</a>
        <?php if ($namaUser): ?>
            <a href="cetak_tiket.php">Tiket</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>

        <?php else: ?>
            <a href="login.html">Login</a>
            <a href="register.html">Daftar</a>
        <?php endif; ?>
    </nav>
    <?php if ($namaUser): ?>
        <p class="welcome-msg">Selamat datang, <strong><?= htmlspecialchars($namaUser); ?></strong>!</p>
    <?php endif; ?>
</header>

<?php if (!empty($logoutMessage)): ?>
<div id="logout-popup" style="
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #f44336;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    z-index: 1000;
">
    <?= $logoutMessage ?>
</div>
<script>
    setTimeout(() => {
        const popup = document.getElementById('logout-popup');
        if (popup) popup.style.display = 'none';
    }, 3000);
</script>
<?php endif; ?>

<section id="hero">
    <h2>Selamat Datang di Layanan Penyewaan Bus Pariwisata</h2>
    <p>Solusi perjalanan wisata Anda yang nyaman dan terpercaya.</p>
    <a href="<?= $namaUser ? 'pesan.php' : 'login.html' ?>" class="btn">Pesan Sekarang</a>
</section>

<section id="hero-carousel">
    <div class="carousel-container">
        <img class="carousel-slide" src="https://png.pngtree.com/thumb_back/fh260/background/20230717/pngtree-d-rendering-of-a-white-isolated-background-featuring-a-medium-sized-image_3893569.jpg" alt="Bus 1"/>
    </div>
</section>

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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<p style="text-align: center; color: red;">Tidak ada bus yang tersedia sesuai pencarian Anda.</p>
<?php endif; ?>


<section id="panduan-pemesanan">
    <h3>CARA PEMESANAN TIKET BUS</h3>
    <div class="steps">
      <div class="step">
        <img
          src="https://st5.depositphotos.com/74733082/64649/v/450/depositphotos_646498030-stock-illustration-date-time-address-place-icons.jpg"
          alt="Pilih Rincian Perjalanan"
        />
        <h4>Pilih rincian perjalanan</h4>
        <p>Masukkan tempat keberangkatan, tujuan, tanggal perjalanan dan kemudian klik 'Cari'</p>
      </div>
      <div class="step">
        <img
          src="https://tiberman.com/blog/wp-content/uploads/2024/03/2681139098.jpg"
          alt="Pilih Bis dan Tempat Duduk"
        />
        <h4>Pilih bis dan tempat duduk anda</h4>
        <p>Pilih bis, tempat duduk, isi rincian penumpang lalu klik 'Pembayaran'</p>
      </div>
      <div class="step">
        <img
          src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQa96nHtyLmxbETcXe9KnPThmnHfVrIdJfR1JDBqi2Gda0VIObKpkJt3Od6QnbJjQfGM1g&usqp=CAU"
          alt="Pembayaran Mudah"
        />
        <h4>Cara Pembayaran yang Mudah</h4>
        <p>Pembayaran dapat dilakukan melalui ATM, internet banking, Alfamart, kartu kredit/debit, dll</p>
      </div>
    </div>
  </section>

  <section
    id="keunggulan-layanan"
    style="text-align: center; padding: 40px 20px; background: #f3f3f3"
  >
    <h3>KELEBIHAN LAYANAN KAMI</h3>
    <div
      style="
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 30px;
      "
    >
      <div
        style="
          flex: 1 1 250px;
          max-width: 300px;
          margin: 10px;
          background: white;
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        "
      >
        <img
          src="https://cdn-icons-png.flaticon.com/512/1086/1086741.png"
          alt="Tanpa Biaya Tambahan"
          style="width: 50px; height: 50px"
        />
        <h4>TANPA BIAYA TAMBAHAN</h4>
        <p>Pesan tiket bis anda dengan harga terbaik</p>
      </div>
      <div
        style="
          flex: 1 1 250px;
          max-width: 300px;
          margin: 10px;
          background: white;
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        "
      >
        <img
          src="https://cdn-icons-png.flaticon.com/512/2462/2462719.png"
          alt="Pembayaran Online Aman"
          style="width: 50px; height: 50px"
        />
        <h4>PEMBAYARAN ONLINE YANG AMAN & NYAMAN</h4>
        <p>Bayar tiket online anda dengan cara yang aman dan nyaman</p>
      </div>
      <div
        style="
          flex: 1 1 250px;
          max-width: 300px;
          margin: 10px;
          background: white;
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        "
      >
        <img
          src="https://cdn-icons-png.flaticon.com/512/1087/1087924.png"
          alt="Pilih Tempat Duduk"
          style="width: 50px; height: 50px"
        />
        <h4>PILIH TEMPAT DUDUK YANG ANDA INGINKAN</h4>
        <p>Pesan tempat duduk sesuai pilihan anda</p>
      </div>
    </div>
  </section>

  <footer>
 

  <section id="about" style="padding: 40px 20px; background: #f9f9f9; text-align: center;">
  <h3 style="color: black;">Tentang Kami</h3>
  <p style="color: black;">Kantor pusat Putri Transway berlokasi di Yogyakarta. Kunjungi kami atau lihat lokasi kami di bawah ini:</p>
  <div id="map" style="width: 100%; height: 400px; margin-top: 20px; border: 1px solid #ccc;"></div>
</section>

<script>
  function initMap() {
    const lokasi = { lat: -7.801194, lng: 110.364917 }; // Titik Yogyakarta
    const map = new google.maps.Map(document.getElementById("map"), {
      zoom: 13,
      center: lokasi
    });
    new google.maps.Marker({
      position: lokasi,
      map: map,
      title: "Kantor Pusat Putri Transway"
    });
  }
</script>

<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBLNEo0U75qcSGPKWs7Dm66dk1ylhgoqfM&callback=initMap">
</script>

  <footer>
    <p>&copy; 2025 Putri Transway. Semua Hak Dilindungi.</p>
  </footer>

  <script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-slide');

    function showSlide(index) {
      slides.forEach((slide, i) => {
        slide.classList.remove('active');
        if (i === index) {
          slide.classList.add('active');
        }
      });
    }

    function nextSlide() {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }

    showSlide(currentSlide);
    setInterval(nextSlide, 3000); // Ganti gambar setiap 3 detik
  </script>

</body>
</html>
