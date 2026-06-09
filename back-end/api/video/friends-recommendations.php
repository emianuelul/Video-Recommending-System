<?php
require_once __DIR__ . '/../../class/TokenManager.php';
require_once __DIR__ . '/../../class/SimilarityCalculator.php';
require_once __DIR__ . '/../../util/auth.php';
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/utilities.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$token = auth();
$userId = TokenManager::getUserId($token);
//$userId = "2cb2ab06b4cfa99816d2c277eed74b03";

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
        LIMIT 24"
    );

    $friendsLikesSelect->execute([":uid" => $userId]);
    $friendsLikes = $friendsLikesSelect->fetchAll(PDO::FETCH_ASSOC);
    $likedIds = array_column($friendsLikes, 'video_id');

    if (empty($likedIds)) {
        return [];
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

$recs = getFriendsRecommendations();

if (isset($_GET['format']) && $_GET['format'] === 'rss') {
    header('Content-Type: application/rss+xml; charset=utf-8');
    $watchUrl = 'https://www.youtube.com/watch?v=';
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<rss version="2.0"><channel>';
    echo '<title>Your Friends\' Recommended Videos</title>';
    echo '<link>' . $watchUrl . '</link>';
    echo '<description>Videos your friends have liked</description>';
    foreach ($recs as $video) {
        echo '<item>';
        echo '<title>' . htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') . '</title>';
        echo '<link>' . $watchUrl . htmlspecialchars($video['id'], ENT_QUOTES, 'UTF-8') . '</link>';
        echo '<description>' . htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') . '</description>';
        if (!empty($video['thumbnails']['medium']['url'])) {
            echo '<enclosure url="' . htmlspecialchars($video['thumbnails']['medium']['url'], ENT_QUOTES, 'UTF-8') . '" type="image/jpeg" />';
        }
        echo '<guid isPermaLink="false">' . htmlspecialchars($video['id'], ENT_QUOTES, 'UTF-8') . '</guid>';
        echo '<pubDate>' . gmdate('r') . '</pubDate>';
        echo '</item>';
    }
    echo '</channel></rss>';
    exit;
}

header('Content-Type: application/json');
echo json_encode($recs);
