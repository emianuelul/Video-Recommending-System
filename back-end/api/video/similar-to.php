<?php
require_once __DIR__ . '/../../class/TokenManager.php';
require_once __DIR__ . '/../../class/SimilarityCalculator.php';
require_once __DIR__ . '/../../class/VideoDTO.php';
require_once __DIR__ . '/../../util/auth.php';
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/utilities.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$token = auth();
$body = json_decode(file_get_contents('php://input'), true);

if (empty($body['video'])) {
    echo json_encode(["error" => "Missing video DTO"]);
    exit;
}

$sourceDto = VideoDTO::fromSerialized($body['video']);

$tagSelect = $db->prepare("SELECT tag, weight FROM user_tags WHERE user_id = :user_id ORDER BY weight DESC LIMIT 10");
$catSelect = $db->prepare("SELECT category_id, weight FROM user_categories WHERE user_id = :user_id ORDER BY weight DESC LIMIT 5");
$prefsSelect = $db->prepare("SELECT languages, duration FROM user_preferences WHERE user_id = :user_id");
$exclSelect = $db->prepare("SELECT video_id FROM user_likes WHERE user_id = :user_id");

$userId = TokenManager::getUserId($token);

$tagSelect->execute([":user_id" => $userId]);
$catSelect->execute([":user_id" => $userId]);
$prefsSelect->execute([":user_id" => $userId]);
$exclSelect->execute([":user_id" => $userId]);

$tags = $tagSelect->fetchAll(PDO::FETCH_ASSOC);
$categories = $catSelect->fetchAll(PDO::FETCH_ASSOC);
$prefs = $prefsSelect->fetch(PDO::FETCH_ASSOC);
$excludedVids = $exclSelect->fetchAll(PDO::FETCH_ASSOC);

$tagWeights = array_column($tags, 'weight', 'tag');
$categoryWeights = array_column($categories, 'weight', 'category_id');
$exclVidArray = array_column($excludedVids, 'video_id');
$userLanguagesArray = json_decode($prefs['languages'] ?? '[]', true) ?: [];
$userDurationArray = json_decode($prefs['duration'] ?? '[]', true) ?: [];

// Build a short, safe query from the source video's tags + category.
// Cap individual tags at 30 chars and the full query at 100 chars so the
// YouTube API URL stays well under its 2 KB limit.
$rawTags     = array_filter($sourceDto->getTags() ?? [], fn($t) => strlen($t) <= 30);
$queryTags   = array_slice(array_unique(array_map(fn($t) => strtolower($t), $rawTags)), 0, 5);
$queryParts  = array_filter(array_merge($queryTags, [$sourceDto->getCategoryId()]));
$queryString = implode(' ', $queryParts);
if (strlen($queryString) > 100) {
    $queryString = substr($queryString, 0, 100);
}
$query = urlencode($queryString);

$durationSecs = $sourceDto->getDurationSeconds();
$videoDuration = null;
if ($durationSecs > 0) {
    if ($durationSecs < 240)       $videoDuration = "&videoDuration=short";
    elseif ($durationSecs <= 1200) $videoDuration = "&videoDuration=medium";
    else                           $videoDuration = "&videoDuration=long";
}

$candidates = search($token, "q=" . $query, $videoDuration, null, null, null, 'relevance', "&maxResults=" . 24);

$calc = new SimilarityCalculator();

$filtered = array_filter($candidates, fn($cand) =>
    $cand->getId() !== $sourceDto->getId() && !in_array($cand->getId(), $exclVidArray)
);

usort($filtered, fn($a, $b) =>
    $calc->calculateWithProfile($b, $tagWeights, $categoryWeights, $userLanguagesArray, $userDurationArray)
    - $calc->calculateWithProfile($a, $tagWeights, $categoryWeights, $userLanguagesArray, $userDurationArray)
);

$top = array_slice($filtered, 0, 15);

header('Content-Type: application/json');
echo json_encode([
    'source' => $sourceDto,
    'similar' => $top
]);
