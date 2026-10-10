<?php
class CategoryModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll() {
        $this->db->query('SELECT * FROM categories ORDER BY name');
        return $this->db->resultSet();
    }

    public function findById($id) {
        $this->db->query('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function nameExists($name, $exceptId = null) {
        $sql = 'SELECT id FROM categories WHERE name = :name';
        if ($exceptId !== null) $sql .= ' AND id <> :id';
        $sql .= ' LIMIT 1';
        $this->db->query($sql);
        $this->db->bind(':name', $name);
        if ($exceptId !== null) $this->db->bind(':id', $exceptId);
        return $this->db->single() !== false;
    }

    public function create($name, $description) {
        $this->db->query('INSERT INTO categories (name, description) VALUES (:name, :desc)');
        $this->db->bind(':name', $name);
        $this->db->bind(':desc', $description);
        return $this->db->execute();
    }

    public function update($id, $name, $description) {
        $this->db->query('UPDATE categories SET name = :name, description = :desc WHERE id = :id');
        $this->db->bind(':name', $name);
        $this->db->bind(':desc', $description);
        $this->db->bind(':id',   $id);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM categories WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}