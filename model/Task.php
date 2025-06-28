<?php
class Task {
    private $db;
    private $userId;
    private $tableName;

    public function __construct($userId) {
        $this->connectDB();
        $this->userId = $userId;
        $this->tableName = "user_" . $userId . "_tasks";
    }

    private function connectDB() {
        $this->db = new mysqli('localhost', 'root', '', 'webtech');
        if ($this->db->connect_error) {
            die("Database connection failed: " . $this->db->connect_error);
        }
    }

    public function create($title, $description, $dueDate, $priority, $status) {
        $stmt = $this->db->prepare("INSERT INTO `{$this->tableName}` 
            (title, description, due_date, priority, status) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $description, $dueDate, $priority, $status);
        return $stmt->execute();
    }

    public function getAll() {
        $result = $this->db->query("SELECT * FROM `{$this->tableName}` ORDER BY due_date ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($taskId) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->tableName}` WHERE task_id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($taskId, $title, $description, $dueDate, $priority, $status) {
        $stmt = $this->db->prepare("UPDATE `{$this->tableName}` 
            SET title = ?, description = ?, due_date = ?, priority = ?, status = ? 
            WHERE task_id = ?");
        $stmt->bind_param("sssssi", $title, $description, $dueDate, $priority, $status, $taskId);
        return $stmt->execute();
    }

    public function delete($taskId) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->tableName}` WHERE task_id = ?");
        $stmt->bind_param("i", $taskId);
        return $stmt->execute();
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}