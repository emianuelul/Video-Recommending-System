<?php
header('Access-Control-Allow-Origin: *');
require_once '../config.php';
require_once '../class/VideoDTO.php';

$q = "q=" . urlencode($_GET['q']);
$videoDuration = isset($_GET['videoDuration']) ? "&videoDuration=" . $_GET['videoDuration'] : '';
$publishedAfter = isset($_GET['publishedAfter']) ? "&publishedAfter=" . $_GET['publishedAfter'] : '';
$publishedBefore = isset($_GET['publishedBefore']) ? "&publishedBefore=" . $_GET['publishedBefore'] : '';
$relevanceLanguage = isset($_GET['relevanceLanguage']) ? "&relevanceLanguage=" . $_GET['relevanceLanguage'] : '';
$order = isset($_GET['order']) ? "&order=" . $_GET['order'] : '';
$resultNumber = "&maxResults=" . 20;
$apiKey = "&key=" . YT_API_KEY;

$params = $q . $videoDuration . $publishedAfter . $publishedBefore . $relevanceLanguage . $order . $resultNumber;

$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&" . $params . $apiKey;

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode([
        'error' => 'Missing search query'
    ]);
    exit;
}


$req = @file_get_contents($url);

if ($req === false) {
    $error = error_get_last();

    echo json_encode([
        'error' => 'Failed to fetch YouTube API response',
        'details' => $error ? $error['message'] : 'Unknown error',
        'url' => $url
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
    $videoDTOArray = [];
    if (isset($detailsData['items'])) {
        foreach ($detailsData['items'] as $item) {
            $videoDTOArray[] = new VideoDTO($item);
        }
    }


    header("Content-Type: application/json");
    echo json_encode($videoDTOArray);
}
