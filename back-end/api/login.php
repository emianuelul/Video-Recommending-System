<?php
$allowed_origins = ["http://127.0.0.1:8001", "http://localhost:8001"];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:8001');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

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

        http_response_code(200);
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
