<?php

class User {
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
      public function getDB() {
        return $this->db;
    }
    public function tableExists($tableName) {
        $result = $this->db->query("SHOW TABLES LIKE '$tableName'");
        return $result->num_rows > 0;
    }
    /**
     * Register a new user
     * @param string $name
     * @param string $email
     * @param string $password
     * @return bool True if registration succeeded, false otherwise
     */
    public function register($name, $email, $password) {
        // Check if email exists
        if ($this->emailExists($email)) {
            return false;
        }

        // Start transaction
        $this->db->begin_transaction();

        try {
            // Insert user with plain text password 
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $password);
            $stmt->execute();
            $userId = $this->db->insert_id;
            $stmt->close();

            // Create user's tasks table
            $this->createUserTasksTable($userId);

            // Commit transaction
            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    private function createUserTasksTable($userId) {
        $tableName = "user_" . $userId . "_tasks";
        
        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
            `task_id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `due_date` DATETIME,
            `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
            `status` ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        return $this->db->query($sql);
    }

    public function verifyCredentials($email, $password) {
        $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Compare plain text passwords (INSECURE)
            if ($password === $user['password']) {
                unset($user['password']); // Remove password before returning
                return $user;
            }
        }
        return false;
    }

    /**
     * Check if email already exists in database
     * @param string $email
     * @return bool True if email exists, false otherwise
     */
    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Compare plain text passwords 
            if ($password === $user['password']) {
                unset($user['password']);
                return $user;
            }
        }
        
        return false;
    }

    public function updateAvatar($userId, $avatarPath) {
        $stmt = $this->db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatarPath, $userId);
        return $stmt->execute();
    }

    public function getAvatar($userId) {
        $stmt = $this->db->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['avatar'];
    }

    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT id, name, email, avatar, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return false;
    }

    // Close database connection when object is destroyed
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}