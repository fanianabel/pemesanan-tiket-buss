<?php
session_start();
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
session_unset();
session_destroy();
header("Location: index.php?logout=1&nama=" . urlencode($nama));
exit;
?>