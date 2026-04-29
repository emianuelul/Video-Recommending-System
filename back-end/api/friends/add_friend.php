<?php
header("Access-Control-Allow-Origin: http://localhost:8001");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Tye, Authorization");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once '../../../db/database.php';
require_once '../../utilities.php';

function respond($status, $message) {
    http_response_code($status);
    echo json_encode([
        "status" => $status,
        "message" => $message
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed");
}

$data = json_decode(file_get_contents("php://input"), true);

$friend1_id = $data["friend1_id"] ?? null;
$friend2_id = $data["friend2_id"] ?? null;

if (!$friend1_id || !$friend2_id) {
    respond(400, "Missing friends IDs");
}

if ($friend1_id == $friend2_id) {
    respond(400, "You cannot add yourself as a friends");
}

$check = $db->prepare("
    SELECT id FROM friends
    WHERE 
        (friend1_id = :friend1_id AND friend2_id = :friend2_id)
        OR
        (friend1_id = :friend2_id AND friend2_id = :friend1_id)
    LIMIT 1
");

$check->execute([
    ":friend1_id" => $friend1_id,
    ":friend2_id" => $friend2_id
]);

if ($check->fetch()) {
    respond(409, "Friend request already exists");
}

$stmt = $db->prepare("
    INSERT INTO friends (friend1_id, friend2_id, status)
    VALUES (:friend1_id, :friend2_id, 'pending')
");

$stmt->execute([
    ":friend1_id" => $friend1_id,
    ":friend2_id" => $friend2_id
]);

respond(200, "Friend request sent");
