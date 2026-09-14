<?php

require_once __DIR__ . '/../core/Model.php';

class TicketComment extends Model
{
    public function getByTicketId($ticketId)
    {
        $sql = "SELECT tc.*, u.name as user_name, u.role as user_role
                FROM ticket_comments tc
                JOIN users u ON tc.user_id = u.id
                WHERE tc.ticket_id = ?
                ORDER BY tc.created_at ASC";
        return $this->fetchAll($sql, [$ticketId]);
    }

    public function create($ticketId, $userId, $comment, $isInternal = 0)
    {
        $this->query(
            "INSERT INTO ticket_comments (ticket_id, user_id, comment, is_internal) VALUES (?, ?, ?, ?)",
            [$ticketId, $userId, $comment, $isInternal]
        );
        return $this->lastInsertId();
    }
}
