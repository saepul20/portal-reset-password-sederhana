<?php
// api/logout_handler.php
session_start(); // Mulai sesi untuk mengakses variabel sesi

// Hapus semua variabel sesi
$_SESSION = array();

// Jika diinginkan, hancurkan juga cookie sesi.
// Catatan: Ini akan menghancurkan sesi, dan bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Akhirnya, hancurkan sesi.
session_destroy();

// Arahkan ke halaman login (atau halaman utama)
header("Location: ../login.html"); // Kembali ke halaman login di direktori utama
exit;
?>