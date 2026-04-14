<?php
require_once '../../db/database.php';
//header('Content-Type: application/json');
//phpinfo();
header('Content-Type: text/plain');

var_dump(extension_loaded('openssl'));
exit;
echo json_encode(['status' => 'ok', 'message' => 'DB conn']);
