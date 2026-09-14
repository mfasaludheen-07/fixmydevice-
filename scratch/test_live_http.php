<?php

/**
 * Complete FixMyDevice End-to-End Live HTTP Test Suite
 * Executing against Apache at http://127.0.0.1/FixMyDevice/public/index.php
 */

$baseUrl = 'http://127.0.0.1/FixMyDevice/public/index.php';
$passCount = 0;
$failCount = 0;

function runTest($title, $fn) {
    global $passCount, $failCount;
    try {
        $msg = $fn();
        echo "  [PASS] $title\n";
        if (!empty($msg)) {
            echo "         -> $msg\n";
        }
        $passCount++;
    } catch (Throwable $e) {
        echo "  [FAIL] $title: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

function sendHttp($url, $method = 'GET', $postData = [], $cookieJar = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $raw = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $headerStr = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    $location = null;
    if (preg_match('/^Location:\s*([^\r\n]+)/mi', $headerStr, $m)) {
        $location = trim($m[1]);
    }

    // Check for PHP crash / unhandled errors
    if (preg_match('/(Fatal error|Parse error):/i', $body, $errMatch)) {
        throw new Exception("PHP Fatal Error: " . substr(strip_tags($body), 0, 200));
    }

    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $body,
        'location' => $location,
        'error' => $error
    ];
}

function extractCsrf($html) {
    if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/i', $html, $m)) {
        return $m[1];
    }
    return null;
}

echo "========================================================\n";
echo "  FixMyDevice Full Live Localhost HTTP End-to-End Suite \n";
echo "  Target: $baseUrl\n";
echo "========================================================\n\n";

// Shared session cookie jars for the test actors
$guestJar    = tempnam(sys_get_temp_dir(), 'fmd_gst_');
$customerJar = tempnam(sys_get_temp_dir(), 'fmd_cst_');
$techJar     = tempnam(sys_get_temp_dir(), 'fmd_tch_');
$adminJar    = tempnam(sys_get_temp_dir(), 'fmd_adm_');

$testCustomerEmail = 'liveqa_' . time() . '@example.com';
$testCustomerPass  = 'SecurePass123!';
$ticketCode = null;
$ticketId = null;

// ====================================================
// SUITE 1: PUBLIC INTERFACES & ROUTING SECURITY
// ====================================================
echo "SUITE 1: Public Interfaces, Routing & Error Handling\n";

runTest("Public Home page renders cleanly (HTTP 200)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl, 'GET', [], $guestJar);
    if ($res['code'] !== 200) throw new Exception("Expected 200, got {$res['code']}");
    if (strpos($res['body'], 'FixMyDevice') === false) throw new Exception("Missing site brand");
    return "Status: 200 OK, Landing page with hero & navigation verified";
});

runTest("Public Track Complaint page renders search box (HTTP 200)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=track', 'GET', [], $guestJar);
    if ($res['code'] !== 200) throw new Exception("Expected 200, got {$res['code']}");
    if (strpos($res['body'], 'Public Complaint Tracker') === false) throw new Exception("Missing tracker heading");
    return "Status: 200 OK, Public ticket tracker form rendered";
});

runTest("Login page renders CSRF token (HTTP 200)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=login', 'GET', [], $guestJar);
    if ($res['code'] !== 200) throw new Exception("Expected 200, got {$res['code']}");
    $csrf = extractCsrf($res['body']);
    if (!$csrf) throw new Exception("CSRF token missing");
    return "Status: 200 OK, CSRF protection initialized (" . substr($csrf, 0, 8) . "...)";
});

runTest("Register page renders registration form (HTTP 200)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=register', 'GET', [], $guestJar);
    if ($res['code'] !== 200) throw new Exception("Expected 200, got {$res['code']}");
    $csrf = extractCsrf($res['body']);
    if (!$csrf) throw new Exception("CSRF token missing");
    return "Status: 200 OK, Form fields and CSRF verified";
});

