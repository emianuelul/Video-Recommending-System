<?php

require_once __DIR__ . '/../class/TokenManager.php';

function auth() {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $token);

    if ($token === '') {
        $token = $_GET['token'] ?? '';
    }

    if (!TokenManager::checkTokenValidity($token)) {
        exit;
    }

    return $token;
}
