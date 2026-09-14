<?php

require_once __DIR__ . '/../core/Model.php';

class Ticket extends Model
{
    public function generateTicketCode()
    {
        do {
            $code = 'TKT-' . date('Y') . '-' . rand(1000, 9999);
            $exists = $this->fetch("SELECT id FROM tickets WHERE ticket_code = ?", [$code]);
        } while ($exists);
        return $code;
    }

    public function create($data)
    {
        $code = $this->generateTicketCode();
        $this->query(
            "INSERT INTO tickets (ticket_code, user_id, category_id, device_name, brand, model_number, serial_number, warranty_status, issue_title, description, priority, preferred_date, attachment_url, location_lat, location_lng, location_address) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $code,
                $data['user_id'],
                $data['category_id'],
                $data['device_name'],
                $data['brand'],
                $data['model_number'] ?? null,
                $data['serial_number'] ?? null,
                $data['warranty_status'] ?? 'out_of_warranty',
                $data['issue_title'],
                $data['description'],
                $data['priority'] ?? 'medium',
                $data['preferred_date'] ?? null,
                $data['attachment_url'] ?? null,
                $data['location_lat'] ?? null,
                $data['location_lng'] ?? null,
                $data['location_address'] ?? null
            ]
        );

        $tktId = $this->lastInsertId();

        // Add history log
        $this->addHistory($tktId, $data['user_id'], null, 'pending', 'Ticket registered by customer.');

        return $code;
    }

    public function findByCode($code)
    {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, 
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
                       tech.name as technician_name, tech.phone as technician_phone, tech.email as technician_email
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                JOIN users u ON t.user_id = u.id
                LEFT JOIN users tech ON t.technician_id = tech.id
                WHERE t.ticket_code = ?";
        return $this->fetch($sql, [$code]);
    }

    public function findById($id)
    {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, 
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
                       tech.name as technician_name, tech.phone as technician_phone, tech.email as technician_email
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                JOIN users u ON t.user_id = u.id
                LEFT JOIN users tech ON t.technician_id = tech.id
                WHERE t.id = ?";
        return $this->fetch($sql, [$id]);
    }

    public function getTicketsByUser($userId)
    {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, tech.name as technician_name
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                LEFT JOIN users tech ON t.technician_id = tech.id
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    public function getTicketsByTechnician($techId)
    {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, u.name as customer_name, u.phone as customer_phone
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                JOIN users u ON t.user_id = u.id
                WHERE t.technician_id = ?
                ORDER BY t.created_at DESC";
        return $this->fetchAll($sql, [$techId]);
    }

    public function getAllTickets($filterStatus = null, $filterCategory = null)
    {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, u.name as customer_name, tech.name as technician_name
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                JOIN users u ON t.user_id = u.id
                LEFT JOIN users tech ON t.technician_id = tech.id
                WHERE 1=1";
        $params = [];

        if (!empty($filterStatus)) {
            $sql .= " AND t.status = ?";
            $params[] = $filterStatus;
        }

        if (!empty($filterCategory)) {
            $sql .= " AND t.category_id = ?";
            $params[] = $filterCategory;
        }

        $sql .= " ORDER BY t.created_at DESC";
        return $this->fetchAll($sql, $params);
    }

    public function updateStatus($ticketId, $newStatus, $changedByUserId, $note = '')
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return false;

        $oldStatus = $ticket['status'];

        $this->query("UPDATE tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$newStatus, $ticketId]);

        $this->addHistory($ticketId, $changedByUserId, $oldStatus, $newStatus, $note);
        return true;
    }

    public function assignTechnician($ticketId, $techId, $assignedByUserId)
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return false;

        $newStatus = ($ticket['status'] === 'pending') ? 'assigned' : $ticket['status'];

        $this->query("UPDATE tickets SET technician_id = ?, status = ? WHERE id = ?", [$techId, $newStatus, $ticketId]);

        $tech = $this->fetch("SELECT name FROM users WHERE id = ?", [$techId]);
        $techName = $tech['name'] ?? 'Technician';

        $this->addHistory($ticketId, $assignedByUserId, $ticket['status'], $newStatus, "Assigned repair ticket to {$techName}.");
        return true;
    }

    public function updateCost($ticketId, $cost, $updatedByUserId, $note = '')
    {
        $this->query("UPDATE tickets SET estimated_cost = ? WHERE id = ?", [$cost, $ticketId]);
        $this->addHistory($ticketId, $updatedByUserId, null, 'quote_updated', "Updated estimated repair cost to ₹" . number_format($cost, 2) . ". " . $note);
        return true;
    }

    public function claimTicket($ticketId, $techId)
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return false;

        $newStatus = ($ticket['status'] === 'pending') ? 'assigned' : $ticket['status'];

        $this->query("UPDATE tickets SET technician_id = ?, status = ? WHERE id = ?", [$techId, $newStatus, $ticketId]);

        $tech = $this->fetch("SELECT name FROM users WHERE id = ?", [$techId]);
        $techName = $tech['name'] ?? 'Technician';

        $this->addHistory($ticketId, $techId, $ticket['status'], $newStatus, "Ticket claimed by {$techName}.");
        return true;
    }

    public function getUnassignedTickets()
    {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, u.name as customer_name, u.phone as customer_phone
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                JOIN users u ON t.user_id = u.id
                WHERE t.technician_id IS NULL AND t.status NOT IN ('resolved', 'cancelled')
                ORDER BY FIELD(t.priority, 'urgent', 'high', 'medium', 'low'), t.created_at ASC";
        return $this->fetchAll($sql);
    }

    public function addHistory($ticketId, $changedByUserId, $fromStatus, $toStatus, $note = '')
    {
        $this->query(
            "INSERT INTO ticket_history (ticket_id, changed_by_user_id, status_from, status_to, note) VALUES (?, ?, ?, ?, ?)",
            [$ticketId, $changedByUserId, $fromStatus, $toStatus, $note]
        );
    }

    public function getHistory($ticketId)
    {
        $sql = "SELECT th.*, u.name as user_name, u.role as user_role
                FROM ticket_history th
                JOIN users u ON th.changed_by_user_id = u.id
                WHERE th.ticket_id = ?
                ORDER BY th.created_at ASC";
        return $this->fetchAll($sql, [$ticketId]);
    }

    public function getStats()
    {
        $total = $this->fetch("SELECT COUNT(*) as cnt FROM tickets")['cnt'];
        $pending = $this->fetch("SELECT COUNT(*) as cnt FROM tickets WHERE status = 'pending'")['cnt'];
        $inProgress = $this->fetch("SELECT COUNT(*) as cnt FROM tickets WHERE status IN ('assigned', 'in_diagnosis', 'awaiting_parts', 'repair_in_progress')")['cnt'];
        $ready = $this->fetch("SELECT COUNT(*) as cnt FROM tickets WHERE status = 'ready'")['cnt'];
        $resolved = $this->fetch("SELECT COUNT(*) as cnt FROM tickets WHERE status = 'resolved'")['cnt'];

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'ready' => $ready,
            'resolved' => $resolved
        ];
    }
}
