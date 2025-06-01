<?php
// api/db_config.php
// Aktifkan pelaporan error untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');    // Ganti dengan username DB Anda
define('DB_PASSWORD', '');        // Ganti dengan password DB Anda
define('DB_NAME', 'portal_db_sederhana'); // Ganti dengan nama database Anda

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    // Di produksi, log error ini dan tampilkan pesan generik
    // die("Koneksi gagal: " . $conn->connect_error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Kesalahan koneksi database. Harap coba lagi nanti.']);
    exit; // Hentikan eksekusi jika koneksi gagal
}
// Set charset ke utf8mb4 untuk dukungan karakter yang lebih baik
$conn->set_charset("utf8mb4");
?>