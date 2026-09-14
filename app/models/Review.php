<?php

require_once __DIR__ . '/../core/Model.php';

class Review extends Model
{
    public function getByTicketId($ticketId)
    {
        return $this->fetch("SELECT r.*, u.name as customer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ?", [$ticketId]);
    }

    public function create($ticketId, $userId, $rating, $comment)
    {
        $this->query(
            "INSERT INTO reviews (ticket_id, user_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)",
            [$ticketId, $userId, $rating, $comment]
        );
        return $this->lastInsertId();
    }

    public function getRecentReviews($limit = 6)
    {
        $sql = "SELECT r.*, u.name as customer_name, t.device_name, t.brand, c.name as category_name
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN tickets t ON r.ticket_id = t.id
                JOIN categories c ON t.category_id = c.id
                ORDER BY r.created_at DESC
                LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }
}
