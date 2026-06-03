<?php

require_once __DIR__ . '/../class/TokenManager.php';

function auth() {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $token);

    if (!TokenManager::checkTokenValidity($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    return $token;
}
