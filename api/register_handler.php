<?php
// api/register_handler.php
header('Content-Type: application/json');
require_once 'db_config.php'; // Koneksi database

// Aktifkan pelaporan error untuk development (hapus/komen di produksi)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$response = ['success' => false, 'message' => 'Input tidak valid.'];

// Ambil data JSON dari body request
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['username'], $input['email'], $input['password'])) {
    $username = trim($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $response['message'] = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $response['message'] = 'Password minimal harus 8 karakter.';
    } else {
        // Cek apakah username atau email sudah ada
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$stmt_check) {
            $response['message'] = "Prepare statement check gagal: " . $conn->error;
            echo json_encode($response);
            exit;
        }
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response['message'] = 'Username atau email sudah terdaftar.';
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            if (!$password_hash) {
                 $response['message'] = 'Gagal melakukan hashing password.';
                 echo json_encode($response);
                 exit;
            }

            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            if (!$stmt_insert) {
                $response['message'] = "Prepare statement insert gagal: " . $conn->error;
                echo json_encode($response);
                exit;
            }
            $stmt_insert->bind_param("sss", $username, $email, $password_hash);

            if ($stmt_insert->execute()) {
                $response['success'] = true;
                $response['message'] = 'Registrasi berhasil! Silakan login.';
                // Di sini Anda bisa menambahkan logika pengiriman email verifikasi jika diperlukan
            } else {
                $response['message'] = 'Registrasi gagal. Error: ' . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

$conn->close();
echo json_encode($response);
?>