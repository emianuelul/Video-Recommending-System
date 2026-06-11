<?php

function respond($status, $message, $data = []) {
    http_response_code($status);
    header("Content-Type: application/json");

    echo json_encode(array_merge([
        "status" => $status,
        "message" => $message
    ], $data));
    exit;
}

function readJsonBody() {
    $rawBody = file_get_contents("php://input");
    $data = json_decode($rawBody, true);

    return is_array($data) ? $data : [];
}

function getRequiredValue($source, $keys) {
    foreach ($keys as $key) {
        if (isset($source[$key]) && $source[$key] !== "") {
            return $source[$key];
        }
    }

    return null;
}

function getUserIdByUsername($username) {
    global $db;

    $stmt = $db->prepare("
        SELECT id FROM users
        WHERE username = :username
        LIMIT 1
    ");

    $stmt->execute([":username" => trim($username)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ? $user["id"] : null;
}
