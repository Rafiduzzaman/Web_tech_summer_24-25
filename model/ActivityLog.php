<?php
class ActivityLog {
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

    public function log($userId, $action, $description = '', $ipAddress = null, $userAgent = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        if ($userAgent === null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        }

        $stmt = $this->db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $action, $description, $ipAddress, $userAgent);
        return $stmt->execute();
    }

    public function getLogs($filters = [], $limit = 100, $offset = 0) {
        $whereClause = "WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters['user_id'])) {
            $whereClause .= " AND user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['action'])) {
            $whereClause .= " AND action = ?";
            $params[] = $filters['action'];
            $types .= "s";
        }

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        if (!empty($filters['ip_address'])) {
            $whereClause .= " AND ip_address = ?";
            $params[] = $filters['ip_address'];
            $types .= "s";
        }

        $sql = "SELECT al.*, u.name as user_name, u.email as user_email 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                $whereClause 
                ORDER BY al.created_at DESC 
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getLogsByUser($userId, $limit = 50) {
        $stmt = $this->db->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Alias for getLogsByUser() to maintain compatibility
    public function getUserActivities($userId, $limit = 10) {
        return $this->getLogsByUser($userId, $limit);
    }

    public function getRecentLogs($limit = 20) {
        $stmt = $this->db->prepare("SELECT al.*, u.name as user_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getStats($filters = []) {
        $whereClause = "WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        $stats = [];

        // Total logs
        $sql = "SELECT COUNT(*) as total FROM activity_logs $whereClause";
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
        } else {
            $result = $this->db->query($sql)->fetch_assoc();
        }
        $stats['total'] = $result['total'];

        // Logs by action type
        $sql = "SELECT action, COUNT(*) as count FROM activity_logs $whereClause GROUP BY action ORDER BY count DESC";
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }
        $stats['by_action'] = $result->fetch_all(MYSQLI_ASSOC);

        // Logs by user
        $sql = "SELECT u.name, COUNT(*) as count FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id $whereClause GROUP BY al.user_id ORDER BY count DESC LIMIT 10";
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }
        $stats['by_user'] = $result->fetch_all(MYSQLI_ASSOC);

        return $stats;
    }

    public function exportLogs($filters = [], $format = 'csv') {
        $logs = $this->getLogs($filters, 10000); // Get up to 10,000 logs

        if ($format === 'csv') {
            $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'User', 'Action', 'Description', 'IP Address', 'Date']);

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['id'],
                    $log['user_name'] ?? 'Unknown',
                    $log['action'],
                    $log['description'],
                    $log['ip_address'],
                    $log['created_at']
                ]);
            }

            fclose($output);
            return true;
        }

        return false;
    }

    public function cleanupOldLogs($days = 90) {
        $stmt = $this->db->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->bind_param("i", $days);
        return $stmt->execute();
    }

    // Convenience methods for common actions
    public function logLogin($userId, $success = true) {
        $action = $success ? 'login_success' : 'login_failed';
        $description = $success ? 'User logged in successfully' : 'Failed login attempt';
        return $this->log($userId, $action, $description);
    }

    public function logLogout($userId) {
        return $this->log($userId, 'logout', 'User logged out');
    }

    public function logTaskCreate($userId, $taskTitle) {
        return $this->log($userId, 'task_create', "Created task: $taskTitle");
    }

    public function logTaskUpdate($userId, $taskTitle) {
        return $this->log($userId, 'task_update', "Updated task: $taskTitle");
    }

    public function logTaskDelete($userId, $taskTitle) {
        return $this->log($userId, 'task_delete', "Deleted task: $taskTitle");
    }

    public function logProfileUpdate($userId) {
        return $this->log($userId, 'profile_update', 'Updated profile information');
    }

    public function logPasswordChange($userId) {
        return $this->log($userId, 'password_change', 'Changed password');
    }

    public function logAdminAction($userId, $action, $description) {
        return $this->log($userId, "admin_$action", $description);
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
} 