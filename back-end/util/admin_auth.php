<?php

require_once __DIR__ . '/../../db/database.php';
require_once __DIR__ . '/utilities.php';

function getAdminToken() {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    return str_replace('Bearer ', '', $token);
}

function adminAuth() {
    global $db, $AVAILABLE_HOURS;

    $token = getAdminToken();

    if ($token === '') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["status" => 401, "message" => "Missing admin token"]);
        exit;
    }

    $stmt = $db->prepare("
        SELECT admin_id FROM admin_tokens
        WHERE token = :token
          AND datetime(created_at, '+' || :availableHours || ' hours') >= datetime('now')
        LIMIT 1
    ");

    $stmt->execute([
        ':token' => $token,
        ':availableHours' => $AVAILABLE_HOURS
    ]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["status" => 401, "message" => "Invalid admin token"]);
        exit;
    }

    return $admin['admin_id'];
}
