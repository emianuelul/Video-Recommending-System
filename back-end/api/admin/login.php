<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/utilities.php';

global $db, $AVAILABLE_HOURS;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => 405, "message" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data["username"] ?? '');
$password = trim($data["password"] ?? '');

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["status" => 400, "message" => "Username and password are required"]);
    exit;
}

$stmt = $db->prepare("SELECT id, username, password_hash FROM admins WHERE username = :username");
$stmt->execute([':username' => $username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['password_hash'])) {
    $token = bin2hex(random_bytes(64));

    $stmt = $db->prepare("INSERT INTO admin_tokens (admin_id, token) VALUES (:adminId, :token)");
    $stmt->execute([
        ':adminId' => $admin['id'],
        ':token' => $token
    ]);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Admin logged in successfully",
        "adminToken" => $token,
        "adminId" => $admin['id'],
        "createdAt" => date('Y-m-d H:i:s'),
        "availableHours" => $AVAILABLE_HOURS
    ]);
} else {
    http_response_code(401);
    echo json_encode(["status" => 401, "message" => "Invalid admin credentials"]);
}
