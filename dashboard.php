<?php
// dashboard.php
session_start(); // Mulai atau lanjutkan sesi yang ada

// Cek apakah pengguna sudah login, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit; // Pastikan tidak ada kode lain yang dieksekusi setelah redirect
}

// Ambil username dari sesi untuk ditampilkan
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PortalKu</title>
    <link rel="stylesheet" href="css/style.css"> <style>
        /* Tambahan style khusus dashboard jika perlu */
        .dashboard-content {
            padding: 20px;
            background-color: #fff;
            margin: 20px auto; /* Tengahkan container */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            max-width: 800px; /* Lebar maksimal konten dashboard */
            text-align: left; /* Atau center jika lebih disukai */
        }
        .dashboard-content h1 {
            color: #1c1e21;
            border-bottom: 1px solid #dddfe2;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .dashboard-content p {
            font-size: 1.1em;
            line-height: 1.6;
        }
        .logout-button { /* Styling untuk tombol logout agar mirip tombol lain */
            display: inline-block;
            background-color: #dc3545; /* Warna merah untuk logout */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.2s;
        }
        .logout-button:hover {
            background-color: #c82333; /* Warna merah lebih gelap saat hover */
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="index.html" class="brand">PortalKu</a>
        <div class="nav-right">
            <a href="dashboard.php">Dashboard</a>
            <a href="api/logout_handler.php">Logout</a>
            </div>
    </div>

    <div class="dashboard-content">
        <h1>Selamat Datang di Dashboard, <?php echo $username; ?>!</h1>
        <p>Ini adalah area pribadi Anda. Dari sini Anda dapat mengakses berbagai fitur dan pengaturan akun.</p>
        
        <p>Beberapa hal yang mungkin ingin Anda lakukan:</p>
        <ul>
            <li>Edit profil Anda (fitur belum dibuat)</li>
            <li>Lihat riwayat aktivitas (fitur belum dibuat)</li>
            <li>Ubah pengaturan (fitur belum dibuat)</li>
        </ul>

        <p>Terima kasih telah bergabung dengan PortalKu.</p>

        <a href="api/logout_handler.php" class="logout-button">Logout dari Akun</a>
    </div>

    <script>
        // JavaScript spesifik dashboard jika ada
        console.log("Halaman dashboard dimuat untuk pengguna: <?php echo addslashes($username); ?>");
    </script>

</body>
</html>