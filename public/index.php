<?php

// FixMyDevice - Public Web Application Front Controller

$isProduction = (getenv('APP_ENV') === 'production') || (getenv('VERCEL') == '1');

if ($isProduction) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Autoload core files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Router.php';

// Initialize session securely
Session::init();

// Dispatch Router
$router = new Router();
$router->dispatch();