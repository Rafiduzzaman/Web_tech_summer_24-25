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
     * Hash password using PHP's password_hash function
     */
    private function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password against hash
     */
    private function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Register a new user
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $role (default: 'user')
     * @return bool|int User ID if registration succeeded, false otherwise
     */
    public function register($name, $email, $password, $role = 'user') {
        // Check if email exists
        if ($this->emailExists($email)) {
            return false;
        }

        // Start transaction
        $this->db->begin_transaction();

        try {
            // Hash the password
            $hashedPassword = $this->hashPassword($password);
            
            // Insert user with hashed password
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, email_verified, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
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
            `category` VARCHAR(100) DEFAULT 'General',
            `subtasks` JSON,
            `attachments` JSON,
            `shared_with` JSON,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        return $this->db->query($sql);
    }

    public function verifyCredentials($email, $password) {
        $stmt = $this->db->prepare("SELECT id, name, email, password, role, email_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password hash
            if ($this->verifyPassword($password, $user['password'])) {
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
        return $this->verifyCredentials($email, $password);
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
        $row = $result->fetch_assoc();
        return $row ? $row['avatar'] : null;
    }

    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT id, name, email, avatar, role, email_verified, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function updateProfile($userId, $name, $email) {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $userId);
        return $stmt->execute();
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        // First verify current password
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user || !$this->verifyPassword($currentPassword, $user['password'])) {
            return false;
        }

        // Update with new hashed password
        $hashedPassword = $this->hashPassword($newPassword);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }

    public function getAllUsers() {
        $result = $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateUserRole($userId, $role) {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $userId);
        return $stmt->execute();
    }

    public function deleteUser($userId) {
        // Start transaction
        $this->db->begin_transaction();
        
        try {
            // Delete user's tasks table
            $tableName = "user_" . $userId . "_tasks";
            $this->db->query("DROP TABLE IF EXISTS `$tableName`");
            
            // Delete user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function verifyEmail($userId) {
        $stmt = $this->db->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function generatePasswordResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();
        
        return $stmt->affected_rows > 0 ? $token : false;
    }

    public function resetPassword($token, $newPassword) {
        $hashedPassword = $this->hashPassword($newPassword);
        $stmt = $this->db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("ss", $hashedPassword, $token);
        $stmt->execute();
        
        return $stmt->affected_rows > 0;
    }

    // Close database connection when object is destroyed
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}