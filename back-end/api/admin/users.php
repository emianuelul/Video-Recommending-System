<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/admin_auth.php';

global $db;

header('Content-Type: application/json');

adminAuth();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->prepare("
        SELECT id, username, created_at
        FROM users
        ORDER BY created_at DESC
    ");
    $stmt->execute();

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "users" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => 405, "message" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data["action"] ?? '';
$userId = $data["user_id"] ?? '';

if ($userId === '') {
    http_response_code(400);
    echo json_encode(["status" => 400, "message" => "Missing user ID"]);
    exit;
}

if ($action === 'change_password') {
    $newPassword = trim($data["new_password"] ?? '');

    if ($newPassword === '') {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "Missing new password"]);
        exit;
    }

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password_hash = :passwordHash WHERE id = :userId");
    $stmt->execute([
        ':passwordHash' => $passwordHash,
        ':userId' => $userId
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["status" => 404, "message" => "User not found"]);
        exit;
    }

    $deleteTokens = $db->prepare("DELETE FROM user_tokens WHERE user_id = :userId");
    $deleteTokens->execute([':userId' => $userId]);

    http_response_code(200);
    echo json_encode(["status" => 200, "message" => "Password changed"]);
    exit;
}

if ($action === 'delete_user') {
    $db->beginTransaction();

    try {
        $checkUser = $db->prepare("SELECT id FROM users WHERE id = :userId LIMIT 1");
        $checkUser->execute([':userId' => $userId]);

        if (!$checkUser->fetch(PDO::FETCH_ASSOC)) {
            $db->rollBack();
            http_response_code(404);
            echo json_encode(["status" => 404, "message" => "User not found"]);
            exit;
        }

        $tables = [
            "user_tokens",
            "user_preferences",
            "user_tags",
            "user_categories",
            "user_likes"
        ];

        foreach ($tables as $table) {
            $stmt = $db->prepare("DELETE FROM {$table} WHERE user_id = :userId");
            $stmt->execute([':userId' => $userId]);
        }

        $stmt = $db->prepare("DELETE FROM friends WHERE friend1_id = :userId OR friend2_id = :userId");
        $stmt->execute([':userId' => $userId]);

        $stmt = $db->prepare("DELETE FROM users WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);

        $checkUser->execute([':userId' => $userId]);

        if ($checkUser->fetch(PDO::FETCH_ASSOC)) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(["status" => 500, "message" => "User could not be deleted"]);
            exit;
        }

        $db->commit();
        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "message" => "User deleted",
            "deletedUserId" => $userId
        ]);
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["status" => 500, "message" => $e->getMessage()]);
        exit;
    }
}

http_response_code(400);
echo json_encode(["status" => 400, "message" => "Invalid action"]);
