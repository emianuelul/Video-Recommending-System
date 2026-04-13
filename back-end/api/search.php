<?php
require_once '../config.php';

$query = urlencode("teatrul national iasi");
$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . $query . "&key=" . YT_API_KEY;
$req = file_get_contents($url);

header("Content-Type: application/json");
echo $req;