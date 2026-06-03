<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/utilities.php';

require_once __DIR__ . '/../../db/database.php';
require_once __DIR__ . '/../utilities.php';

function respond($status, $message, $data = []) {
    http_response_code($status);
    echo json_encode(array_merge([
        "status" => $status,
        "message" => $message
    ], $data));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = trim($data["username"] ?? ' ');
    $password = trim($data["password"] ?? ' ');

    if ($username === '' || $password === '') {
        respond(400, 'Username and password are required.');
    }

    $stmt = $db->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $token = bin2hex(random_bytes(64));

        $stmt = $db->prepare("INSERT INTO user_tokens (user_id, token) VALUES (:userId, :token)");
        $stmt->execute([
            ':userId' => $user['id'],
            ':token' => $token
        ]);

        try {
            decayUserWeights($user['id']);
        } catch (Throwable $e) {
            error_log('[decay] user=' . $user['id'] . 'err=' . $e->getMessage());
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(["status" => 200,
            "message" => "Logged In Successfully",
            "token" => $token,
            "userId" => $user['id'],
            "createdAt" => date('Y-m-d H:i:s'),
            "availableHours" => $AVAILABLE_HOURS
        ]);
    } else {
        respond(500, "Failed to log in");
    }
}