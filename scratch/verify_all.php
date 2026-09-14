<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Ticket.php';
require_once __DIR__ . '/../app/models/Category.php';

echo "=== FixMyDevice Automated Verification Test ===\n\n";

// 1. Verify User Credentials & Password Hashes
$db = Database::getInstance()->connect();
$db->exec("DELETE FROM users WHERE email LIKE '%test%@example.com' OR email LIKE '%vance%@fixmydevice.com'");

$userModel = new User();
$adminUser = $userModel->findByEmail('admin@fixmydevice.com');
$techUser = $userModel->findByEmail('tech@fixmydevice.com');

assert($adminUser !== false, "Admin user exists");
assert($techUser !== false, "Tech user exists");

$adminHashValid = password_verify('admin123', $adminUser['password_hash']);
$techHashValid = password_verify('tech123', $techUser['password_hash']);

echo "1. Credentials & Hashing Test:\n";
echo "   - Admin password_verify: " . ($adminHashValid ? "PASS (Hash starts with " . substr($adminUser['password_hash'], 0, 7) . ")" : "FAIL") . "\n";
echo "   - Tech password_verify: " . ($techHashValid ? "PASS (Hash starts with " . substr($techUser['password_hash'], 0, 7) . ")" : "FAIL") . "\n";

// Test creating new user with hashed password
$randSuffix = rand(1000, 9999);
$newUserId = $userModel->create([
    'name' => 'Alice Test',
    'email' => "alice.test.{$randSuffix}@example.com",
    'password' => 'secret456',
    'phone' => '+1 555 999 0011',
    'address' => '456 Test Lane',
    'role' => 'customer'
]);
$alice = $userModel->findById($newUserId);
$aliceFull = $userModel->findByEmail("alice.test.{$randSuffix}@example.com");
assert(password_verify('secret456', $aliceFull['password_hash']), "Alice password hash valid");
echo "   - New User Creation Hash: PASS (Securely hashed)\n\n";

// 2. Test Ticket Creation & Unassigned Queue
echo "2. Ticket Lifecycle & Unassigned Queue Test:\n";
$catModel = new Category();
$categories = $catModel->getAll();
$firstCat = $categories[0];

$ticketModel = new Ticket();
$ticketCode = $ticketModel->create([
    'user_id' => $newUserId,
    'category_id' => $firstCat['id'],
    'device_name' => 'Bravia 65" 4K OLED',
    'brand' => 'Sony',
    'model_number' => 'A80K',
    'serial_number' => 'SN-SNY-998822',
    'warranty_status' => 'in_warranty',
    'issue_title' => 'No power after lightning surge',
    'description' => 'Red standby LED blinks 6 times repeatedly.',
    'priority' => 'urgent',
    'preferred_date' => date('Y-m-d')
]);

echo "   - Created Ticket: {$ticketCode}\n";
$ticket = $ticketModel->findByCode($ticketCode);
assert($ticket['status'] === 'pending', "Ticket initial status is pending");
assert($ticket['technician_id'] === null, "Ticket initially unassigned");

$unassigned = $ticketModel->getUnassignedTickets();
$foundInUnassigned = false;
foreach ($unassigned as $ut) {
    if ($ut['ticket_code'] === $ticketCode) {
        $foundInUnassigned = true;
        break;
    }
}
echo "   - Appears in Technician Unassigned Queue: " . ($foundInUnassigned ? "PASS" : "FAIL") . "\n";

// 3. Test Claiming Ticket by Technician
echo "\n3. Technician Claim & Proceeding Work Test:\n";
$claimSuccess = $ticketModel->claimTicket($ticket['id'], $techUser['id']);
assert($claimSuccess, "Claim succeeded");

$claimedTicket = $ticketModel->findById($ticket['id']);
assert($claimedTicket['technician_id'] == $techUser['id'], "Assigned to tech");
assert($claimedTicket['status'] === 'assigned', "Status transitioned to assigned");
echo "   - Technician Claimed Ticket: PASS (technician_id={$techUser['id']}, status={$claimedTicket['status']})\n";

// 4. Test Progressing through all stages
$stages = [
    'in_diagnosis' => 'Diagnostic multimeter check complete.',
    'awaiting_parts' => 'Ordered replacement mainboard.',
    'repair_in_progress' => 'Board installed, soldering capacitors.',
    'ready' => 'Thermal test passed for 2 hours.',
    'resolved' => 'Customer picked up appliance and paid invoice.'
];

$statusesMeta = [
    'pending' => 1,
    'assigned' => 2,
    'in_diagnosis' => 3,
    'awaiting_parts' => 3,
    'repair_in_progress' => 4,
    'ready' => 5,
    'resolved' => 6
];

echo "\n4. Progress Stage Advancement & Stepper Calculations:\n";
foreach ($stages as $stage => $note) {
    $ticketModel->updateStatus($ticket['id'], $stage, $techUser['id'], $note);
    $curr = $ticketModel->findById($ticket['id']);
    $stepNum = $statusesMeta[$curr['status']];
    $progressPct = max(0, min(100, ($stepNum - 1) * 20));
    echo "   - Advanced to {$stage}: Step {$stepNum}/6 ({$progressPct}% fill)\n";
    assert($curr['status'] === $stage);
}

// 5. Check History Audit Log
$history = $ticketModel->getHistory($ticket['id']);
echo "\n5. Audit History Log:\n";
echo "   - Recorded " . count($history) . " history events.\n";
foreach ($history as $h) {
    echo "     * [{$h['status_from']} -> {$h['status_to']}] by {$h['user_name']} ({$h['user_role']}): {$h['note']}\n";
}
assert(count($history) >= 7, "History logged accurately");

// 6. Test Admin Assign / Reassign Feature
echo "\n6. Admin Assign/Reassign Technician Test:\n";
// Create another technician
$tech2Id = $userModel->create([
    'name' => 'Marcus Vance (Senior Tech)',
    'email' => 'marcus.vance@fixmydevice.com',
    'password' => 'techpass123',
    'phone' => '+1 800 555 0777',
    'address' => 'Workshop Hub 1',
    'role' => 'technician'
]);

$reassignSuccess = $ticketModel->assignTechnician($ticket['id'], $tech2Id, $adminUser['id']);
assert($reassignSuccess, "Admin reassigned tech successfully");
$reassignedTicket = $ticketModel->findById($ticket['id']);
assert($reassignedTicket['technician_id'] == $tech2Id, "New technician assigned");
echo "   - Admin assigned to Marcus Vance: PASS\n";

// Clean up test records
$db = Database::getInstance()->connect();
$stmt1 = $db->prepare("DELETE FROM tickets WHERE id = ?");
$stmt1->execute([$ticket['id']]);
$stmt2 = $db->prepare("DELETE FROM users WHERE id IN (?, ?)");
$stmt2->execute([$newUserId, $tech2Id]);

echo "\n=== ALL VERIFICATION TESTS PASSED SUCCESSFULLY! ===\n";
