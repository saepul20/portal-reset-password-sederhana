<?php
// api/request_reset_handler.php

// Aktifkan pelaporan error PHP secara eksplisit untuk development
// Di lingkungan produksi, error harus dicatat ke file, bukan ditampilkan ke pengguna.
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set ke 0 di produksi, 1 untuk development jika perlu lihat error di output langsung
ini_set('log_errors', 1); // Pastikan error dicatat
// ini_set('error_log', '/path/to/your/php-error.log'); // Tentukan path log error Anda

header('Content-Type: application/json');
require_once 'db_config.php'; // Pastikan path ini benar

$response = ['success' => false, 'message' => 'Input tidak valid atau terjadi kesalahan internal.'];

// Ambil data JSON dari body request
$input = json_decode(file_get_contents('php://input'), true);

// Periksa apakah koneksi database berhasil (dari db_config.php)
// $conn sudah tersedia dari db_config.php jika tidak ada error koneksi yang menghentikan skrip.
// Jika $conn->connect_error ada di db_config.php, skrip sudah berhenti di sana.

if (isset($input['email'])) {
    $email = trim($input['email']);

    if (empty($email)) {
        $response['message'] = 'Alamat email wajib diisi.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Format email tidak valid.';
        echo json_encode($response);
        exit;
    }

    // Gunakan try-catch untuk menangani potensi error database dengan lebih baik
    try {
        $stmt_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt_user) {
            // Log error prepare statement
            error_log("Prepare statement user check gagal: " . $conn->error);
            $response['message'] = "Terjadi kesalahan pada server (prepare user).";
            echo json_encode($response);
            exit;
        }
        $stmt_user->bind_param("s", $email);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();

        if ($user = $result_user->fetch_assoc()) {
            $user_id = $user['id'];
            $token = bin2hex(random_bytes(32)); // Token acak yang lebih aman
            
            // Set waktu kedaluwarsa token (misalnya, 1 jam dari sekarang)
            $expires_at_dt = new DateTime('+1 hour');
            $expires_at_str = $expires_at_dt->format('Y-m-d H:i:s'); // Format untuk MySQL DATETIME

            $stmt_token = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            if (!$stmt_token) {
                error_log("Prepare statement token insert gagal: " . $conn->error);
                $response['message'] = "Terjadi kesalahan pada server (prepare token).";
                echo json_encode($response);
                exit;
            }
            $stmt_token->bind_param("iss", $user_id, $token, $expires_at_str);

            if ($stmt_token->execute()) {
                // PENTING: Implementasi Pengiriman Email Aktual
                // Baris di bawah ini adalah placeholder dan TIDAK akan mengirim email nyata
                // Anda perlu menggunakan library seperti PHPMailer dan layanan SMTP.
                $reset_link_base = "http://localhost/portal_projek/set_new_password.html"; // Sesuaikan dengan URL Anda
                $reset_link_full = $reset_link_base . "?token=" . $token;

                // Simulasi pengiriman email untuk development (bisa dihapus di produksi)
                // error_log("Link reset untuk " . $email . ": " . $reset_link_full);

                // TODO: Kirim email menggunakan PHPMailer atau layanan serupa
                // $mail_sent_successfully = sendPasswordResetEmail($email, $reset_link_full); // Buat fungsi ini
                $mail_sent_successfully = true; // Asumsikan email terkirim untuk alur ini

                if ($mail_sent_successfully) {
                    $response['success'] = true;
                    $response['message'] = 'Jika email Anda terdaftar, instruksi untuk mereset password telah dikirim.';
                } else {
                    // Sebaiknya jangan beritahu pengguna jika email gagal terkirim karena masalah teknis,
                    // cukup log errornya. Tetap tampilkan pesan sukses generik.
                    error_log("Gagal mengirim email reset password ke: " . $email);
                    $response['success'] = true; // Tetap kirim sukses ke frontend
                    $response['message'] = 'Jika email Anda terdaftar, instruksi untuk mereset password telah dikirim (periksa folder spam).';
                }
            } else {
                error_log('Gagal menyimpan token reset: ' . $stmt_token->error);
                $response['message'] = 'Gagal memproses permintaan reset (db token).';
            }
            $stmt_token->close();
        } else {
            // Email tidak ditemukan. Kirim pesan generik yang sama untuk keamanan (tidak membocorkan email mana yang terdaftar).
            $response['success'] = true; 
            $response['message'] = 'Jika email Anda terdaftar, instruksi untuk mereset password telah dikirim.';
        }
        $stmt_user->close();

    } catch (Exception $e) {
        error_log("Exception dalam request_reset_handler: " . $e->getMessage());
        // Pesan error generik untuk pengguna
        $response['message'] = "Terjadi kesalahan tidak terduga pada server.";
    }

} else {
    $response['message'] = 'Alamat email tidak disertakan dalam permintaan.';
}

$conn->close();
echo json_encode($response);
exit; // Pastikan tidak ada output lain setelah ini
?>