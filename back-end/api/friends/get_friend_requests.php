<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/api_helpers.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    respond(405, "Method not allowed");
}

$friend1_id = $_GET["friend1_id"] ?? null;
$type = $_GET["type"] ?? "incoming";

if (!$friend1_id) {
    respond(400, "Missing user ID");
}

if (!in_array($type, ["incoming", "outgoing", "all"], true)) {
    respond(400, "Invalid request type");
}

if ($type === "incoming") {
    $stmt = $db->prepare("
        SELECT
            f.id AS request_id,
            f.friend1_id AS requester_id,
            requester.username AS requester_username,
            f.friend2_id AS receiver_id,
            receiver.username AS receiver_username,
            f.created_at,
            'incoming' AS type
        FROM friends f
        JOIN users requester ON requester.id = f.friend1_id
        JOIN users receiver ON receiver.id = f.friend2_id
        WHERE f.friend2_id = :friend1_id
          AND f.status = 'pending'
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([":friend1_id" => $friend1_id]);
} elseif ($type === "outgoing") {
    $stmt = $db->prepare("
        SELECT
            f.id AS request_id,
            f.friend1_id AS requester_id,
            requester.username AS requester_username,
            f.friend2_id AS receiver_id,
            receiver.username AS receiver_username,
            f.created_at,
            'outgoing' AS type
        FROM friends f
        JOIN users requester ON requester.id = f.friend1_id
        JOIN users receiver ON receiver.id = f.friend2_id
        WHERE f.friend1_id = :friend1_id
          AND f.status = 'pending'
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([":friend1_id" => $friend1_id]);
} else {
    $stmt = $db->prepare("
        SELECT
            f.id AS request_id,
            f.friend1_id AS requester_id,
            requester.username AS requester_username,
            f.friend2_id AS receiver_id,
            receiver.username AS receiver_username,
            f.created_at,
            CASE
                WHEN f.friend2_id = :incoming_friend1_id THEN 'incoming'
                ELSE 'outgoing'
            END AS type
        FROM friends f
        JOIN users requester ON requester.id = f.friend1_id
        JOIN users receiver ON receiver.id = f.friend2_id
        WHERE (f.friend1_id = :outgoing_friend1_id OR f.friend2_id = :incoming_friend1_id_where)
          AND f.status = 'pending'
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([
        ":incoming_friend1_id" => $friend1_id,
        ":outgoing_friend1_id" => $friend1_id,
        ":incoming_friend1_id_where" => $friend1_id
    ]);
}

respond(200, "Friend requests fetched", [
    "requests" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
