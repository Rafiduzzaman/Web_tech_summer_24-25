<?php
class Notification {
    private $db;

    public function __construct() {
        $this->connectDB();
    }

    private function connectDB() {
        $this->db = new mysqli('localhost', 'root', '', 'webtech');
        if ($this->db->connect_error) {
            die("Database connection failed: " . $this->db->connect_error);
        }
    }

    public function create($userId, $title, $message, $type = 'info') {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $userId, $title, $message, $type);
        return $stmt->execute();
    }

    public function getUnread($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll($userId, $limit = 50) {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Alias for getAll() to maintain compatibility
    public function getUserNotifications($userId, $limit = 5) {
        return $this->getAll($userId, $limit);
    }

    public function markAsRead($notificationId, $userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        return $stmt->execute();
    }

    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function delete($notificationId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        return $stmt->execute();
    }

    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }

    public function createTaskNotification($userId, $taskTitle, $action) {
        $title = "Task Update";
        $message = "Task '$taskTitle' has been $action.";
        return $this->create($userId, $title, $message, 'info');
    }

    public function createDueDateNotification($userId, $taskTitle, $dueDate) {
        $title = "Task Due Soon";
        $message = "Task '$taskTitle' is due on " . date('M d, Y', strtotime($dueDate));
        return $this->create($userId, $title, $message, 'warning');
    }

    public function createOverdueNotification($userId, $taskTitle) {
        $title = "Task Overdue";
        $message = "Task '$taskTitle' is overdue!";
        return $this->create($userId, $title, $message, 'error');
    }

    public function createSharedTaskNotification($userId, $taskTitle, $sharedBy) {
        $title = "Task Shared";
        $message = "$sharedBy has shared task '$taskTitle' with you.";
        return $this->create($userId, $title, $message, 'info');
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
} 