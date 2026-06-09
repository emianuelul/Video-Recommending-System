<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/friend_helpers.php';

setFriendApiHeaders("POST, OPTIONS");
handleOptionsRequest();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed");
}

$data = readJsonBody();

$friend1_id = $data["friend1_id"] ?? null;
$friend2_username = trim(getRequiredValue($data, ["friend2_username", "friend_username", "username"]) ?? "");
$friend2_id = $friend2_username !== "" ? getUserIdByUsername($friend2_username) : ($data["friend2_id"] ?? null);

if (!$friend1_id || ($friend2_username === "" && !$friend2_id)) {
    respond(400, "Missing friend username");
}

if ($friend2_username !== "" && !$friend2_id) {
    respond(404, "User not found");
}

if ($friend1_id == $friend2_id) {
    respond(400, "You cannot add yourself as a friend");
}

$check = $db->prepare("
    SELECT id, status FROM friends
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

$existingFriendship = $check->fetch(PDO::FETCH_ASSOC);

if ($existingFriendship && $existingFriendship["status"] === "accepted") {
    respond(409, "Already friends");
}

if ($existingFriendship) {
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
