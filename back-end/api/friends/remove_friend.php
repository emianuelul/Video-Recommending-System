<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/api_helpers.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed");
}

$data = readJsonBody();

$friendship_id = $data["friendship_id"] ?? null;
$friend1_id = $data["friend1_id"] ?? null;
$friend_username = trim($data["friend_username"] ?? "");
$friend_id = $friend_username !== "" ? getUserIdByUsername($friend_username) : ($data["friend_id"] ?? null);

if (!$friendship_id && (!$friend1_id || ($friend_username === "" && !$friend_id))) {
    respond(400, "Missing friend username");
}

if (!$friendship_id && $friend_username !== "" && !$friend_id) {
    respond(404, "User not found");
}

if (!$friendship_id && $friend1_id == $friend_id) {
    respond(400, "You cannot remove yourself from friends");
}

if ($friendship_id) {
    $stmt = $db->prepare("
        DELETE FROM friends
        WHERE id = :friendship_id
          AND status = 'accepted'
    ");

    $stmt->execute([":friendship_id" => $friendship_id]);
} else {
    $stmt = $db->prepare("
        DELETE FROM friends
        WHERE status = 'accepted'
          AND (
            (friend1_id = :friend1_id_1 AND friend2_id = :friend_id_1)
            OR
            (friend1_id = :friend_id_2 AND friend2_id = :friend1_id_2)
          )
    ");

    $stmt->execute([
        ":friend1_id_1" => $friend1_id,
        ":friend_id_1" => $friend_id,
        ":friend_id_2" => $friend_id,
        ":friend1_id_2" => $friend1_id
    ]);
}

if ($stmt->rowCount() === 0) {
    respond(404, "Friendship not found");
}

respond(200, "Friend removed");
