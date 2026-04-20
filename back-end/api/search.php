<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once '../config.php';

// videoDuration
/*
 * any
 * long -> longer than 20mins
 * medium -> 4mins - 20mins
 * short -> <4mins
 */

// publishedAfter / publishedBefore
// RFC 3339 formatted date-time value (ex. : 1970-01-01T00:00:00Z)

// relevanceLanguage
// ISO 639-1 two-letter language code (ex. : ro 4 romanian)

// order
/*
 * date
 * rating
 * relevance
 * title
 * videoCount
 * viewCount
 */

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode([
        'error' => 'Missing search query'
    ]);
    exit;
}

$q = "q=" . urlencode($_GET['q']);
$videoDuration = isset($_GET['videoDuration']) ? "&videoDuration=" . $_GET['videoDuration'] : '';
$publishedAfter = isset($_GET['publishedAfter']) ? "&publishedAfter=" . $_GET['publishedAfter'] : '';
$publishedBefore = isset($_GET['publishedBefore']) ? "&publishedBefore=" . $_GET['publishedBefore'] : '';
$relevanceLanguage = isset($_GET['relevanceLanguage']) ? "&relevanceLanguage=" . $_GET['relevanceLanguage'] : '';
$order = isset($_GET['order']) ? "&orphr=" . $_GET['order'] : '';
$resultNumber = "&maxResults=" . 20;

$params = $q . $videoDuration . $publishedAfter . $publishedBefore . $relevanceLanguage . $order . $resultNumber;

$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&" . $params . "&key=" . YT_API_KEY;

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

echo json_encode($data);
