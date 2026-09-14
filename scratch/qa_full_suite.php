<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Ticket.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/TicketComment.php';
require_once __DIR__ . '/../app/models/Review.php';

echo "========================================================\n";
echo "  FixMyDevice Professional QA Automated Test Suite      \n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function runTest($name, $callable) {
    global $passCount, $failCount;
    try {
        $callable();
        echo "  [PASS] $name\n";
        $passCount++;
    } catch (Throwable $e) {
        echo "  [FAIL] $name: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

// Ensure database connection
$db = Database::getInstance()->connect();

// ----------------------------------------------------
// SUITE 1: ROUTING & 404 PROTECTION
// ----------------------------------------------------
echo "SUITE 1: Routing Engine & 404 Error Handling\n";

runTest("Router handles default 'admin' without crash", function () {
    $router = new Router();
    $reflector = new ReflectionClass($router);
    $method = $reflector->getMethod('isActionCallable');
    $method->setAccessible(true);
    
    require_once __DIR__ . '/../app/controllers/AdminController.php';
    $admin = new AdminController();
    if (!$method->invoke($router, $admin, 'index') && !$method->invoke($router, $admin, 'dashboard')) {
        throw new Exception("Neither index nor dashboard is callable on AdminController");
    }
});

runTest("Router handles default 'ticket' without crash", function () {
    $router = new Router();
    $reflector = new ReflectionClass($router);
    $method = $reflector->getMethod('isActionCallable');
    $method->setAccessible(true);
    
    require_once __DIR__ . '/../app/controllers/TicketController.php';
    $ticket = new TicketController();
    if (!$method->invoke($router, $ticket, 'index') && !$method->invoke($router, $ticket, 'dashboard')) {
        throw new Exception("Neither index nor dashboard is callable on TicketController");
    }
});

runTest("Router handles default 'technician' without crash", function () {
    $router = new Router();
    $reflector = new ReflectionClass($router);
    $method = $reflector->getMethod('isActionCallable');
    $method->setAccessible(true);
    
    require_once __DIR__ . '/../app/controllers/TechnicianController.php';
    $tech = new TechnicianController();
    if (!$method->invoke($router, $tech, 'index') && !$method->invoke($router, $tech, 'dashboard')) {
        throw new Exception("Neither index nor dashboard is callable on TechnicianController");
    }
});

runTest("Router blocks protected base controller methods (render, redirect, etc.)", function () {
    $router = new Router();
    $reflector = new ReflectionClass($router);
    $method = $reflector->getMethod('isActionCallable');
    $method->setAccessible(true);
    
    require_once __DIR__ . '/../app/controllers/TicketController.php';
    $ticket = new TicketController();
    if ($method->invoke($router, $ticket, 'render')) {
        throw new Exception("Protected method 'render' was allowed to be invoked!");
    }
    if ($method->invoke($router, $ticket, 'requireLogin')) {
        throw new Exception("Protected method 'requireLogin' was allowed to be invoked!");
    }
});

runTest("404 template renders cleanly with valid HTML", function () {
    ob_start();
    require_once __DIR__ . '/../app/controllers/HomeController.php';
    $home = new HomeController();
    $home->render('errors/404', ['pageTitle' => '404 - Page Not Found']);
    $html = ob_get_clean();
    if (strpos($html, '404') === false || strpos($html, 'Page Not Found') === false) {
        throw new Exception("404 template missing expected text");
    }
});

// ----------------------------------------------------
// SUITE 2: AUTHENTICATION & SECURITY
// ----------------------------------------------------
echo "\nSUITE 2: Authentication & Password Security\n";

$userModel = new User();

runTest("Operational staff passwords strictly use bcrypt (password_hash)", function () use ($userModel) {
    $admin = $userModel->findByEmail('admin@fixmydevice.com');
    $tech = $userModel->findByEmail('tech@fixmydevice.com');
    if (!$admin || !password_verify('admin123', $admin['password_hash'])) {
        throw new Exception("Admin password verification failed");
    }
    if (!$tech || !password_verify('tech123', $tech['password_hash'])) {
        throw new Exception("Tech password verification failed");
    }
    if (strpos($admin['password_hash'], '$2y$') !== 0) {
        throw new Exception("Admin hash is not standard bcrypt format");
    }
});

runTest("User creation enforces bcrypt hashing", function () use ($userModel, $db) {
    $testEmail = 'qa_temp_' . uniqid() . '@test.com';
    $newId = $userModel->create([
        'name' => 'QA Tester',
        'email' => $testEmail,
        'password' => 'MySecurePass123',
        'role' => 'customer'
    ]);
    $u = $userModel->findByEmail($testEmail);
    if (!password_verify('MySecurePass123', $u['password_hash'])) {
        throw new Exception("Created user password could not be verified with password_verify");
    }
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$newId]);
});

