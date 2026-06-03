<?php
require_once __DIR__ . '/../../class/TokenManager.php';
require_once __DIR__ . '/../../class/SimilarityCalculator.php';
require_once __DIR__ . '/../../util/auth.php';
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/utilities.php';

//$token = auth();

//$userId = TokenManager::getUserId($token);

$userId = "2cb2ab06b4cfa99816d2c277eed74b03";

function getFriendsRecommendations() {
    global $db, $userId;

    $friendsLikesSelect = $db->prepare(
        "WITH friend_ids AS (
            SELECT friend1_id AS fid FROM friends WHERE friend2_id = :uid AND status = 'accepted'
            UNION
            SELECT friend2_id AS fid FROM friends WHERE friend1_id = :uid AND status = 'accepted'
        )
        SELECT ul.video_id, COUNT(*) as freq
        FROM user_likes ul
        JOIN friend_ids f ON ul.user_id = f.fid
        WHERE ul.video_id NOT IN (SELECT video_id FROM user_likes WHERE user_id = :uid)
        GROUP BY ul.video_id
        ORDER BY freq DESC
        LIMIT 15"
    );

    $friendsLikesSelect->execute([":uid" => $userId]);
    $friendsLikes = $friendsLikesSelect->fetchAll(PDO::FETCH_ASSOC);
    $likedIds = array_column($friendsLikes, 'video_id');

    if (empty($likedIds)) {
        return ["friends" => []];
    }

    $placeholders = implode(',', array_fill(0, count($likedIds), '?'));

    $videoDTOSelect = $db->prepare(
        "SELECT video_json FROM interacted_videos WHERE id IN ($placeholders)"
    );
    $videoDTOSelect->execute($likedIds);
    $videoDTOs = $videoDTOSelect->fetchAll(PDO::FETCH_COLUMN);

    $likedCheck = $db->prepare(
        "SELECT video_id FROM user_likes WHERE user_id = ? AND video_id IN ($placeholders)"
    );
    $likedCheck->execute([$userId, ...$likedIds]);
    $userLikedIds = array_flip($likedCheck->fetchAll(PDO::FETCH_COLUMN));

    $result = [];
    foreach ($videoDTOs as $videoJson) {
        $video = json_decode($videoJson, true);
        $result[] = $video;
    }

    return $result;
}

header("Content-Type: application/json");
echo json_encode(getFriendsRecommendations());