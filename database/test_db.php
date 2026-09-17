<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied.\n");
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->connect();
    echo "CONNECTED_SUCCESS\n";
} catch (Exception $e) {
    echo "CONNECTION_ERROR\n";
}
