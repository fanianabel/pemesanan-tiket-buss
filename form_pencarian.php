<?php
// koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "putri_transway");

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// ambil data kota
$sql = "SELECT nama_kota FROM kota ORDER BY nama_kota ASC";
$result = $koneksi->query($sql);
?>

<section>
  <h3>Cari Bus Anda</h3>
  <form action="php/pemesananHandler.php" method="POST">

    <label for="tanggal">Tanggal Keberangkatan</label>
    <input type="date" id="tanggal" name="tanggal" required>

    <label for="jumlah">Jumlah Penumpang</label>
    <input type="number" id="jumlah" name="jumlah" min="1" required>

    <label for="lokasi">Lokasi Keberangkatan</label>
    <select id="lokasi" name="lokasi" required>
      <option value="">-- Pilih Keberangkatan --</option>
      <?php while($row = $result->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($row['nama_kota']) ?>">
          <?= htmlspecialchars($row['nama_kota']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <?php
    // ulangi query untuk tujuan (atau reset pointer)
    $result = $koneksi->query($sql);
    ?>

    <label for="tujuan">Tujuan</label>
    <select id="tujuan" name="tujuan" required>
      <option value="">-- Pilih Tujuan --</option>
      <?php while($row = $result->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($row['nama_kota']) ?>">
          <?= htmlspecialchars($row['nama_kota']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <button type="submit">Cari Bus</button>
  </form>
</section>

<?php $koneksi->close(); ?>