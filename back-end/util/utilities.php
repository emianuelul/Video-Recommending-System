<?php

require_once __DIR__ . '/../../db/database.php';
require_once __DIR__ . '/../config.php';

$categoriesList = [
    1 => "Film & Animation",
    2 => "Autos & Vehicles",
    10 => "Music",
    15 => "Pets & Animals",
    17 => "Sports",
    18 => "Short Movies",
    19 => "Travel & Events",
    20 => "Gaming",
    21 => "Videoblogging",
    22 => "People & Blogs",
    23 => "Comedy",
    24 => "Entertainment",
    25 => "News & Politics",
    26 => "Howto & Style",
    27 => "Education",
    28 => "Science & Technology",
    29 => "Nonprofits & Activism",
    30 => "Movies",
    31 => "Anime/Animation",
    32 => "Action/Adventure",
    33 => "Classics",
    34 => "Comedy",
    35 => "Documentary",
    36 => "Drama",
    37 => "Family",
    38 => "Foreign",
    39 => "Horror",
    40 => "Sci-Fi/Fantasy",
    41 => "Thriller",
    42 => "Shorts",
    43 => "Shows",
    44 => "Trailers"
];

$AVAILABLE_HOURS = 24;
$DECAY_HALF_LIFE_HOURS = 720;

function decayUserWeights($userId) {
    global $db, $DECAY_HALF_LIFE_HOURS;
    $now = time();

    $tables = ['user_tags', 'user_categories'];
    foreach ($tables as $table) {
        $select = $db->prepare("SELECT id, weight, last_interacted_at FROM {$table} WHERE user_id = :user_id");
        $select->execute([':user_id' => $userId]);
        $update = $db->prepare("UPDATE {$table} SET weight = :weight WHERE id = :id");

        while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
            $elapsedHours = ($now - strtotime($row['last_interacted_at'])) / 3600;
            if ($elapsedHours >= $DECAY_HALF_LIFE_HOURS) {
                $newWeight = max(0, $row['weight'] - 1);
                $update->execute([':weight' => $newWeight, ':id' => $row['id']]);
            }
        }
    }
}

function search($token,
                $q,
                $videoDuration = null,
                $publishedAfter = null,
                $publishedBefore = null,
                $relevanceLanguage = null,
                $order = null,
                $resultNumber = null) {
    global $db;
    $videoDTOArray = [];
    $apiKey = "&key=" . YT_API_KEY;

    // Each optional segment must already carry its own &key=value prefix,
    // OR be passed as a plain value here and prefixed below.
    // We normalise $order here so callers can pass either "&order=relevance"
    // or just "relevance" without causing concatenation bugs.
    if ($order !== null && !str_starts_with($order, '&')) {
        $order = "&order=" . urlencode($order);
    }

    $params = $q .
        ($videoDuration ?? '') .
        ($publishedAfter ?? '') .
        ($publishedBefore ?? '') .
        ($relevanceLanguage ?? '') .
        ($order ?? '') .
        ($resultNumber ?? '');

    $url      = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&" . $params . $apiKey;
    $urlNoApi = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&" . $params;

    $req = file_get_contents($url);

    if ($req === false) {
        $error = error_get_last();

        echo json_encode([
            'error' => 'Failed to fetch YouTube API response',
            'details' => $error ? $error['message'] : 'Unknown error',
            'url' => $urlNoApi
        ]);
        exit;
    }

    $data = json_decode($req, true);

    if ($data === null) {
        echo json_encode([
            'error' => 'Invalid JSON returned by YouTube API',
            'raw' => $req
        ]);
        exit;
    }

    $idsArray = [];
    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            if (isset($item['id']['videoId'])) {
                $idsArray[] = $item['id']['videoId'];
            }
        }
    }

    $videoIds = implode(',', $idsArray);

    if (!empty($videoIds)) {
        $detailsUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics,topicDetails&id=" . $videoIds . $apiKey;
        $detailsReq = file_get_contents($detailsUrl);

        $detailsData = json_decode($detailsReq, true);
        if (isset($detailsData['items'])) {
            $userId = TokenManager::getUserId($token);

            foreach ($detailsData['items'] as $item) {
                $videoDTO = new VideoDTO($item);

                $stmt = $db->prepare("
            SELECT 1 FROM user_likes
            WHERE video_id = :video_id AND user_id = :user_id;
            ");

                $stmt->execute([
                    ":video_id" => $videoDTO->getId(),
                    ":user_id" => $userId
                ]);

                $videoDTO->setIsLikedByUser($stmt->fetch() != null);
                $videoDTOArray[] = $videoDTO;
            }
        }
    }
    return $videoDTOArray;
}

function countryTrending($token, $regionCode, $resultNumber = 24) {
    global $db;
    $apiKey = "&key=" . YT_API_KEY;

    $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics,topicDetails&chart=mostPopular&regionCode=" . urlencode($regionCode) . "&maxResults=" . (int)$resultNumber . $apiKey;
    $urlNoApi = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics,topicDetails&chart=mostPopular&regionCode=" . urlencode($regionCode) . "&maxResults=" . (int)$resultNumber;

    $req = file_get_contents($url);

    if ($req === false) {
        $error = error_get_last();

        echo json_encode([
            'error' => 'Failed to fetch YouTube API response',
            'details' => $error ? $error['message'] : 'Unknown error',
            'url' => $urlNoApi
        ]);
        exit;
    }

    $data = json_decode($req, true);

    if ($data === null) {
        echo json_encode([
            'error' => 'Invalid JSON returned by YouTube API',
            'raw' => $req
        ]);
        exit;
    }

    $videoDTOArray = [];
    if (isset($data['items'])) {
        $userId = TokenManager::getUserId($token);

        foreach ($data['items'] as $item) {
            $videoDTO = new VideoDTO($item);

            $stmt = $db->prepare("
                SELECT 1 FROM user_likes
                WHERE video_id = :video_id AND user_id = :user_id;
            ");

            $stmt->execute([
                ":video_id" => $videoDTO->getId(),
                ":user_id" => $userId
            ]);

            $videoDTO->setIsLikedByUser($stmt->fetch() != null);
            $videoDTOArray[] = $videoDTO;
        }
    }

    return $videoDTOArray;
}
