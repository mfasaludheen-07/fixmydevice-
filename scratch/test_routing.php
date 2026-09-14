<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Router.php';

$results = [];

// Test url=admin
try {
    $_GET['url'] = 'admin';
    $router = new Router();
    // buffer output
    ob_start();
    $router->dispatch();
    ob_end_clean();
    $results[] = "url=admin: OK";
} catch (Throwable $e) {
    $results[] = "url=admin: ERROR - " . $e->getMessage();
}

// Test url=ticket
try {
    $_GET['url'] = 'ticket';
    $router = new Router();
    ob_start();
    $router->dispatch();
    ob_end_clean();
    $results[] = "url=ticket: OK";
} catch (Throwable $e) {
    $results[] = "url=ticket: ERROR - " . $e->getMessage();
}

// Test url=technician
try {
    $_GET['url'] = 'technician';
    $router = new Router();
    ob_start();
    $router->dispatch();
    ob_end_clean();
    $results[] = "url=technician: OK";
} catch (Throwable $e) {
    $results[] = "url=technician: ERROR - " . $e->getMessage();
}

// Test url=ticket/render (protected base method)
try {
    $_GET['url'] = 'ticket/render';
    $router = new Router();
    ob_start();
    $router->dispatch();
    ob_end_clean();
    $results[] = "url=ticket/render: OK";
} catch (Throwable $e) {
    $results[] = "url=ticket/render: ERROR - " . $e->getMessage();
}

// Test url=ticket/does_not_exist
try {
    $_GET['url'] = 'ticket/does_not_exist';
    $router = new Router();
    ob_start();
    $router->dispatch();
    ob_end_clean();
    $results[] = "url=ticket/does_not_exist: OK";
} catch (Throwable $e) {
    $results[] = "url=ticket/does_not_exist: ERROR - " . $e->getMessage();
}

echo implode("\n", $results) . "\n";
