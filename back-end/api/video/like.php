<?php

require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/config.php';
require_once __DIR__ . '/../../class/TokenManager.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

global $db;

function getBody() {
    return json_decode(file_get_contents("php://input"), true);
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
$videoId = $body['videoId'] ?? null;

if (!$videoId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing videoId']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

    echo json_encode([
        "success" => true,
        "liked" => true
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $stmt = $db->prepare("
        DELETE FROM user_likes
        WHERE user_id = :user_id AND video_id = :video_id
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':video_id' => $videoId
    ]);

    echo json_encode([
        "success" => true,
        "liked" => false
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);