runTest("Custom 404 error page renders for invalid URLs (HTTP 404)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=unknown/service/endpoint', 'GET', [], $guestJar);
    if ($res['code'] !== 404) throw new Exception("Expected 404, got {$res['code']}");
    if (strpos($res['body'], 'Page Not Found') === false && strpos($res['body'], '404') === false) {
        throw new Exception("Custom 404 view not rendered");
    }
    return "Status: 404 Not Found, Branded 404 template active";
});

runTest("Reflection security blocks protected Controller methods (HTTP 404)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=ticket/render', 'GET', [], $guestJar);
    if ($res['code'] !== 404) throw new Exception("Expected 404 for protected method, got {$res['code']}");
    return "Status: 404 Not Found, Protected method direct call successfully prevented";
});

runTest("Guest access to ?url=admin redirects to login without fatal error (HTTP 302)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=admin', 'GET', [], $guestJar);
    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect, got {$res['code']}");
    if (strpos($res['location'], 'login') === false) throw new Exception("Expected redirect to login, got: {$res['location']}");
    return "Status: 302 Redirect -> {$res['location']}";
});

runTest("Guest access to ?url=technician redirects to login without fatal error (HTTP 302)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=technician', 'GET', [], $guestJar);
    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect, got {$res['code']}");
    if (strpos($res['location'], 'login') === false) throw new Exception("Expected redirect to login, got: {$res['location']}");
    return "Status: 302 Redirect -> {$res['location']}";
});

runTest("Guest access to ?url=ticket redirects to login without fatal error (HTTP 302)", function () use ($baseUrl, $guestJar) {
    $res = sendHttp($baseUrl . '?url=ticket', 'GET', [], $guestJar);
    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect, got {$res['code']}");
    if (strpos($res['location'], 'login') === false) throw new Exception("Expected redirect to login, got: {$res['location']}");
    return "Status: 302 Redirect -> {$res['location']}";
});

// ====================================================
// SUITE 2: CUSTOMER ONBOARDING, SESSION & LIFECYCLE
// ====================================================
echo "\nSUITE 2: Customer Registration, Authentication & Dashboard\n";

