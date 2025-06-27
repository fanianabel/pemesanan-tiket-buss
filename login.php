<?php
include('db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Cek ke database berdasarkan email
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role']; // role diambil dari DB

            // Arahkan sesuai peran
            if ($row['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: /BUSS/index.php');
            }
            exit;
        } else {
            echo 'Password salah.';
        }
    } else {
        echo 'Email tidak terdaftar.';
    }

    mysqli_close($conn);
}
?>




// login.php - Proses autentikasi pengguna

<!-- include('db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Cek login sebagai admin
    if ($email === 'admin' && $password === 'admin') {
        $_SESSION['role'] = 'admin';
        $_SESSION['nama'] = 'Administrator';
        $_SESSION['email'] = 'admin';

        // Redirect ke halaman admin
        header('Location: admin_dashboard.php');
        exit;
    }

    // Deteksi role admin
if ($password === 'admin') {
    $_SESSION['role'] = 'admin';
} else {
    $_SESSION['role'] = 'user';
}


    // Cek login sebagai penumpang (user biasa)
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = 'penumpang';

            // Redirect ke halaman dashboard penumpang
            header('Location: /BUSS/index.php');
            exit;
        } else {
            echo 'Password salah.';
        }
    } else {
        echo 'Email tidak terdaftar.';
    }

    mysqli_close($conn);
}
?> -->