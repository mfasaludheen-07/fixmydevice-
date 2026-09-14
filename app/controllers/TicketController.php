<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/TicketComment.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/User.php';

class TicketController extends Controller
{
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->requireLogin();
        $user = Session::user();

        if ($user['role'] === 'admin') {
            $this->redirect('admin/dashboard');
        } elseif ($user['role'] === 'technician') {
            $this->redirect('technician/dashboard');
        }

        $ticketModel = new Ticket();
        $tickets = $ticketModel->getTicketsByUser($user['id']);

        $this->render('customer/dashboard', [
            'pageTitle' => 'My Service Dashboard - FixMyDevice',
            'user' => $user,
            'tickets' => $tickets
        ]);
    }

    public function create()
    {
        $this->requireLogin();
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $user = Session::user();
            $categoryId = intval($_POST['category_id'] ?? 0);
            $deviceName = trim($_POST['device_name'] ?? '');
            $brand = trim($_POST['brand'] ?? '');
            $modelNumber = trim($_POST['model_number'] ?? '');
            $serialNumber = trim($_POST['serial_number'] ?? '');
            $warrantyStatus = $_POST['warranty_status'] ?? 'out_of_warranty';
            $issueTitle = trim($_POST['issue_title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';
            $preferredDate = $_POST['preferred_date'] ?? null;
            $locationLat = !empty($_POST['location_lat']) ? floatval($_POST['location_lat']) : null;
            $locationLng = !empty($_POST['location_lng']) ? floatval($_POST['location_lng']) : null;
            $locationAddress = trim($_POST['location_address'] ?? '');

            if (empty($categoryId) || empty($deviceName) || empty($brand) || empty($issueTitle) || empty($description)) {
                Session::setFlash('error', 'Please fill in all required fields marked with *');
                $this->render('customer/create_ticket', [
                    'pageTitle' => 'Register Repair Complaint - FixMyDevice',
                    'categories' => $categories,
                    'formData' => $_POST
                ]);
                return;
            }

            // Secure File Upload Handling
            $attachmentUrl = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['attachment']['tmp_name'];
                $fileSize = $_FILES['attachment']['size'];
                $origName = $_FILES['attachment']['name'];

                // 1. Max file size check (5MB)
                if ($fileSize > 5 * 1024 * 1024) {
                    Session::setFlash('error', 'Uploaded file exceeds maximum limit of 5MB.');
                    $this->render('customer/create_ticket', [
                        'pageTitle' => 'Register Repair Complaint - FixMyDevice',
                        'categories' => $categories,
                        'formData' => $_POST
                    ]);
                    return;
                }

                // 2. Strict Extension Whitelist
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
                if (!in_array($ext, $allowedExts)) {
                    Session::setFlash('error', 'Invalid file type. Only JPG, PNG, WEBP, GIF, and PDF files are allowed.');
                    $this->render('customer/create_ticket', [
                        'pageTitle' => 'Register Repair Complaint - FixMyDevice',
                        'categories' => $categories,
                        'formData' => $_POST
                    ]);
                    return;
                }

                // 3. MIME Type Verification via finfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fileTmp);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                if (!in_array($mimeType, $allowedMimes)) {
                    Session::setFlash('error', 'File contents do not match allowed image or PDF formats.');
                    $this->render('customer/create_ticket', [
                        'pageTitle' => 'Register Repair Complaint - FixMyDevice',
                        'categories' => $categories,
                        'formData' => $_POST
                    ]);
                    return;
                }

                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // 4. Secure Random Filename Generation
                $randomFilename = bin2hex(random_bytes(16)) . '.' . $ext;
                $targetFile = $uploadDir . $randomFilename;

                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $attachmentUrl = 'uploads/' . $randomFilename;
                }
            }

            $ticketModel = new Ticket();
            $ticketCode = $ticketModel->create([
                'user_id' => $user['id'],
                'category_id' => $categoryId,
                'device_name' => $deviceName,
                'brand' => $brand,
                'model_number' => $modelNumber,
                'serial_number' => $serialNumber,
                'warranty_status' => $warrantyStatus,
                'issue_title' => $issueTitle,
                'description' => $description,
                'priority' => $priority,
                'preferred_date' => $preferredDate ?: null,
                'attachment_url' => $attachmentUrl,
                'location_lat' => $locationLat,
                'location_lng' => $locationLng,
                'location_address' => $locationAddress ?: null
            ]);

            Session::setFlash('success', "Complaint ticket created successfully! Code: {$ticketCode}");
            $this->redirect('ticket/view/' . $ticketCode);
        }

        $this->render('customer/create_ticket', [
            'pageTitle' => 'Register Repair Complaint - FixMyDevice',
            'categories' => $categories
        ]);
    }

    public function view($ticketCode = null)
    {
        if (empty($ticketCode)) {
            $this->redirect('ticket/dashboard');
        }

        $ticketModel = new Ticket();
        $ticket = $ticketModel->findByCode($ticketCode);

        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            $this->redirect('ticket/dashboard');
        }

        $user = Session::user();
        
        // Strict Authorization Access Check
        // Guests or regular customers can ONLY view tickets that belong to them
        if (!$user) {
            // Unauthenticated users trying to access full ticket view are redirected to public tracker
            $this->redirect('track?code=' . urlencode($ticketCode));
        }

        if ($user['role'] === 'customer' && $ticket['user_id'] != $user['id']) {
            Session::setFlash('error', 'Unauthorized access to this ticket.');
            $this->redirect('ticket/dashboard');
        }

        $commentModel = new TicketComment();
        $allComments = $commentModel->getByTicketId($ticket['id']);

        // Privacy Filter: Regular customers cannot see internal staff notes
        $comments = [];
        foreach ($allComments as $c) {
            if ($user['role'] === 'customer' && !empty($c['is_internal'])) {
                continue;
            }
            $comments[] = $c;
        }

        $history = $ticketModel->getHistory($ticket['id']);

        $reviewModel = new Review();
        $review = $reviewModel->getByTicketId($ticket['id']);

        $technicians = [];
        if ($user['role'] === 'admin') {
            $userModel = new User();
            $technicians = $userModel->getAllTechnicians();
        }

        $this->render('customer/view_ticket', [
            'pageTitle' => "Ticket {$ticket['ticket_code']} - FixMyDevice",
            'ticket' => $ticket,
            'comments' => $comments,
            'history' => $history,
            'review' => $review,
            'technicians' => $technicians,
            'user' => $user
        ]);
    }

    public function invoice($ticketCode = null)
    {
        $this->requireLogin();
        if (empty($ticketCode)) {
            $this->redirect('ticket/dashboard');
        }

        $ticketModel = new Ticket();
        $ticket = $ticketModel->findByCode($ticketCode);

        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            $this->redirect('ticket/dashboard');
        }

        $user = Session::user();
        if ($user['role'] === 'customer' && $ticket['user_id'] != $user['id']) {
            Session::setFlash('error', 'Unauthorized access to this invoice.');
            $this->redirect('ticket/dashboard');
        }

        $history = $ticketModel->getHistory($ticket['id']);

        // Render standalone printable invoice without standard layout
        $this->render('customer/invoice', [
            'pageTitle' => "Repair Invoice & Job Sheet - {$ticket['ticket_code']}",
            'ticket' => $ticket,
            'history' => $history,
            'user' => $user
        ], false);
    }

    public function comment()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $ticketId = intval($_POST['ticket_id'] ?? 0);
            $ticketCode = $_POST['ticket_code'] ?? '';
            $commentText = trim($_POST['comment'] ?? '');
            
            // Only staff (technicians and admins) are permitted to post internal notes
            $user = Session::user();
            $isStaff = in_array($user['role'], ['technician', 'admin']);
            $isInternal = ($isStaff && !empty($_POST['is_internal'])) ? 1 : 0;

            $ticketModel = new Ticket();
            $ticket = $ticketModel->findById($ticketId);

            if (!$ticket) {
                Session::setFlash('error', 'Invalid ticket.');
                $this->redirect('ticket/dashboard');
            }

            // Authorization: User must be ticket owner, assigned tech, or admin
            if ($user['role'] === 'customer' && $ticket['user_id'] != $user['id']) {
                Session::setFlash('error', 'Unauthorized operation.');
                $this->redirect('ticket/dashboard');
            }

            if (!empty($commentText)) {
                $commentModel = new TicketComment();
                $commentModel->create($ticketId, $user['id'], $commentText, $isInternal);
                $flashMsg = $isInternal ? 'Private internal note saved.' : 'Message posted to ticket thread.';
                Session::setFlash('success', $flashMsg);
            } else {
                Session::setFlash('error', 'Comment content cannot be empty.');
            }

            $this->redirect('ticket/view/' . $ticketCode);
        }
    }

    public function review()
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $ticketId = intval($_POST['ticket_id'] ?? 0);
            $ticketCode = $_POST['ticket_code'] ?? '';
            $rating = intval($_POST['rating'] ?? 5);
            $commentText = trim($_POST['comment'] ?? '');

            $ticketModel = new Ticket();
            $ticket = $ticketModel->findById($ticketId);

            if (!$ticket || $ticket['user_id'] != Session::user()['id']) {
                Session::setFlash('error', 'Unauthorized operation.');
                $this->redirect('ticket/dashboard');
            }

            if ($ticketId > 0 && $rating >= 1 && $rating <= 5) {
                $reviewModel = new Review();
                $reviewModel->create($ticketId, Session::user()['id'], $rating, $commentText);
                Session::setFlash('success', 'Thank you for your rating and service review!');
            }

            $this->redirect('ticket/view/' . $ticketCode);
        }
    }
}
