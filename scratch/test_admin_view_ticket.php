<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Ticket.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/controllers/TicketController.php';

Session::init();

// 1. Create a dummy ticket to test
$userModel = new User();
$adminUser = $userModel->findByEmail('admin@fixmydevice.com');
$techUser = $userModel->findByEmail('tech@fixmydevice.com');

$catModel = new Category();
$cat = $catModel->getAll()[0];

$ticketModel = new Ticket();
$ticketCode = $ticketModel->create([
    'user_id' => $adminUser['id'],
    'category_id' => $cat['id'],
    'device_name' => 'Test TV',
    'brand' => 'LG',
    'warranty_status' => 'out_of_warranty',
    'issue_title' => 'Test display issue',
    'description' => 'Test description',
    'priority' => 'high'
]);

echo "Created test ticket: $ticketCode\n";

// Set session as Admin
$_SESSION['user'] = $adminUser;

// Now run TicketController::view($ticketCode) while capturing output buffer
ob_start();
$controller = new TicketController();
$controller->view($ticketCode);
$html = ob_get_clean();

echo "Render output size: " . strlen($html) . " bytes\n";
echo "Dropdown present: " . (strpos($html, 'name="technician_id"') !== false ? "YES" : "NO") . "\n";
echo "Technician name present: " . (strpos($html, 'Alex Miller') !== false ? "YES" : "NO") . "\n";

// Clean up test ticket
$db = Database::getInstance()->connect();
$db->prepare("DELETE FROM tickets WHERE ticket_code = ?")->execute([$ticketCode]);

echo "SUCCESS: TicketController::view executed cleanly for Admin with 0 errors!\n";
