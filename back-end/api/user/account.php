<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/auth.php';
require_once __DIR__ . '/../../util/utilities.php';

global $db;

$allowed_origins = ["http://127.0.0.1:8001", "http://localhost:8001"];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$token = auth();
$userId = TokenManager::getUserId($token);

if (!$userId) {
    http_response_code(401);
    echo json_encode(["status" => 401, "message" => "Unauthorized"]);
    exit;
}

function getCurrentUser($userId) {
    global $db;

    $stmt = $db->prepare("
        SELECT id, username, created_at
        FROM users
        WHERE id = :userId
        LIMIT 1
    ");
    $stmt->execute([':userId' => $userId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verifyCurrentPassword($userId, $password) {
    global $db;

    $stmt = $db->prepare("
        SELECT password_hash
        FROM users
        WHERE id = :userId
        LIMIT 1
    ");
    $stmt->execute([':userId' => $userId]);
    $hash = $stmt->fetchColumn();

    return $hash && password_verify($password, $hash);
}

function deleteUserData($userId) {
    global $db;

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
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = getCurrentUser($userId);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => 404, "message" => "User not found"]);
        exit;
    }

    $preferencesStmt = $db->prepare("
        SELECT languages, duration, country
        FROM user_preferences
        WHERE user_id = :userId
        LIMIT 1
    ");
    $preferencesStmt->execute([':userId' => $userId]);
    $preferences = $preferencesStmt->fetch(PDO::FETCH_ASSOC);

    $categoriesStmt = $db->prepare("
        SELECT category_id, weight, last_interacted_at
        FROM user_categories
        WHERE user_id = :userId
        ORDER BY weight DESC
    ");
    $categoriesStmt->execute([':userId' => $userId]);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

    global $categoriesList;
    foreach ($categories as &$cat) {
        $catId = $cat['category_id'];
        $cat['category_name'] = $categoriesList[$catId] ?? $catId;
    }
    unset($cat);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "user" => $user,
        "preferences" => $preferences ?: null,
        "categories" => $categories
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
$currentPassword = trim($data["current_password"] ?? '');

if ($currentPassword === '') {
    http_response_code(400);
    echo json_encode(["status" => 400, "message" => "Current password is required"]);
    exit;
}

if (!verifyCurrentPassword($userId, $currentPassword)) {
    http_response_code(403);
    echo json_encode(["status" => 403, "message" => "Current password is incorrect"]);
    exit;
}

if ($action === 'change_password') {
    $newPassword = trim($data["new_password"] ?? '');

    if ($newPassword === '') {
        http_response_code(400);
        echo json_encode(["status" => 400, "message" => "New password is required"]);
        exit;
    }

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password_hash = :passwordHash WHERE id = :userId");
    $stmt->execute([
        ':passwordHash' => $passwordHash,
        ':userId' => $userId
    ]);

    $stmt = $db->prepare("DELETE FROM user_tokens WHERE user_id = :userId AND token <> :token");
    $stmt->execute([
        ':userId' => $userId,
        ':token' => $token
    ]);

    http_response_code(200);
    echo json_encode(["status" => 200, "message" => "Password changed"]);
    exit;
}

if ($action === 'delete_account') {
    $db->beginTransaction();

    try {
        deleteUserData($userId);
        $db->commit();

        http_response_code(200);
        echo json_encode(["status" => 200, "message" => "Account deleted"]);
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
