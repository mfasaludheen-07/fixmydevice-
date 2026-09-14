<?php

// Turn on error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload core files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Router.php';

// Initialize session
Session::init();

// Auto-run DB setup if database or users table is missing
try {
    $db = Database::getInstance()->connect();
    $check = $db->query("SHOW TABLES LIKE 'users'")->fetch();
    if (!$check) {
        require_once __DIR__ . '/../database/setup.php';
    }
} catch (Exception $e) {
    // If connection fails or tables missing, run setup
    require_once __DIR__ . '/../database/setup.php';
}

// Dispatch Router
$router = new Router();
$router->dispatch();