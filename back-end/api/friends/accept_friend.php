<?php
require_once __DIR__ . '/../../../db/database.php';
require_once __DIR__ . '/../../util/api_helpers.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed");
}

$data = readJsonBody();

$request_id = $data["request_id"] ?? null;
$current_user_id = $data["friend1_id"] ?? ($data["user_id"] ?? null);
$other_username = trim(getRequiredValue($data, ["friend_username", "username"]) ?? "");
$other_user_id = $other_username !== "" ? getUserIdByUsername($other_username) : ($data["friend2_id"] ?? ($data["friend_id"] ?? null));

if (!$request_id && (!$current_user_id || ($other_username === "" && !$other_user_id))) {
    respond(400, "Missing friend username");
}

if (!$request_id && $other_username !== "" && !$other_user_id) {
    respond(404, "User not found");
}

if ($request_id) {
    if (!$current_user_id) {
        respond(400, "Missing current user ID");
    }

    $stmt = $db->prepare("
        UPDATE friends
        SET status = 'accepted'
        WHERE id = :request_id
          AND friend2_id = :current_user_id
          AND status = 'pending'
    ");

    $stmt->execute([
        ":request_id" => $request_id,
        ":current_user_id" => $current_user_id
    ]);
} else {
    $stmt = $db->prepare("
        UPDATE friends
        SET status = 'accepted'
        WHERE friend1_id = :other_user_id
          AND friend2_id = :current_user_id
          AND status = 'pending'
    ");

    $stmt->execute([
        ":other_user_id" => $other_user_id,
        ":current_user_id" => $current_user_id
    ]);
}

if ($stmt->rowCount() > 0) {
    respond(200, "Friend request accepted");
}

if ($request_id) {
    $check = $db->prepare("
        SELECT status, friend2_id FROM friends
        WHERE id = :request_id
        LIMIT 1
    ");

    $check->execute([":request_id" => $request_id]);
} else {
    $check = $db->prepare("
        SELECT status, friend2_id FROM friends
        WHERE friend1_id = :other_user_id
          AND friend2_id = :current_user_id
        LIMIT 1
    ");

    $check->execute([
        ":other_user_id" => $other_user_id,
        ":current_user_id" => $current_user_id
    ]);
}

$friendship = $check->fetch(PDO::FETCH_ASSOC);

if (!$friendship) {
    respond(404, "Friend request not found");
}

if ($friendship["friend2_id"] !== $current_user_id) {
    respond(403, "Only the request receiver can accept this friend request");
}

if ($friendship["status"] === "accepted") {
    respond(409, "Friend request is already accepted");
}

respond(409, "Friend request is not pending");
