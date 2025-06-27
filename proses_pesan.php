<?php
session_start();
include 'koneksi.php';

$lokasi = $_POST['lokasi_asal'] ?? '';
$tujuan = $_POST['lokasi_tujuan'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';

// Simpan hasil ke session (optional)
$_SESSION['hasil_pencarian'] = [
  'lokasi' => $lokasi,
  'tujuan' => $tujuan,
  'tanggal' => $tanggal
];

// Redirect ke pesan.php dengan query string
header("Location: pesan.php?lokasi=" . urlencode($lokasi) . "&tujuan=" . urlencode($tujuan) . "&tanggal=" . urlencode($tanggal));
exit;
?>

//gadipake