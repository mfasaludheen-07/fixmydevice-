<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Category.php';

class AdminController extends Controller
{
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->requireRole('admin');

        $ticketModel = new Ticket();
        $userModel = new User();
        $categoryModel = new Category();

        $filterStatus = $_GET['status'] ?? null;
        $filterCategory = $_GET['category'] ?? null;

        $tickets = $ticketModel->getAllTickets($filterStatus, $filterCategory);
        $technicians = $userModel->getAllTechnicians();
        $categories = $categoryModel->getAll();
        $stats = $ticketModel->getStats();
        $users = $userModel->getAllUsers();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Admin Control Center - FixMyDevice',
            'tickets' => $tickets,
            'technicians' => $technicians,
            'categories' => $categories,
            'stats' => $stats,
            'users' => $users,
            'filterStatus' => $filterStatus,
            'filterCategory' => $filterCategory
        ]);
    }

    public function assign()
    {
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $ticketId = intval($_POST['ticket_id'] ?? 0);
            $techId = intval($_POST['technician_id'] ?? 0);
            $ticketCode = trim($_POST['ticket_code'] ?? '');

            if ($ticketId > 0 && $techId > 0) {
                $ticketModel = new Ticket();
                $ticketModel->assignTechnician($ticketId, $techId, Session::user()['id']);
                Session::setFlash('success', 'Technician assigned successfully.');
            } else {
                Session::setFlash('error', 'Please select a valid technician.');
            }

            if (!empty($ticketCode)) {
                $this->redirect('ticket/view/' . $ticketCode);
            } else {
                $this->redirect('admin/dashboard');
            }
        }
    }

    public function create_technician()
    {
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (empty($name) || empty($email) || empty($password)) {
                Session::setFlash('error', 'Technician name, email, and password are required.');
                $this->redirect('admin/dashboard');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::setFlash('error', 'Invalid email address format.');
                $this->redirect('admin/dashboard');
            }

            if (strlen($password) < 6) {
                Session::setFlash('error', 'Password must be at least 6 characters long.');
                $this->redirect('admin/dashboard');
            }

            $userModel = new User();
            if ($userModel->findByEmail($email)) {
                Session::setFlash('error', 'An account with this email address already exists.');
                $this->redirect('admin/dashboard');
            }

            // User::create internally hashes password with password_hash(..., PASSWORD_DEFAULT)
            $userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => $phone ?: null,
                'address' => $address ?: null,
                'role' => 'technician'
            ]);

            Session::setFlash('success', "Technician account '{$name}' registered successfully.");
            $this->redirect('admin/dashboard');
        }
    }

    public function change_role()
    {
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $userId = intval($_POST['user_id'] ?? 0);
            $newRole = trim($_POST['role'] ?? '');

            if ($userId <= 0 || !in_array($newRole, ['customer', 'technician', 'admin'])) {
                Session::setFlash('error', 'Invalid user or role selection.');
                $this->redirect('admin/dashboard');
            }

            // Prevent an admin from demoting their own logged-in account
            if ($userId === Session::user()['id'] && $newRole !== 'admin') {
                Session::setFlash('error', 'You cannot change your own admin account role.');
                $this->redirect('admin/dashboard');
            }

            $userModel = new User();
            $userModel->updateRole($userId, $newRole);

            Session::setFlash('success', "User role updated successfully to " . ucfirst($newRole) . ".");
            $this->redirect('admin/dashboard');
        }
    }

    public function create_category()
    {
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $name = trim($_POST['name'] ?? '');
            $icon = trim($_POST['icon'] ?? 'tv');
            $description = trim($_POST['description'] ?? '');

            if (!empty($name)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                $categoryModel = new Category();
                $categoryModel->create($name, $slug, $icon, $description);
                Session::setFlash('success', "Hardware category '{$name}' created.");
            } else {
                Session::setFlash('error', 'Category name is required.');
            }

            $this->redirect('admin/dashboard');
        }
    }

    public function export_csv()
    {
        $this->requireRole('admin');

        $ticketModel = new Ticket();
        $tickets = $ticketModel->getAllTickets();

        $filename = "FixMyDevice_Tickets_" . date('Y-m-d_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Output BOM for Excel UTF-8 compatibility
        fputs($output, "\xEF\xBB\xBF");

        // Header row
        fputcsv($output, [
            'Ticket Code',
            'Category',
            'Device Name',
            'Brand',
            'Customer Name',
            'Assigned Technician',
            'Status',
            'Priority',
            'Est. Cost (INR)',
            'Submitted Date'
        ]);

        foreach ($tickets as $t) {
            fputcsv($output, [
                $t['ticket_code'],
                $t['category_name'],
                $t['device_name'],
                $t['brand'],
                $t['customer_name'],
                $t['technician_name'] ?: 'Unassigned',
                strtoupper(str_replace('_', ' ', $t['status'])),
                strtoupper($t['priority']),
                number_format($t['estimated_cost'], 2),
                $t['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
