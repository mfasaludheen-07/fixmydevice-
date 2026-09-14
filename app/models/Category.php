<?php

require_once __DIR__ . '/../core/Model.php';

class Category extends Model
{
    public function getAll()
    {
        return $this->fetchAll("SELECT * FROM categories ORDER BY name ASC");
    }

    public function findById($id)
    {
        return $this->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
    }

    public function create($name, $slug, $icon, $description)
    {
        $this->query(
            "INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)",
            [$name, $slug, $icon, $description]
        );
        return $this->lastInsertId();
    }
}
