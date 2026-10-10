<?php
class UserModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function findByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email LIMIT 1');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    public function findById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function emailExists($email) {
        $this->db->query('SELECT id FROM users WHERE email = :email LIMIT 1');
        $this->db->bind(':email', $email);
        return $this->db->single() !== false;
    }

    public function create($data) {
        $this->db->query('INSERT INTO users (full_name, email, password, role)
                          VALUES (:full_name, :email, :password, :role)');
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':email',     $data['email']);
        $this->db->bind(':password',  $data['password']);
        $this->db->bind(':role',      $data['role'] ?? 'user');
        return $this->db->execute();
    }

    public function getAll() {
        $this->db->query('SELECT id, full_name, email, role, created_at
                          FROM users ORDER BY created_at DESC');
        return $this->db->resultSet();
    }

    public function updateRole($id, $role) {
        $this->db->query('UPDATE users SET role = :role WHERE id = :id');
        $this->db->bind(':role', $role);
        $this->db->bind(':id',   $id);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}