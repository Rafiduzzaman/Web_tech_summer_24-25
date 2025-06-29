<?php
class Task {
    private $db;
    private $userId;
    private $tableName;

    public function __construct($userId) {
        $this->connectDB();
        $this->userId = $userId;
        $this->tableName = "user_" . $userId . "_tasks";
        $this->ensureTableExists();
    }

    private function connectDB() {
        $this->db = new mysqli('localhost', 'root', '', 'webtech');
        if ($this->db->connect_error) {
            die("Database connection failed: " . $this->db->connect_error);
        }
    }

    private function ensureTableExists() {
        $check = $this->db->query("SHOW TABLES LIKE '" . $this->tableName . "'");
        if ($check->num_rows === 0) {
            $sql = "CREATE TABLE `{$this->tableName}` (
                task_id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                due_date DATETIME,
                priority ENUM('low','medium','high') DEFAULT 'medium',
                status ENUM('pending','in_progress','completed') DEFAULT 'pending',
                category VARCHAR(100) DEFAULT 'General',
                subtasks JSON,
                attachments JSON,
                shared_with JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->db->query($sql);
        }
    }

    public function create($title, $description, $dueDate, $priority, $status, $category = 'General', $subtasks = [], $attachments = [], $sharedWith = []) {
        $subtasksJson = json_encode($subtasks);
        $attachmentsJson = json_encode($attachments);
        $sharedWithJson = json_encode($sharedWith);
        
        $stmt = $this->db->prepare("INSERT INTO `{$this->tableName}` 
            (title, description, due_date, priority, status, category, subtasks, attachments, shared_with) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $title, $description, $dueDate, $priority, $status, $category, $subtasksJson, $attachmentsJson, $sharedWithJson);
        return $stmt->execute();
    }