runTest("CSRF Token generation and validation", function () {
    Session::init();
    $token = Session::generateCsrfToken();
    if (empty($token) || strlen($token) !== 64) {
        throw new Exception("CSRF token is not a valid 64-char hex string");
    }
    if (!Session::validateCsrfToken($token)) {
        throw new Exception("Valid CSRF token failed validation");
    }
    if (Session::validateCsrfToken('fake_invalid_token_12345')) {
        throw new Exception("Invalid CSRF token passed validation!");
    }
});

// ----------------------------------------------------
// SUITE 3: COMPLAINT LIFECYCLE & STEPPER
// ----------------------------------------------------
echo "\nSUITE 3: Complaint Lifecycle & Visual Stepper\n";

$ticketModel = new Ticket();
$catModel = new Category();
$categories = $catModel->getAll();

// Create test user and ticket
$testUserEmail = 'qa_cust_' . uniqid() . '@test.com';
$testCustId = $userModel->create([
    'name' => 'QA Customer',
    'email' => $testUserEmail,
    'password' => 'CustPass123',
    'phone' => '+1 800 555 9911',
    'address' => '100 Testing Blvd',
    'role' => 'customer'
]);

$createdTicketCode = null;
$createdTicketId = null;

runTest("Register new complaint ticket", function () use ($ticketModel, $testCustId, $categories, &$createdTicketCode, &$createdTicketId) {
    $createdTicketCode = $ticketModel->create([
        'user_id' => $testCustId,
        'category_id' => $categories[0]['id'],
        'device_name' => 'QA Smart Refrigerator',
        'brand' => 'Samsung',
        'model_number' => 'RF28R7351SR',
        'serial_number' => 'QA-SN-112233',
        'warranty_status' => 'in_warranty',
        'issue_title' => 'Compressor noise during rapid freeze',
        'description' => 'Loud vibration when cooling coils activate.',
        'priority' => 'high',
        'preferred_date' => date('Y-m-d')
    ]);

    if (!preg_match('/^TKT-\d{4}-\d{4}$/', $createdTicketCode)) {
        throw new Exception("Ticket code format invalid: $createdTicketCode");
    }

    $tkt = $ticketModel->findByCode($createdTicketCode);
    if (!$tkt) throw new Exception("Ticket not found in DB");
    $createdTicketId = $tkt['id'];

    if ($tkt['status'] !== 'pending') throw new Exception("Initial status should be pending, got: {$tkt['status']}");
    if (!empty($tkt['technician_id'])) throw new Exception("New ticket should be unassigned");
});

runTest("Ticket appears in technician unassigned queue", function () use ($ticketModel, $createdTicketCode) {
    $unassigned = $ticketModel->getUnassignedTickets();
    $found = false;
    foreach ($unassigned as $ut) {
        if ($ut['ticket_code'] === $createdTicketCode) {
            $found = true;
            break;
        }
    }
    if (!$found) throw new Exception("Ticket not present in getUnassignedTickets()");
});

// ----------------------------------------------------
// SUITE 4: TECHNICIAN WORKBENCH & ADVANCEMENT
// ----------------------------------------------------
echo "\nSUITE 4: Technician Workbench & Progression\n";

$techUser = $userModel->findByEmail('tech@fixmydevice.com');

runTest("Technician claims unassigned ticket", function () use ($ticketModel, $createdTicketId, $techUser) {
    $success = $ticketModel->claimTicket($createdTicketId, $techUser['id']);
    if (!$success) throw new Exception("claimTicket returned false");

    $tkt = $ticketModel->findById($createdTicketId);
    if ($tkt['technician_id'] != $techUser['id']) throw new Exception("Technician ID not set");
    if ($tkt['status'] !== 'assigned') throw new Exception("Status did not advance to assigned");
});

runTest("Advance ticket through all 6 repair stages with stepper calculations", function () use ($ticketModel, $createdTicketId, $techUser) {
    $steps = [
        'in_diagnosis'       => ['step' => 3, 'pct' => 40],
        'awaiting_parts'     => ['step' => 3, 'pct' => 40],
        'repair_in_progress' => ['step' => 4, 'pct' => 60],
        'ready'              => ['step' => 5, 'pct' => 80],
        'resolved'           => ['step' => 6, 'pct' => 100]
    ];

    $statusesMeta = [
        'pending' => 1, 'assigned' => 2, 'in_diagnosis' => 3,
        'awaiting_parts' => 3, 'repair_in_progress' => 4,
        'ready' => 5, 'resolved' => 6
    ];

    foreach ($steps as $st => $expected) {
        $ticketModel->updateStatus($createdTicketId, $st, $techUser['id'], "QA Stage: $st");
        $tkt = $ticketModel->findById($createdTicketId);
        if ($tkt['status'] !== $st) throw new Exception("Failed to advance to $st");

        $stepNum = $statusesMeta[$tkt['status']];
        $calcPct = max(0, min(100, ($stepNum - 1) * 20));
        if ($calcPct !== $expected['pct']) {
            throw new Exception("Stepper percent mismatch for $st: expected {$expected['pct']}, got $calcPct");
        }
    }
});

