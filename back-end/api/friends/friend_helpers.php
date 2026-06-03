<?php

function setFriendApiHeaders($methods = "GET, POST, OPTIONS") {
    $allowedOrigins = ["http://127.0.0.1:8001", "http://localhost:8001"];
    $origin = $_SERVER["HTTP_ORIGIN"] ?? "";

    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: " . $origin);
    } else {
        header("Access-Control-Allow-Origin: http://localhost:8001");
    }

    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: " . $methods);
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json");
}

function handleOptionsRequest() {
    if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
        http_response_code(204);
        exit;
    }
}

function respond($status, $message, $data = []) {
    http_response_code($status);
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

