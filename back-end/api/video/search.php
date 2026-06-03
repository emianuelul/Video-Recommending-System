<?php

require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/config.php';
require_once __DIR__ . '/../../class/VideoDTO.php';
require_once __DIR__ . '/../../util/auth.php';

$token = auth();

$q = "q=" . urlencode($_GET['q']);
$videoDuration = isset($_GET['videoDuration']) ? "&videoDuration=" . $_GET['videoDuration'] : '';
$publishedAfter = isset($_GET['publishedAfter']) ? "&publishedAfter=" . $_GET['publishedAfter'] : '';
$publishedBefore = isset($_GET['publishedBefore']) ? "&publishedBefore=" . $_GET['publishedBefore'] : '';
$relevanceLanguage = isset($_GET['relevanceLanguage']) ? "&relevanceLanguage=" . $_GET['relevanceLanguage'] : '';
$order = isset($_GET['order']) ? "&order=" . $_GET['order'] : '';
$resultNumber = "&maxResults=" . (int)MAX_SEARCH_RESULTS;

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode([
        'error' => 'Missing search query'
    ]);
    exit;
}


echo json_encode(search($token, $q, $videoDuration, $publishedAfter, $publishedBefore, $relevanceLanguage, $order, $resultNumber));