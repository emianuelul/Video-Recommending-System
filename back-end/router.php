<?php
header('Access-Control-Allow-Origin: http://localhost:8001');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if (file_exists($file) && is_file($file)) {
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
        return false;
    }
    require_once $file;
} else {
    http_response_code(404);
    echo json_encode(["error" => "Endpoint not found"]);
}
