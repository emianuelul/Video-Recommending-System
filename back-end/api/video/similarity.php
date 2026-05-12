<?php
require_once __DIR__ . "/../class/SimilarityCalculator.php";
require_once __DIR__ . "/../class/VideoDTO.php";

$calc = new SimilarityCalculator();
$json = json_decode(file_get_contents('php://input'), true);

$videoJson1 = $json["video1"];
$videoJson2 = $json["video2"];

$videoDTO1 = new VideoDTO($videoJson1);
$videoDTO2 = new VideoDTO($videoJson2);

header("Content-Type: application/json");
echo $calc->calculate($videoDTO1, $videoDTO2);

