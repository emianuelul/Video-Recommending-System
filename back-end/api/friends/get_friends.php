<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/friend_helpers.php';

setFriendApiHeaders("GET, OPTIONS");
handleOptionsRequest();

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    respond(405, "Method not allowed");
}

$user_id = $_GET["user_id"] ?? null;

if (!$user_id) {
    respond(400, "Missing user ID");
}

$stmt = $db->prepare("
    SELECT
        f.id AS friendship_id,
        CASE
            WHEN f.friend1_id = :case_user_id THEN f.friend2_id
            ELSE f.friend1_id
        END AS friend_id,
        u.username AS friend_username,
        f.created_at
    FROM friends f
    JOIN users u ON u.id = CASE
        WHEN f.friend1_id = :join_user_id THEN f.friend2_id
        ELSE f.friend1_id
    END
    WHERE (f.friend1_id = :where_user_id_1 OR f.friend2_id = :where_user_id_2)
      AND f.status = 'accepted'
    ORDER BY u.username COLLATE NOCASE ASC
");

$stmt->execute([
    ":case_user_id" => $user_id,
    ":join_user_id" => $user_id,
    ":where_user_id_1" => $user_id,
    ":where_user_id_2" => $user_id
]);

respond(200, "Friends fetched", [
    "friends" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
