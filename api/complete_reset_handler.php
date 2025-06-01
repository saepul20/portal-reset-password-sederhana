<?php
// api/complete_reset_handler.php
header('Content-Type: application/json');
require_once 'db_config.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$response = ['success' => false, 'message' => 'Input tidak valid.'];
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['token'], $input['newPassword'])) {
    $token = $input['token'];
    $newPassword = $input['newPassword'];

    if (empty($token) || empty($newPassword)) {
        $response['message'] = 'Token dan password baru wajib diisi.';
    } elseif (strlen($newPassword) < 8) {
        $response['message'] = 'Password baru minimal harus 8 karakter.';
    } else {
        // Cek token di database
        $stmt_check_token = $conn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
        if (!$stmt_check_token) {
             $response['message'] = "Prepare statement token check gagal: " . $conn->error;
             echo json_encode($response);
             exit;
        }
        $stmt_check_token->bind_param("s", $token);
        $stmt_check_token->execute();
        $result_token = $stmt_check_token->get_result();

        if ($reset_data = $result_token->fetch_assoc()) {
            $user_id = $reset_data['user_id'];
            $expires_at_str = $reset_data['expires_at'];
            $expires_at_dt = new DateTime($expires_at_str);
            $now_dt = new DateTime();


            if ($now_dt > $expires_at_dt) {
                $response['message'] = 'Token reset sudah kedaluwarsa.';
            } else {
                $new_password_hash = password_hash($newPassword, PASSWORD_BCRYPT);
                 if (!$new_password_hash) {
                     $response['message'] = 'Gagal melakukan hashing password baru.';
                     echo json_encode($response);
                     exit;
                }

                $stmt_update_pass = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                if (!$stmt_update_pass) {
                     $response['message'] = "Prepare statement update password gagal: " . $conn->error;
                     echo json_encode($response);
                     exit;
                }
                $stmt_update_pass->bind_param("si", $new_password_hash, $user_id);

                if ($stmt_update_pass->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Password berhasil direset. Silakan login dengan password baru Anda.';

                    // Hapus token yang sudah digunakan
                    $stmt_delete_token = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                    $stmt_delete_token->bind_param("s", $token);
                    $stmt_delete_token->execute();
                    $stmt_delete_token->close();
                } else {
                    $response['message'] = 'Gagal mengupdate password. Error: ' . $stmt_update_pass->error;
                }
                $stmt_update_pass->close();
            }
        } else {
            $response['message'] = 'Token reset tidak valid atau tidak ditemukan.';
        }
        $stmt_check_token->close();
    }
}

$conn->close();
echo json_encode($response);
?>