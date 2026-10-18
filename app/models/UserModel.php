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

    // Danh sách tài khoản có thể được phân công xử lý sự cố (officer + admin), đang hoạt động
    public function getOfficers() {
        $this->db->query("SELECT id, full_name, email, role
                          FROM users
                          WHERE role IN ('officer', 'admin') AND account_status = 'active'
                          ORDER BY FIELD(role, 'officer', 'admin'), full_name ASC");
        return $this->db->resultSet();
    }

    public function updateRole($id, $role) {
        $this->db->query('UPDATE users SET role = :role WHERE id = :id');
        $this->db->bind(':role', $role);
        $this->db->bind(':id',   $id);
        return $this->db->execute();
    }

    public function updateProfile($id, $fullName, $phone = '') {
        $this->db->query('UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id');
        $this->db->bind(':full_name', $fullName);
        $this->db->bind(':phone',     $phone);
        $this->db->bind(':id',        $id);
        return $this->db->execute();
    }

    public function updatePassword($id, $hashedPassword) {
        $this->db->query('UPDATE users SET password = :password WHERE id = :id');
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':id',       $id);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}