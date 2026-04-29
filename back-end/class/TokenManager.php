<?php
require_once __DIR__ . "/../../db/database.php";
require_once __DIR__ . "/../utilities.php";

class TokenManager {
    private static function cleanUpOldTokens() {
        global $db;
        global $AVAILABLE_HOURS;

        $stmt = $db->prepare("DELETE FROM user_tokens WHERE created_at + :availableHours > date('now')");
        $stmt->execute([':availableHours' => $AVAILABLE_HOURS]);
    }

    public static function checkTokenValidity($token): bool {
        global $db;
        self::cleanUpOldTokens();

        $stmt = $db->prepare("SELECT token FROM user_tokens WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $dbToken = $stmt->fetch();

        if (!$dbToken) {
            http_response_code(401);
            echo json_encode(["status" => 401, "message" => "Unauthorized, token is not valid"]);

            return false;
        }

        return true;
    }
}