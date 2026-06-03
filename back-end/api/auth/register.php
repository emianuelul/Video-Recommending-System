<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/utilities.php';

$allowed_origins = ["http://127.0.0.1:8001", "http://localhost:8001"];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = trim($data["username"] ?? ' ');
    $password = trim($data["password"] ?? ' ');
    $categories = $data["categories"] ?? [];
    $languages = $data["languages"] ?? ' ';
    $durations = $data["durations"] ?? [];
    $country = $data["country"] ?? ' ';

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $userId = bin2hex(random_bytes(16));

    try {
        $stmt = $db->prepare("INSERT INTO users (id, username, password_hash) VALUES (:userId, :username, :password)");
        $stmt->execute([
            ':userId' => $userId,
            ':username' => $username,
            ':password' => $hashedPassword
        ]);

        foreach ($categories as $category) {
            $stmt = $db->prepare(
                "INSERT INTO user_categories (user_id, category_id, weight, last_interacted_at)
                    VALUES (:userId, :categoryId, :weight, :lastInteractedAt)");
            $stmt->execute([
                ':userId' => $userId,
                ':categoryId' => $category,
                ':weight' => 5,
                ':lastInteractedAt' => date('Y-m-d H:i:s')
            ]);
        }

        $stmt = $db->prepare(
            "INSERT INTO user_preferences (user_id, languages, duration, country)
        VALUES (:userId, :languages, :duration, :country)");
        $stmt->execute([
            ':userId' => $userId,
            ':languages' => json_encode($data["languages"] ?? []),
            ':duration' => json_encode($data["durations"] ?? []),
            ':country' => $country
        ]);

        $response = json_encode(["status" => 200, "message" => "Account created successfully!"]);

        http_response_code(200);

        echo $response;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $response = json_encode(["status" => 409, "message" => "Username is taken."]);

            http_response_code(409);
            header("Content-Type: application/json");
            echo $response;
        } else {
            $response = json_encode(["status" => 500, "message" => $e->getMessage()]);

            http_response_code(500);
            header("Content-Type: application/json");
            echo $response;
        }
    }
}