<?php

require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/config.php';
require_once __DIR__ . '/../../class/TokenManager.php';
require_once __DIR__ . '/../../util/auth.php';

auth();

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

global $db;

function getBody() {
    return json_decode(file_get_contents("php://input"), true);
}

function updateTag(PDO $db, string $userId, string $tag, float $delta) {

    $stmt = $db->prepare("
        SELECT weight FROM user_tags
        WHERE user_id = :user_id AND tag = :tag
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':tag' => $tag
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $weight = max(0, min(10, $delta > 0 ? $delta : 1));

        $stmt = $db->prepare("
            INSERT INTO user_tags (user_id, tag, weight, last_interacted_at)
            VALUES (:user_id, :tag, :weight, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':tag' => $tag,
            ':weight' => $weight
        ]);

        return;
    }

    $newWeight = $row['weight'] + $delta;

    $newWeight = max(0, min(10, $newWeight));

    $stmt = $db->prepare("
        UPDATE user_tags
        SET weight = :weight,
            last_interacted_at = CURRENT_TIMESTAMP
        WHERE user_id = :user_id AND tag = :tag
    ");

    $stmt->execute([
        ':weight' => $newWeight,
        ':user_id' => $userId,
        ':tag' => $tag
    ]);
}

function updateCategory(PDO $db, string $userId, int $categoryId, float $delta) {

    $stmt = $db->prepare("
        SELECT weight FROM user_categories
        WHERE user_id = :user_id AND category_id = :category_id
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':category_id' => $categoryId
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $weight = max(0, min(10, $delta > 0 ? $delta : 1));

        $stmt = $db->prepare("
            INSERT INTO user_categories (user_id, category_id, weight, last_interacted_at)
            VALUES (:user_id, :category_id, :weight, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':category_id' => $categoryId,
            ':weight' => $weight
        ]);

        return;
    }

    $newWeight = $row['weight'] + $delta;
    $newWeight = max(0, min(10, $newWeight));

    $stmt = $db->prepare("
        UPDATE user_categories
        SET weight = :weight,
            last_interacted_at = CURRENT_TIMESTAMP
        WHERE user_id = :user_id AND category_id = :category_id
    ");

    $stmt->execute([
        ':weight' => $newWeight,
        ':user_id' => $userId,
        ':category_id' => $categoryId
    ]);
}

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $token);

if (!TokenManager::checkTokenValidity($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = TokenManager::getUserId($token);

$body = getBody();
$videoId = $body['id'] ?? null;

if (!$videoId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing videoId']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tags = $body['tags'] ?? [];
    $categoryId = isset($body['categoryId']) ? (int)$body['categoryId'] : null;

    $stmt = $db->prepare("
        INSERT OR IGNORE INTO user_likes (user_id, video_id)
        VALUES (:user_id, :video_id)
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':video_id' => $videoId
    ]);

    $stmt = $db->prepare("
        INSERT OR IGNORE INTO interacted_videos (id, video_json)
        VALUES (:id, :video_json)
    ");

    $stmt->execute([
        ':id' => $videoId,
        ':video_json' => json_encode($body)
    ]);

    foreach ($tags as $tag) {
        updateTag($db, $userId, $tag, 1.0);
    }

    if ($categoryId !== null) {
        updateCategory($db, $userId, $categoryId, 1.0);
    }

    echo json_encode([
        "success" => true,
        "liked" => true
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $tags = $body['tags'] ?? [];
    $categoryId = isset($body['categoryId']) ? (int)$body['categoryId'] : null;

    $stmt = $db->prepare("
        DELETE FROM user_likes
        WHERE user_id = :user_id AND video_id = :video_id
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':video_id' => $videoId
    ]);

    foreach ($tags as $tag) {
        updateTag($db, $userId, $tag, -0.5);
    }

    if ($categoryId !== null) {
        updateCategory($db, $userId, $categoryId, -0.5);
    }

    echo json_encode([
        "success" => true,
        "liked" => false
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);