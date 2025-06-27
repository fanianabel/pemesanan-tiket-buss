<?php
include 'db.php'; // koneksi sudah kamu tangani di file ini

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nama = $_POST["nama"];
  $email = $_POST["email"];
  $telepon = $_POST["telepon"];
  $password = $_POST["password"];
  $konfirmasi = $_POST["konfirmasi"];

  // Jika konfirmasi salah
  if ($password !== $konfirmasi) {
    header("Location: register.html?error=konfirmasi_salah&nama=" . urlencode($nama) . "&email=" . urlencode($email) . "&telepon=" . urlencode($telepon));
    exit();
  }

  // Cek apakah email sudah ada
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    header("Location: register.html?error=email_terdaftar&nama=" . urlencode($nama) . "&email=" . urlencode($email) . "&telepon=" . urlencode($telepon));
    exit();
  }

  // Simpan ke database
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO users (nama, email, telepon, password) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $nama, $email, $telepon, $hashed_password);

  if ($stmt->execute()) {
    echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='login.html';</script>";
    exit();
  } else {
    header("Location: register.html?error=gagal_simpan&nama=" . urlencode($nama) . "&email=" . urlencode($email) . "&telepon=" . urlencode($telepon));
    exit();
  }
}
?>