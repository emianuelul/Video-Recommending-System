<?php
require_once '../../db/database.php';
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => 'DB conn']);