<?php
header('Access-Control-Allow-Origin: *');
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

$q = "q=" . urlencode($_GET['q']);
$videoDuration = isset($_GET['videoDuration']) ? "&videoDuration=" . $_GET['videoDuration'] : '';
$publishedAfter = isset($_GET['publishedAfter']) ? "&publishedAfter=" . $_GET['publishedAfter'] : '';
$publishedBefore = isset($_GET['publishedBefore']) ? "&publishedBefore=" . $_GET['publishedBefore'] : '';
$relevanceLanguage = isset($_GET['relevanceLanguage']) ? "&relevanceLanguage=" . $_GET['relevanceLanguage'] : '';
$order = isset($_GET['order']) ? "&order=" . $_GET['order'] : '';
$resultNumber = "&maxResults=" . 20;

$params = $q . $videoDuration . $publishedAfter . $publishedBefore . $relevanceLanguage . $order . $resultNumber;

$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&" . $params . "&key=" . YT_API_KEY;

$req = file_get_contents($url);

echo $req;