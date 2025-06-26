<?php
class Profile {
    private $db;
    private $userId;

    public function __construct($userId) {
        $this->connectDB();
        $this->userId = $userId;
    }

    private function connectDB() {
        $this->db = new mysqli('localhost', 'root', '', 'webtech');
        if ($this->db->connect_error) {
            die("Database connection failed: " . $this->db->connect_error);
        }
    }

    /**
     * Get user profile data
     */
    public function getProfile() {
        $stmt = $this->db->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Update profile information
     */
    public function updateProfile($name, $email) {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $this->userId);
        return $stmt->execute();
    }

    /**
     * Change user password
     */
    public function changePassword($currentPassword, $newPassword) {
        // First verify current password
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $this->userId);
                return $updateStmt->execute();
            }
        }
        return false;
    }

    /**
     * Update profile picture
     */
    public function updateAvatar($avatarPath) {
        $stmt = $this->db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatarPath, $this->userId);
        return $stmt->execute();
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}