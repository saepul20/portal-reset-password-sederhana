<?php
// api/login_handler.php
session_start(); // Penting untuk manajemen sesi jika menggunakan sesi PHP
header('Content-Type: application/json');
require_once 'db_config.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$response = ['success' => false, 'message' => 'Input tidak valid.'];
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['identifier'], $input['password'])) {
    $identifier = trim($input['identifier']);
    $password = $input['password'];

    if (empty($identifier) || empty($password)) {
        $response['message'] = 'Username/Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            $response['message'] = "Prepare statement gagal: " . $conn->error;
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                $response['success'] = true;
                $response['message'] = 'Login berhasil! Selamat datang, ' . htmlspecialchars($user['username']) . '.';
                
                // Set sesi untuk pengguna yang login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                // Jika menggunakan JWT, generate dan kirim token di sini
                // $response['token'] = generate_jwt_token($user['id']);
            } else {
                $response['message'] = 'Kombinasi username/email dan password salah.';
            }
        } else {
            $response['message'] = 'Kombinasi username/email dan password salah.';
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>