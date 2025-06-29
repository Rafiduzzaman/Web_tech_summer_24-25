<?php
// Database Migration Script
// Run this once to update your existing database structure

$db = new mysqli('localhost', 'root', '', 'webtech');

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

echo "Starting database migration...\n";

// Update users table
$alterUsersTable = "
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS role ENUM('admin', 'editor', 'user') DEFAULT 'user' AFTER password,
ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) DEFAULT 0 AFTER role,
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL AFTER email_verified,
ADD COLUMN IF NOT EXISTS reset_expires DATETIME NULL AFTER reset_token,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reset_expires,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at
";

if ($db->query($alterUsersTable)) {
    echo "✓ Users table updated successfully\n";
} else {
    echo "✗ Error updating users table: " . $db->error . "\n";
}

// Create notifications table
$createNotificationsTable = "
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)
";

if ($db->query($createNotificationsTable)) {
    echo "✓ Notifications table created successfully\n";
} else {
    echo "✗ Error creating notifications table: " . $db->error . "\n";
}

// Create activity_logs table
$createActivityLogsTable = "
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)
";

if ($db->query($createActivityLogsTable)) {
    echo "✓ Activity logs table created successfully\n";
} else {
    echo "✗ Error creating activity logs table: " . $db->error . "\n";
}

// Create contact_submissions table
$createContactTable = "
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    status ENUM('pending', 'read', 'replied') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
";

if ($db->query($createContactTable)) {
    echo "✓ Contact submissions table created successfully\n";
} else {
    echo "✗ Error creating contact submissions table: " . $db->error . "\n";
}

// Create system_settings table
$createSettingsTable = "
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
";

if ($db->query($createSettingsTable)) {
    echo "✓ System settings table created successfully\n";
    
    // Insert default settings
    $defaultSettings = [
        ['site_name', 'TaskMaster', 'Website name'],
        ['site_description', 'Efficient task management system', 'Website description'],
        ['max_file_size', '25', 'Maximum file upload size in MB'],
        ['allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx', 'Allowed file types for uploads'],
        ['email_notifications', '1', 'Enable email notifications'],
        ['maintenance_mode', '0', 'Maintenance mode status']
    ];
    
    foreach ($defaultSettings as $setting) {
        $stmt = $db->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        $key = $setting[0];
        $value = $setting[1];
        $description = $setting[2];
        $stmt->bind_param("sss", $key, $value, $description);
        $stmt->execute();
    }
    echo "✓ Default settings inserted\n";
} else {
    echo "✗ Error creating system settings table: " . $db->error . "\n";
}

// Update existing user tasks tables to include new fields
$result = $db->query("SHOW TABLES LIKE 'user_%_tasks'");
while ($row = $result->fetch_array()) {
    $tableName = $row[0];
    
    $alterTaskTable = "
    ALTER TABLE `$tableName` 
    ADD COLUMN IF NOT EXISTS category VARCHAR(100) DEFAULT 'General' AFTER status,
    ADD COLUMN IF NOT EXISTS subtasks JSON AFTER category,
    ADD COLUMN IF NOT EXISTS attachments JSON AFTER subtasks,
    ADD COLUMN IF NOT EXISTS shared_with JSON AFTER attachments,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER shared_with
    ";
    
    if ($db->query($alterTaskTable)) {
        echo "✓ Updated task table: $tableName\n";
    } else {
        echo "✗ Error updating task table $tableName: " . $db->error . "\n";
    }
}

// Create admin user if not exists
$adminEmail = 'admin@taskmaster.com';
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$adminName = 'Administrator';

$checkAdmin = $db->prepare("SELECT id FROM users WHERE email = ?");
$checkAdmin->bind_param("s", $adminEmail);
$checkAdmin->execute();
$result = $checkAdmin->get_result();

if ($result->num_rows === 0) {
    $createAdmin = $db->prepare("INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, 'admin', 1)");
    $createAdmin->bind_param("sss", $adminName, $adminEmail, $adminPassword);
    $createAdmin->execute();
    echo "✓ Admin user created (email: admin@taskmaster.com, password: admin123)\n";
} else {
    echo "✓ Admin user already exists\n";
}

echo "\nDatabase migration completed successfully!\n";
echo "You can now delete this file for security.\n";

$db->close();
?> 