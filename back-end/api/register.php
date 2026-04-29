<?php
require_once '../../db/database.php';
require_once '../utilities.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

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
                "INSERT INTO user_categories (user_id, category_id, category_title, weight, last_interacted_at)
                    VALUES (:userId, :categoryId, :categoryTitle, :weight, :lastInteractedAt)");
            $stmt->execute([
                ':userId' => $userId,
                ':categoryId' => $category,
                ':categoryTitle' => $categoriesList[$category],
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
        header("Content-Type: application/json");

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