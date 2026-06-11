<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/api_helpers.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    respond(405, "Method not allowed");
}

$user_id = $_GET["user_id"] ?? null;
$type = $_GET["type"] ?? "incoming";

if (!$user_id) {
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
        WHERE f.friend2_id = :user_id
          AND f.status = 'pending'
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([":user_id" => $user_id]);
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
        WHERE f.friend1_id = :user_id
          AND f.status = 'pending'
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([":user_id" => $user_id]);
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
                WHEN f.friend2_id = :incoming_user_id THEN 'incoming'
                ELSE 'outgoing'
            END AS type
        FROM friends f
        JOIN users requester ON requester.id = f.friend1_id
        JOIN users receiver ON receiver.id = f.friend2_id
        WHERE (f.friend1_id = :outgoing_user_id OR f.friend2_id = :incoming_user_id_where)
          AND f.status = 'pending'
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([
        ":incoming_user_id" => $user_id,
        ":outgoing_user_id" => $user_id,
        ":incoming_user_id_where" => $user_id
    ]);
}

respond(200, "Friend requests fetched", [
    "requests" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
