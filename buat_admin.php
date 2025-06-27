//buat_admin.php

<?php
include('db.php');
$password = password_hash('admin', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (nama, email, password, role)
        VALUES ('Admin', 'admin@putritransway.com', '$password', 'admin')";

if (mysqli_query($conn, $sql)) {
    header("Location: admin_dashboard.html");
    
} else {
    echo "Gagal: " . mysqli_error($conn);
}
?>