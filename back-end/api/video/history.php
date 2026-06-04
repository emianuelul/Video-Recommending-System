<?php
require_once __DIR__ . '/../../class/TokenManager.php';
require_once __DIR__ . '/../../util/auth.php';
require_once __DIR__ . '/../../../db/database.php';

$token = auth();
$userId = TokenManager::getUserId($token);

$likedSelect = $db->prepare("SELECT video_id, created_at FROM user_likes WHERE user_id = :user_id ORDER BY created_at DESC");
$likedSelect->execute([":user_id" => $userId]);
$likedRows = $likedSelect->fetchAll(PDO::FETCH_ASSOC);

if (empty($likedRows)) {
    echo json_encode([]);
    exit;
}

$ids = array_column($likedRows, 'video_id');
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$videoSelect = $db->prepare("SELECT id, video_json FROM interacted_videos WHERE id IN ($placeholders)");
$videoSelect->execute($ids);
$videoRows = $videoSelect->fetchAll(PDO::FETCH_KEY_PAIR);

$likedAt = array_column($likedRows, 'created_at', 'video_id');

$result = [];
foreach ($likedRows as $row) {
    $vid = $row['video_id'];
    $json = $videoRows[$vid] ?? null;
    $item = json_decode($json, true) ?: ['id' => $vid];
    $item['likedAt'] = $likedAt[$vid];
    $result[] = $item;
}

header('Content-Type: application/json');
echo json_encode($result);
