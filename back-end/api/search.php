<?php
header('Access-Control-Allow-Origin: *');
require_once '../config.php';

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

$req = file_get_contents($url);
$data = json_decode($req, true);

$idsArray = [];
if (isset($data['items'])) {
    foreach ($data['items'] as $item) {
        if (isset($item['id']['videoId'])) {
            $idsArray[] = $item['id']['videoId'];
        }
    }
}

$videoIds = implode(',', $idsArray);

$detailsUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics,topicDetails&id=" . $videoIds . $apiKey;
$detailsReq = file_get_contents($detailsUrl);

header("Content-Type: application/json");
echo $detailsReq;