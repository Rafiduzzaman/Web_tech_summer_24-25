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

    /**
     * Register a new user
     * @param string $name
     * @param string $email
     * @param string $password
     * @return bool True if registration succeeded, false otherwise
     */
    public function register($name, $email, $password) {
        // Check if email already exists
        if ($this->emailExists($email)) {
            return false;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the insert statement
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
public function verifyCredentials($email, $password) {
        $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
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

    // Close database connection when object is destroyed
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }

    public function login($email, $password) {
    $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Return user data (excluding password)
            unset($user['password']);
            return $user;
        }
    }
    
    return false;
}
}