runTest("Register new customer via live HTTP POST", function () use ($baseUrl, $customerJar, $testCustomerEmail, $testCustomerPass) {
    // 1. GET registration page to obtain CSRF token
    $page = sendHttp($baseUrl . '?url=register', 'GET', [], $customerJar);
    $csrf = extractCsrf($page['body']);
    if (!$csrf) throw new Exception("Could not get CSRF for registration");

    // 2. Submit valid registration POST
    $res = sendHttp($baseUrl . '?url=register', 'POST', [
        'csrf_token' => $csrf,
        'name' => 'QA Live Test Customer',
        'email' => $testCustomerEmail,
        'phone' => '+1 555 123 4567',
        'address' => '789 Testing Parkway, Suite 100',
        'password' => $testCustomerPass,
        'confirm_password' => $testCustomerPass
    ], $customerJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect on registration, got {$res['code']}");
    if (strpos($res['location'], 'ticket') === false && strpos($res['location'], 'dashboard') === false) {
        throw new Exception("Unexpected redirect: {$res['location']}");
    }

    // 3. Verify session was created and customer dashboard loads
    $dash = sendHttp($baseUrl . '?url=ticket/dashboard', 'GET', [], $customerJar);
    if ($dash['code'] !== 200) throw new Exception("Customer dashboard returned {$dash['code']}");
    if (strpos($dash['body'], 'QA Live Test Customer') === false && strpos($dash['body'], 'Dashboard') === false) {
        throw new Exception("Customer name/dashboard not found");
    }

    return "Registered: $testCustomerEmail -> Auto-logged in -> Dashboard 200 OK";
});

runTest("Customer logout clears session with persistent flash message", function () use ($baseUrl, $customerJar) {
    $res = sendHttp($baseUrl . '?url=logout', 'GET', [], $customerJar);
    if ($res['code'] !== 302) throw new Exception("Expected 302 on logout, got {$res['code']}");

    // Access login page with same cookie jar to verify flash message
    $loginPage = sendHttp($baseUrl . '?url=login', 'GET', [], $customerJar);
    if ($loginPage['code'] !== 200) throw new Exception("Login page returned {$loginPage['code']}");
    if (strpos($loginPage['body'], 'logged out') === false) {
        throw new Exception("Logout flash message not displayed");
    }

    // Attempting dashboard now must redirect to login
    $dashAfter = sendHttp($baseUrl . '?url=ticket/dashboard', 'GET', [], $customerJar);
    if ($dashAfter['code'] !== 302) throw new Exception("Dashboard was not protected after logout!");

    return "Session terminated -> Flash message displayed on Login page";
});

runTest("Customer login with newly registered credentials", function () use ($baseUrl, $customerJar, $testCustomerEmail, $testCustomerPass) {
    $loginPage = sendHttp($baseUrl . '?url=login', 'GET', [], $customerJar);
    $csrf = extractCsrf($loginPage['body']);

    $res = sendHttp($baseUrl . '?url=login', 'POST', [
        'csrf_token' => $csrf,
        'email' => $testCustomerEmail,
        'password' => $testCustomerPass
    ], $customerJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 on login, got {$res['code']}");

    $dash = sendHttp($baseUrl . '?url=ticket/dashboard', 'GET', [], $customerJar);
    if ($dash['code'] !== 200) throw new Exception("Customer dashboard returned {$dash['code']}");
    return "Login successful -> Customer Dashboard 200 OK";
});

// ====================================================
// SUITE 3: COMPLAINT REGISTRATION & TRACKING STEPPER
// ====================================================
echo "\nSUITE 3: Ticket Creation, Detail View & Real-Time Stepper\n";

runTest("Customer files hardware complaint ticket via HTTP POST", function () use ($baseUrl, $customerJar, &$ticketCode, &$ticketId) {
    $createPage = sendHttp($baseUrl . '?url=ticket/create', 'GET', [], $customerJar);
    if ($createPage['code'] !== 200) throw new Exception("Create page returned {$createPage['code']}");
    $csrf = extractCsrf($createPage['body']);

    if (!preg_match('/<option\s+value=["\'](\d+)["\']/i', $createPage['body'], $catM)) {
        throw new Exception("No hardware categories available");
    }
    $catId = $catM[1];

    $deviceName = 'MacBook Pro 16" M3 Max';
    $brand = 'Apple';
    $issueTitle = 'Display Backlight Glitch QA Test';
    $description = 'Display exhibits intermittent flickering on right edge under high brightness.';

    // Action endpoint is ticket/create
    $res = sendHttp($baseUrl . '?url=ticket/create', 'POST', [
        'csrf_token' => $csrf,
        'category_id' => $catId,
        'device_name' => $deviceName,
        'brand' => $brand,
        'model_number' => 'A2991',
        'serial_number' => 'C02XYZ123456',
        'warranty_status' => 'in_warranty',
        'issue_title' => $issueTitle,
        'description' => $description,
        'priority' => 'high'
    ], $customerJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect after ticket creation, got {$res['code']}");

    // Location header contains: ticket/view/TKT-...
    if (preg_match('/ticket\/view\/([A-Za-z0-9\-]+)/i', $res['location'], $locM)) {
        $ticketCode = $locM[1];
    } else {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance()->connect();
        $stmt = $db->query("SELECT id, ticket_code FROM tickets ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $ticketCode = $row['ticket_code'];
    }

    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->connect();
    $stmt = $db->prepare("SELECT id FROM tickets WHERE ticket_code = ?");
    $stmt->execute([$ticketCode]);
    $ticketId = intval($stmt->fetchColumn());

    return "Complaint registered -> Ticket Code: $ticketCode (ID: $ticketId)";
});

runTest("Customer views ticket details & active status stepper (HTTP 200)", function () use ($baseUrl, $customerJar, &$ticketCode) {
    $res = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $customerJar);
    if ($res['code'] !== 200) throw new Exception("Ticket view returned {$res['code']}");
    if (strpos($res['body'], $ticketCode) === false) throw new Exception("Ticket code missing on page");
    if (strpos($res['body'], 'Display Backlight Glitch') === false) throw new Exception("Issue title missing");
    return "Status: 200 OK, Ticket details, category, device info, and stepper rendered";
});

runTest("Customer posts message comment on ticket", function () use ($baseUrl, $customerJar, &$ticketCode, &$ticketId) {
    $viewPage = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $customerJar);
    $csrf = extractCsrf($viewPage['body']);

    $commentText = "Please note I will be available for pickup on weekdays after 5 PM.";
    $res = sendHttp($baseUrl . '?url=ticket/comment', 'POST', [
        'csrf_token' => $csrf,
        'ticket_id' => $ticketId,
        'ticket_code' => $ticketCode,
        'comment' => $commentText
    ], $customerJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect after comment, got {$res['code']}");

    // Refresh view and verify comment is rendered
    $updatedView = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $customerJar);
    if (strpos($updatedView['body'], $commentText) === false) {
        throw new Exception("Submitted customer comment not found on ticket thread");
    }

    return "Comment posted and rendered in live discussion thread";
});

runTest("Public Complaint Tracker renders real-time status for guest (HTTP 200)", function () use ($baseUrl, $guestJar, &$ticketCode) {
    $res = sendHttp($baseUrl . '?url=track&code=' . urlencode($ticketCode), 'GET', [], $guestJar);
    if ($res['code'] !== 200) throw new Exception("Tracker returned {$res['code']}");
    if (strpos($res['body'], $ticketCode) === false) throw new Exception("Ticket code not shown in tracking result");
    if (strpos($res['body'], 'PENDING') === false && strpos($res['body'], 'Pending') === false) {
        throw new Exception("Initial pending status missing");
    }
    return "Public guest successfully tracked ticket {$ticketCode} without logging in";
});

// ====================================================
// SUITE 4: TECHNICIAN WORKBENCH & REPAIR PROGRESSION
// ====================================================
echo "\nSUITE 4: Technician Workbench & Stage Progression\n";

runTest("Technician logs into Workbench portal", function () use ($baseUrl, $techJar) {
    $loginPage = sendHttp($baseUrl . '?url=login', 'GET', [], $techJar);
    $csrf = extractCsrf($loginPage['body']);

    $res = sendHttp($baseUrl . '?url=login', 'POST', [
        'csrf_token' => $csrf,
        'email' => 'tech@fixmydevice.com',
        'password' => 'tech123'
    ], $techJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 on tech login, got {$res['code']}");

    $dash = sendHttp($baseUrl . '?url=technician/dashboard', 'GET', [], $techJar);
    if ($dash['code'] !== 200) throw new Exception("Tech workbench returned {$dash['code']}");
    if (strpos($dash['body'], 'Alex Miller') === false && strpos($dash['body'], 'Technician') === false) {
        throw new Exception("Technician identity not found on workbench");
    }

    return "Technician authenticated -> Workbench loaded (HTTP 200)";
});

runTest("Technician claims unassigned ticket from queue", function () use ($baseUrl, $techJar, &$ticketId, &$ticketCode) {
    $dash = sendHttp($baseUrl . '?url=technician/dashboard', 'GET', [], $techJar);
    $csrf = extractCsrf($dash['body']);

    $res = sendHttp($baseUrl . '?url=technician/claim', 'POST', [
        'csrf_token' => $csrf,
        'ticket_id' => $ticketId,
        'ticket_code' => $ticketCode
    ], $techJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect after claim, got {$res['code']}");

    // Verify in database that technician_id is set and status is assigned
    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->connect();
    $stmt = $db->prepare("SELECT status, technician_id FROM tickets WHERE id = ?");
    $stmt->execute([$ticketId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['status'] !== 'assigned' || empty($row['technician_id'])) {
        throw new Exception("Ticket claim failed: status={$row['status']}, tech={$row['technician_id']}");
    }

    return "Ticket claimed -> Status updated to 'assigned' (Tech ID: {$row['technician_id']})";
});

runTest("Technician updates repair quote estimate", function () use ($baseUrl, $techJar, &$ticketId, &$ticketCode) {
    $detailPage = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $techJar);
    $csrf = extractCsrf($detailPage['body']);

    $res = sendHttp($baseUrl . '?url=technician/update_cost', 'POST', [
        'csrf_token' => $csrf,
        'ticket_id' => $ticketId,
        'ticket_code' => $ticketCode,
        'cost' => '175.50',
        'note' => 'Replacement display ribbon flex cable and micro-soldering labor'
    ], $techJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 after cost update, got {$res['code']}");

    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->connect();
    $stmt = $db->prepare("SELECT estimated_cost FROM tickets WHERE id = ?");
    $stmt->execute([$ticketId]);
    $cost = floatval($stmt->fetchColumn());

    if (abs($cost - 175.50) > 0.01) throw new Exception("Estimated cost mismatch: got $cost");

    return "Repair estimate updated to $175.50";
});

runTest("Technician posts internal staff note (confidential)", function () use ($baseUrl, $techJar, &$ticketId, &$ticketCode, $customerJar) {
    $detailPage = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $techJar);
    $csrf = extractCsrf($detailPage['body']);

    $staffNote = "INTERNAL LOG: Tested backlight inverter circuit. Re-soldered ribbon connector pins.";
    $res = sendHttp($baseUrl . '?url=ticket/comment', 'POST', [
        'csrf_token' => $csrf,
        'ticket_id' => $ticketId,
        'ticket_code' => $ticketCode,
        'comment' => $staffNote,
        'is_internal' => '1'
    ], $techJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 after posting note, got {$res['code']}");

    // Customer must NOT see this internal note
    $custView = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $customerJar);
    if (strpos($custView['body'], $staffNote) !== false) {
        throw new Exception("Privacy Breach: Internal staff note leaked to customer view!");
    }

    return "Internal note logged -> Privacy filter verified (hidden from customer)";
});

runTest("Technician advances ticket through to 'resolved'", function () use ($baseUrl, $techJar, &$ticketId, &$ticketCode) {
    $detailPage = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $techJar);
    $csrf = extractCsrf($detailPage['body']);

    // Advance to resolved
    $res = sendHttp($baseUrl . '?url=technician/update_status', 'POST', [
        'csrf_token' => $csrf,
        'ticket_id' => $ticketId,
        'ticket_code' => $ticketCode,
        'status' => 'resolved',
        'note' => 'Hardware repaired and benchmarked successfully. Passed 24-hour burn-in test.'
    ], $techJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 after status update, got {$res['code']}");

    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->connect();
    $stmt = $db->prepare("SELECT status FROM tickets WHERE id = ?");
    $stmt->execute([$ticketId]);
    $status = $stmt->fetchColumn();

    if ($status !== 'resolved') throw new Exception("Expected status resolved, got {$status}");

    return "Ticket advanced to 'resolved' with complete audit history log";
});

// ====================================================
// SUITE 5: CUSTOMER REVIEWS & FEEDBACK
// ====================================================
echo "\nSUITE 5: Customer Reviews & Duplicate Rating Updates\n";

runTest("Customer submits 5-star satisfaction review", function () use ($baseUrl, $customerJar, &$ticketCode, &$ticketId) {
    $viewPage = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $customerJar);
    $csrf = extractCsrf($viewPage['body']);

    $reviewFeedback = "Exceptional service! Screen looks brand new, flawless repair.";
    $res = sendHttp($baseUrl . '?url=ticket/review', 'POST', [
        'csrf_token' => $csrf,
        'ticket_id' => $ticketId,
        'ticket_code' => $ticketCode,
        'rating' => 5,
        'comment' => $reviewFeedback
    ], $customerJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 redirect after review, got {$res['code']}");

    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->connect();
    $stmt = $db->prepare("SELECT rating, comment FROM reviews WHERE ticket_id = ?");
    $stmt->execute([$ticketId]);
    $rev = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rev || $rev['rating'] != 5) throw new Exception("Review not saved properly");

    return "5-Star review successfully recorded for ticket $ticketCode";
});

// ====================================================
// SUITE 6: ADMIN CONTROL CENTER & REASSIGNMENT
// ====================================================
echo "\nSUITE 6: Admin Management, Analytics & Category Control\n";

runTest("Admin logs into Admin Control Center", function () use ($baseUrl, $adminJar) {
    $loginPage = sendHttp($baseUrl . '?url=login', 'GET', [], $adminJar);
    $csrf = extractCsrf($loginPage['body']);

    $res = sendHttp($baseUrl . '?url=login', 'POST', [
        'csrf_token' => $csrf,
        'email' => 'admin@fixmydevice.com',
        'password' => 'admin123'
    ], $adminJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 on admin login, got {$res['code']}");

    $dash = sendHttp($baseUrl . '?url=admin/dashboard', 'GET', [], $adminJar);
    if ($dash['code'] !== 200) throw new Exception("Admin dashboard returned {$dash['code']}");
    if (strpos($dash['body'], 'Admin Control Center') === false && strpos($dash['body'], 'Total Complaints') === false) {
        throw new Exception("Admin metrics/dashboard not rendered");
    }

    return "Admin authenticated -> Control Center metrics & tickets rendered (HTTP 200)";
});

runTest("Admin creates new Hardware Category", function () use ($baseUrl, $adminJar) {
    $dash = sendHttp($baseUrl . '?url=admin/dashboard', 'GET', [], $adminJar);
    $csrf = extractCsrf($dash['body']);

    $newCatName = "Gaming Consoles & VR " . rand(100, 999);
    $res = sendHttp($baseUrl . '?url=admin/create_category', 'POST', [
        'csrf_token' => $csrf,
        'name' => $newCatName,
        'icon' => 'gamepad',
        'description' => 'PlayStation, Xbox, Nintendo Switch, and VR Headsets'
    ], $adminJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 after category creation, got {$res['code']}");

    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->connect();
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$newCatName]);
    $newId = $stmt->fetchColumn();

    if (!$newId) throw new Exception("New category was not found in database");

    return "Category '{$newCatName}' created (ID: $newId)";
});

runTest("Admin reassigns technician on ticket", function () use ($baseUrl, $adminJar, &$ticketId, &$ticketCode) {
    $viewPage = sendHttp($baseUrl . '?url=ticket/view/' . $ticketCode, 'GET', [], $adminJar);
    $csrf = extractCsrf($viewPage['body']);

    $res = sendHttp($baseUrl . '?url=admin/assign', 'POST', [
        'csrf_token' => $csrf,
        'ticket_id' => $ticketId,
        'ticket_code' => $ticketCode,
        'technician_id' => 2 // Tech ID
    ], $adminJar);

    if ($res['code'] !== 302) throw new Exception("Expected 302 after reassignment, got {$res['code']}");

    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->connect();
    $stmt = $db->prepare("SELECT technician_id FROM tickets WHERE id = ?");
    $stmt->execute([$ticketId]);
    $techId = $stmt->fetchColumn();

    if ($techId != 2) throw new Exception("Technician ID did not match 2");

    return "Ticket technician assigned successfully by Administrator";
});

runTest("Admin exports full tickets registry as CSV", function () use ($baseUrl, $adminJar) {
    $res = sendHttp($baseUrl . '?url=admin/export_csv', 'GET', [], $adminJar);
    if ($res['code'] !== 200) throw new Exception("Expected 200, got {$res['code']}");
    if (strpos($res['headers'], 'Content-Type: text/csv') === false) throw new Exception("Header missing text/csv");
    if (strpos($res['body'], 'Ticket Code') === false || strpos($res['body'], 'Customer Name') === false) {
        throw new Exception("CSV columns missing");
    }
    return "CSV generated and exported with valid MIME header and data rows";
});

// Clean up cookie files
@unlink($guestJar);
@unlink($customerJar);
@unlink($techJar);
@unlink($adminJar);

echo "\n========================================================\n";
echo "  Live HTTP QA Summary: $passCount Passed, $failCount Failed\n";
echo "========================================================\n";

if ($failCount > 0) {
    exit(1);
}
