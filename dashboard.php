<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// Ambil informasi pengguna
include('db.php');
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Tentukan role (default: user)
$role = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Putri Transway</title>
    <link rel="stylesheet" href="/BUSS/css/styleDashboard.css">
</head>
<body>
    <header>
        <h1>Selamat datang, <?php echo htmlspecialchars($user['nama']); ?>!</h1>
        <nav>
            <a href="logout.php">Logout</a>
            <?php if ($role === 'admin'): ?>
                <a href="admin_dashboard.php">Manajemen Armada</a>
                <a href="laporan.php">Laporan</a>
            <?php else: ?>
                <a href="booking.php">Pesan Bus</a>
                <a href="profile.php">Profil</a>
            <?php endif; ?>
        </nav>
    </header>

    <section>
        <h2>Informasi Profil</h2>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Telepon: <?php echo htmlspecialchars($user['telepon']); ?></p>
        <p>Peran: <?php echo $role === 'admin' ? 'Administrator' : 'Penumpang'; ?></p>
    </section>

    <footer>
        <p>© 2025 Putri Transway</p>
    </footer>
</body>
</html>