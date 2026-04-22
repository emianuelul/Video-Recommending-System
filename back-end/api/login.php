<?php
require '../../db/database.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = trim($data["username"] ?? ' ');
    $password = trim($data["password"] ?? ' ');

    if ($username === '' || $password === '') {
        die('Username and password are required.');
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
        header('Content-Type: application/json');
        echo json_encode(["status" => 200, "message" => "Logged In Successfully", "token" => $token, "userId" => $user['id']]);
    } else {

        http_response_code(500);
        echo json_encode(["status" => 500, "message" => "Failed to log in"]);
    }
}