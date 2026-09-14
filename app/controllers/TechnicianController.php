<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Ticket.php';

class TechnicianController extends Controller
{
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->requireRole(['technician', 'admin']);
        $user = Session::user();

        $ticketModel = new Ticket();
        
        if ($user['role'] === 'technician') {
            $tickets = $ticketModel->getTicketsByTechnician($user['id']);
        } else {
            $tickets = $ticketModel->getAllTickets();
        }

        $unassignedTickets = $ticketModel->getUnassignedTickets();

        $this->render('technician/dashboard', [
            'pageTitle' => 'Technician Repair Desk - FixMyDevice',
            'tickets' => $tickets,
            'unassignedTickets' => $unassignedTickets,
            'user' => $user
        ]);
    }

    public function claim()
    {
        $this->requireRole(['technician', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $ticketId = intval($_POST['ticket_id'] ?? 0);
            $ticketCode = trim($_POST['ticket_code'] ?? '');
            $user = Session::user();

            $ticketModel = new Ticket();
            $ticket = $ticketModel->findById($ticketId);

            if (!$ticket) {
                Session::setFlash('error', 'Ticket not found.');
                $this->redirect('technician/dashboard');
            }

            if (!empty($ticket['technician_id']) && $ticket['technician_id'] != $user['id'] && $user['role'] !== 'admin') {
                Session::setFlash('error', 'This repair job is already assigned to another technician.');
                $this->redirect('technician/dashboard');
            }

            $ticketModel->claimTicket($ticketId, $user['id']);
            Session::setFlash('success', "Ticket {$ticket['ticket_code']} successfully claimed and added to your workbench!");

            if (!empty($ticketCode)) {
                $this->redirect('ticket/view/' . $ticketCode);
            } else {
                $this->redirect('technician/dashboard');
            }
        }
    }

    public function update_status()
    {
        $this->requireRole(['technician', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $ticketId = intval($_POST['ticket_id'] ?? 0);
            $ticketCode = $_POST['ticket_code'] ?? '';
            $status = $_POST['status'] ?? '';
            $note = trim($_POST['note'] ?? '');

            $ticketModel = new Ticket();
            $ticket = $ticketModel->findById($ticketId);

            if (!$ticket) {
                Session::setFlash('error', 'Ticket not found.');
                $this->redirect('technician/dashboard');
            }

            $user = Session::user();
            
            // If ticket is currently unassigned, auto-assign to the acting technician
            if ($user['role'] === 'technician' && empty($ticket['technician_id'])) {
                $ticketModel->claimTicket($ticketId, $user['id']);
            } elseif ($user['role'] === 'technician' && $ticket['technician_id'] != $user['id']) {
                Session::setFlash('error', 'Unauthorized operation. You can only update tickets assigned to you.');
                $this->redirect('technician/dashboard');
            }

            $allowedStatuses = ['pending', 'assigned', 'in_diagnosis', 'awaiting_parts', 'repair_in_progress', 'ready', 'resolved', 'cancelled'];
            if (in_array($status, $allowedStatuses)) {
                $ticketModel->updateStatus($ticketId, $status, $user['id'], $note);
                Session::setFlash('success', "Ticket status updated to " . strtoupper(str_replace('_', ' ', $status)) . ".");
            } else {
                Session::setFlash('error', 'Invalid status selected.');
            }

            if (!empty($ticketCode)) {
                $this->redirect('ticket/view/' . $ticketCode);
            } else {
                $this->redirect('technician/dashboard');
            }
        }
    }

    public function update_cost()
    {
        $this->requireRole(['technician', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $ticketId = intval($_POST['ticket_id'] ?? 0);
            $ticketCode = $_POST['ticket_code'] ?? '';
            $cost = floatval($_POST['cost'] ?? 0);
            $note = trim($_POST['note'] ?? '');

            $ticketModel = new Ticket();
            $ticket = $ticketModel->findById($ticketId);

            if (!$ticket) {
                Session::setFlash('error', 'Ticket not found.');
                $this->redirect('technician/dashboard');
            }

            $user = Session::user();
            if ($user['role'] === 'technician' && $ticket['technician_id'] != $user['id']) {
                Session::setFlash('error', 'Unauthorized operation.');
                $this->redirect('technician/dashboard');
            }

            if ($ticketId > 0 && $cost >= 0) {
                $ticketModel->updateCost($ticketId, $cost, $user['id'], $note);
                Session::setFlash('success', "Repair estimate updated to ₹" . number_format($cost, 2));
            } else {
                Session::setFlash('error', 'Invalid cost amount.');
            }

            if (!empty($ticketCode)) {
                $this->redirect('ticket/view/' . $ticketCode);
            } else {
                $this->redirect('technician/dashboard');
            }
        }
    }
}
