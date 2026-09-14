<?php

require_once __DIR__ . '/../../config/database.php';

abstract class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }

    protected function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    protected function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
