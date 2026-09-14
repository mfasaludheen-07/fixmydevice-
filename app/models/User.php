<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    public function findByEmail($email)
    {
        return $this->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function findById($id)
    {
        return $this->fetch("SELECT id, name, email, phone, address, role, created_at FROM users WHERE id = ?", [$id]);
    }

    public function create($data)
    {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $data['role'] ?? 'customer';

        $this->query(
            "INSERT INTO users (name, email, password_hash, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['email'],
                $hash,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $role
            ]
        );
        return $this->lastInsertId();
    }

    public function getAllTechnicians()
    {
        return $this->fetchAll("SELECT id, name, email, phone FROM users WHERE role = 'technician' ORDER BY name ASC");
    }

    public function getAllUsers()
    {
        return $this->fetchAll("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
    }

    public function verifyPassword($user, $password)
    {
        return password_verify($password, $user['password_hash']);
    }

    public function updateProfile($id, $data)
    {
        return $this->query(
            "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?",
            [$data['name'], $data['phone'] ?? null, $data['address'] ?? null, $id]
        );
    }

    public function updatePassword($id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->query(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$hash, $id]
        );
    }

    public function updateRole($id, $role)
    {
        if (!in_array($role, ['customer', 'technician', 'admin'])) {
            return false;
        }
        return $this->query("UPDATE users SET role = ? WHERE id = ?", [$role, $id]);
    }
}
