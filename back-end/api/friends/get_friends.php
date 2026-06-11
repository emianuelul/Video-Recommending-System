<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/api_helpers.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    respond(405, "Method not allowed");
}

$friend1_id = $_GET["friend1_id"] ?? null;

if (!$friend1_id) {
    respond(400, "Missing user ID");
}

$stmt = $db->prepare("
    SELECT
        f.id AS friendship_id,
        CASE
            WHEN f.friend1_id = :case_friend1_id THEN f.friend2_id
            ELSE f.friend1_id
        END AS friend_id,
        u.username AS friend_username,
        f.created_at
    FROM friends f
    JOIN users u ON u.id = CASE
        WHEN f.friend1_id = :join_friend1_id THEN f.friend2_id
        ELSE f.friend1_id
    END
    WHERE (f.friend1_id = :where_friend1_id_1 OR f.friend2_id = :where_friend1_id_2)
      AND f.status = 'accepted'
    ORDER BY u.username COLLATE NOCASE ASC
");

$stmt->execute([
    ":case_friend1_id" => $friend1_id,
    ":join_friend1_id" => $friend1_id,
    ":where_friend1_id_1" => $friend1_id,
    ":where_friend1_id_2" => $friend1_id
]);

respond(200, "Friends fetched", [
    "friends" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
