<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/friend_helpers.php';

setFriendApiHeaders("POST, OPTIONS");
handleOptionsRequest();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed");
}

$data = readJsonBody();

$friendship_id = $data["friendship_id"] ?? null;
$user_id = getRequiredValue($data, ["user_id", "friend1_id"]);
$friend_id = getRequiredValue($data, ["friend_id", "friend2_id"]);

if (!$friendship_id && (!$user_id || !$friend_id)) {
    respond(400, "Missing friends IDs");
}

if (!$friendship_id && $user_id == $friend_id) {
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
            (friend1_id = :user_id_1 AND friend2_id = :friend_id_1)
            OR
            (friend1_id = :friend_id_2 AND friend2_id = :user_id_2)
          )
    ");

    $stmt->execute([
        ":user_id_1" => $user_id,
        ":friend_id_1" => $friend_id,
        ":friend_id_2" => $friend_id,
        ":user_id_2" => $user_id
    ]);
}

if ($stmt->rowCount() === 0) {
    respond(404, "Friendship not found");
}

respond(200, "Friend removed");