    public function getAll($filters = []) {
        $whereClause = "WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($filters['priority'])) {
            $whereClause .= " AND priority = ?";
            $params[] = $filters['priority'];
            $types .= "s";
        }
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['category'])) {
            $whereClause .= " AND category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $whereClause .= " AND (title LIKE ? OR description LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        $orderBy = "ORDER BY ";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'priority':
                    $orderBy .= "FIELD(priority, 'high', 'medium', 'low'), due_date ASC";
                    break;
                case 'due_date':
                    $orderBy .= "due_date ASC";
                    break;
                case 'created':
                    $orderBy .= "created_at DESC";
                    break;
                default:
                    $orderBy .= "due_date ASC";
            }
        } else {
            $orderBy .= "due_date ASC";
        }
        
        $sql = "SELECT * FROM `{$this->tableName}` $whereClause $orderBy";
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $result = $this->db->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function getById($taskId) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->tableName}` WHERE task_id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($taskId, $title, $description, $dueDate, $priority, $status, $category = null, $subtasks = null, $attachments = null, $sharedWith = null) {
        $sql = "UPDATE `{$this->tableName}` SET title = ?, description = ?, due_date = ?, priority = ?, status = ?";
        $params = [$title, $description, $dueDate, $priority, $status];
        $types = "sssss";
        
        if ($category !== null) {
            $sql .= ", category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($subtasks !== null) {
            $sql .= ", subtasks = ?";
            $params[] = json_encode($subtasks);
            $types .= "s";
        }
        
        if ($attachments !== null) {
            $sql .= ", attachments = ?";
            $params[] = json_encode($attachments);
            $types .= "s";
        }
        
        if ($sharedWith !== null) {
            $sql .= ", shared_with = ?";
            $params[] = json_encode($sharedWith);
            $types .= "s";
        }
        
        $sql .= " WHERE task_id = ?";
        $params[] = $taskId;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    public function delete($taskId) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->tableName}` WHERE task_id = ?");
        $stmt->bind_param("i", $taskId);
        return $stmt->execute();
    }

    public function updateStatus($taskId, $status) {
        $stmt = $this->db->prepare("UPDATE `{$this->tableName}` SET status = ? WHERE task_id = ?");
        $stmt->bind_param("si", $status, $taskId);
        return $stmt->execute();
    }

    public function updatePriority($taskId, $priority) {
        $stmt = $this->db->prepare("UPDATE `{$this->tableName}` SET priority = ? WHERE task_id = ?");
        $stmt->bind_param("si", $priority, $taskId);
        return $stmt->execute();
    }

    public function getCategories() {
        $result = $this->db->query("SELECT DISTINCT category FROM `{$this->tableName}` WHERE category IS NOT NULL AND category != '' ORDER BY category");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getStats() {
        $stats = [];
        
        // Total tasks
        $result = $this->db->query("SELECT COUNT(*) as total FROM `{$this->tableName}`");
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Completed tasks
        $result = $this->db->query("SELECT COUNT(*) as completed FROM `{$this->tableName}` WHERE status = 'completed'");
        $stats['completed'] = $result->fetch_assoc()['completed'];
        
        // Pending tasks
        $result = $this->db->query("SELECT COUNT(*) as pending FROM `{$this->tableName}` WHERE status = 'pending'");
        $stats['pending'] = $result->fetch_assoc()['pending'];
        
        // In progress tasks
        $result = $this->db->query("SELECT COUNT(*) as in_progress FROM `{$this->tableName}` WHERE status = 'in_progress'");
        $stats['in_progress'] = $result->fetch_assoc()['in_progress'];
        
        // Overdue tasks
        $result = $this->db->query("SELECT COUNT(*) as overdue FROM `{$this->tableName}` WHERE due_date < NOW() AND status != 'completed'");
        $stats['overdue'] = $result->fetch_assoc()['overdue'];
        
        // High priority tasks
        $result = $this->db->query("SELECT COUNT(*) as `high_priority` FROM `{$this->tableName}` WHERE priority = 'high' AND status != 'completed'");
        $stats['high_priority'] = $result->fetch_assoc()['high_priority'];
        
        // Calculate completion percentage
        $stats['completion_percentage'] = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0;
        
        return $stats;
    }

    // Alias for getStats() to maintain compatibility
    public function getUserStats($userId = null) {
        return $this->getStats();
    }

    public function getRecentTasks($userId = null, $limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->tableName}` ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getUpcomingTasks($userId = null, $limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->tableName}` WHERE due_date >= NOW() AND status != 'completed' ORDER BY due_date ASC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function searchTasks($query) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->tableName}` WHERE title LIKE ? OR description LIKE ? ORDER BY due_date ASC");
        $searchTerm = "%$query%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addSubtask($taskId, $subtask) {
        $task = $this->getById($taskId);
        if (!$task) return false;
        
        $subtasks = json_decode($task['subtasks'] ?? '[]', true);
        $subtasks[] = [
            'id' => uniqid(),
            'text' => $subtask,
            'completed' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($taskId, $task['title'], $task['description'], $task['due_date'], $task['priority'], $task['status'], $task['category'], $subtasks);
    }

    public function toggleSubtask($taskId, $subtaskId) {
        $task = $this->getById($taskId);
        if (!$task) return false;
        
        $subtasks = json_decode($task['subtasks'] ?? '[]', true);
        foreach ($subtasks as &$subtask) {
            if ($subtask['id'] === $subtaskId) {
                $subtask['completed'] = !$subtask['completed'];
                break;
            }
        }
        
        return $this->update($taskId, $task['title'], $task['description'], $task['due_date'], $task['priority'], $task['status'], $task['category'], $subtasks);
    }

    public function addAttachment($taskId, $attachment) {
        $task = $this->getById($taskId);
        if (!$task) return false;
        
        $attachments = json_decode($task['attachments'] ?? '[]', true);
        $attachments[] = [
            'id' => uniqid(),
            'name' => $attachment['name'],
            'path' => $attachment['path'],
            'size' => $attachment['size'],
            'type' => $attachment['type'],
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($taskId, $task['title'], $task['description'], $task['due_date'], $task['priority'], $task['status'], $task['category'], null, $attachments);
    }

    public function removeAttachment($taskId, $attachmentId) {
        $task = $this->getById($taskId);
        if (!$task) return false;
        
        $attachments = json_decode($task['attachments'] ?? '[]', true);
        $attachments = array_filter($attachments, function($attachment) use ($attachmentId) {
            return $attachment['id'] !== $attachmentId;
        });
        
        return $this->update($taskId, $task['title'], $task['description'], $task['due_date'], $task['priority'], $task['status'], $task['category'], null, array_values($attachments));
    }

    public function shareTask($taskId, $userEmail, $permissions = ['view']) {
        $task = $this->getById($taskId);
        if (!$task) return false;
        
        $sharedWith = json_decode($task['shared_with'] ?? '[]', true);
        $sharedWith[] = [
            'email' => $userEmail,
            'permissions' => $permissions,
            'shared_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($taskId, $task['title'], $task['description'], $task['due_date'], $task['priority'], $task['status'], $task['category'], null, null, $sharedWith);
    }

    public function getSharedTasks($userEmail) {
        $result = $this->db->query("SELECT * FROM `{$this->tableName}` WHERE JSON_CONTAINS(shared_with, '\"$userEmail\"', '$.email')");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}