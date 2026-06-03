<?php
require_once __DIR__ . '/../../class/TokenManager.php';
require_once __DIR__ . '/../../class/SimilarityCalculator.php';
require_once __DIR__ . '/../../util/auth.php';
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/utilities.php';

$token = auth();

$userId = TokenManager::getUserId($token);

//$userId = "2cb2ab06b4cfa99816d2c277eed74b03";

function getRecommendations() {
    global $db, $userId, $token;

    $calc = new SimilarityCalculator();

    $tagSelect = $db->prepare("SELECT tag, weight FROM user_tags WHERE user_id = :user_id ORDER BY weight DESC LIMIT 10");
    $catSelect = $db->prepare("SELECT category_id, weight FROM user_categories WHERE user_id = :user_id ORDER BY weight DESC LIMIT 5");
    $exclSelect = $db->prepare("SELECT video_id FROM user_likes WHERE user_id = :user_id");
    $userPrefsSelect = $db->prepare("SELECT languages, duration FROM user_preferences WHERE user_id = :user_id");

    $tagSelect->execute([
        ":user_id" => $userId
    ]);
    $catSelect->execute([
        ":user_id" => $userId
    ]);
    $exclSelect->execute([
        ":user_id" => $userId
    ]);
    $userPrefsSelect->execute([
        ":user_id" => $userId
    ]);

    $tags = $tagSelect->fetchAll(PDO::FETCH_ASSOC);
    $categories = $catSelect->fetchAll(PDO::FETCH_ASSOC);
    $excludedVids = $exclSelect->fetchAll(PDO::FETCH_ASSOC);
    $userPrefs = $userPrefsSelect->fetchAll(PDO::FETCH_ASSOC);

    $tagsArray = array_column($tags, 'tag');
    $tagWeights = array_column($tags, 'weight', 'tag');

    $categoriesArray = array_column($categories, 'category_id');
    $categoryWeights = array_column($categories, 'weight', 'category_id');

    $exclVidArray = array_column($excludedVids, 'video_id');

    $userLanguagesArrayJson = array_column($userPrefs, 'languages');
    $userDurationArrayJson = array_column($userPrefs, 'duration');
    $userLanguagesArray = json_decode($userLanguagesArrayJson[0], true);
    $userDurationArray = json_decode($userDurationArrayJson[0], true);

    $query = urlencode(implode(' ', array_slice($tagsArray, 0, 10)));

    $vids = search(
        $token,
        "q=" . $query,
        null,
        null,
        null,
        null,
        'relevance',
        "&maxResults=" . 24);

    $uniqueVids = array_filter($vids, fn($vidDTO) => !in_array($vidDTO->getId(), $exclVidArray));

    usort($uniqueVids, fn($vid1, $vid2) => $calc->calculateWithProfile($vid2, $tagWeights, $categoryWeights, $userLanguagesArray, $userDurationArray)
        -
        $calc->calculateWithProfile($vid1, $tagWeights, $categoryWeights, $userLanguagesArray, $userDurationArray)
    );

    $recs = array_slice($uniqueVids, 0, 20);

    return $recs;
}

header("Content-Type: application/json");
echo json_encode(getRecommendations());