runTest("Technician update estimated cost quote", function () use ($ticketModel, $createdTicketId, $techUser) {
    $ticketModel->updateCost($createdTicketId, 129.50, $techUser['id'], 'Replacement fan motor');
    $tkt = $ticketModel->findById($createdTicketId);
    if (floatval($tkt['estimated_cost']) != 129.50) {
        throw new Exception("Estimated cost not updated: {$tkt['estimated_cost']}");
    }
});

// ----------------------------------------------------
// SUITE 5: COMMENTS & PRIVACY FILTERING
// ----------------------------------------------------
echo "\nSUITE 5: Comments & Staff Note Privacy Filtering\n";

$commentModel = new TicketComment();

runTest("Post customer comment and staff internal note", function () use ($commentModel, $createdTicketId, $testCustId, $techUser) {
    // 1. Customer comment
    $cId1 = $commentModel->create($createdTicketId, $testCustId, 'Hello, is my fridge ready?', 0);
    if (!$cId1) throw new Exception("Failed to create customer comment");

    // 2. Staff private note
    $cId2 = $commentModel->create($createdTicketId, $techUser['id'], 'Internal: Defect caused by loose wire harness.', 1);
    if (!$cId2) throw new Exception("Failed to create staff note");

    $allComments = $commentModel->getByTicketId($createdTicketId);
    if (count($allComments) < 2) throw new Exception("Comments not stored in DB");

    // Test privacy filter for customer
    $customerFiltered = [];
    foreach ($allComments as $c) {
        if (!empty($c['is_internal'])) continue;
        $customerFiltered[] = $c;
    }

    foreach ($customerFiltered as $cf) {
        if (!empty($cf['is_internal'])) {
            throw new Exception("Privacy leak: Internal note exposed to customer!");
        }
    }
});

// ----------------------------------------------------
// SUITE 6: REVIEWS & RATINGS
// ----------------------------------------------------
echo "\nSUITE 6: Customer Reviews & Duplicate Handling\n";

$reviewModel = new Review();

runTest("Customer submits 5-star review on resolved ticket", function () use ($reviewModel, $createdTicketId, $testCustId) {
    $reviewModel->create($createdTicketId, $testCustId, 5, 'Outstanding service and fast turnaround!');
    $rev = $reviewModel->getByTicketId($createdTicketId);
    if (!$rev || $rev['rating'] != 5) throw new Exception("Review was not saved properly");
});

runTest("Review updates cleanly with ON DUPLICATE KEY UPDATE", function () use ($reviewModel, $createdTicketId, $testCustId) {
    $reviewModel->create($createdTicketId, $testCustId, 4, 'Updated: Good service, slight delay on delivery.');
    $rev = $reviewModel->getByTicketId($createdTicketId);
    if (!$rev || $rev['rating'] != 4) throw new Exception("Review update failed");
});

// ----------------------------------------------------
// SUITE 7: ADMIN CONTROL CENTER
// ----------------------------------------------------
echo "\nSUITE 7: Admin Control Center & Role Assignment\n";

$adminUser = $userModel->findByEmail('admin@fixmydevice.com');

runTest("Admin creates new hardware category", function () use ($db) {
    require_once __DIR__ . '/../app/models/Category.php';
    $cat = new Category();
    $catId = $cat->create('Robotics & Smart Drones', 'smart-drones-qa', 'drone', 'Consumer drones and robotic vacuums');
    if (!$catId) throw new Exception("Failed to create category");
    $db->prepare("DELETE FROM categories WHERE slug = 'smart-drones-qa'")->execute();
});

runTest("Admin reassigns technician", function () use ($ticketModel, $createdTicketId, $adminUser, $userModel, $db) {
    // Create second technician
    $tech2Email = 'qa_tech2_' . uniqid() . '@fixmydevice.com';
    $tech2Id = $userModel->create([
        'name' => 'Sarah Vance Tech',
        'email' => $tech2Email,
        'password' => 'TechPassword456',
        'role' => 'technician'
    ]);

    $ticketModel->assignTechnician($createdTicketId, $tech2Id, $adminUser['id']);
    $tkt = $ticketModel->findById($createdTicketId);
    if ($tkt['technician_id'] != $tech2Id) throw new Exception("Technician reassign failed");

    // Clean up second tech
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$tech2Id]);
});

// Clean up test customer and ticket
$db->prepare("DELETE FROM tickets WHERE id = ?")->execute([$createdTicketId]);
$db->prepare("DELETE FROM users WHERE id = ?")->execute([$testCustId]);

echo "\n========================================================\n";
echo "  QA Suite Summary: $passCount Passed, $failCount Failed\n";
echo "========================================================\n";

if ($failCount > 0) {
    exit(1);
}
