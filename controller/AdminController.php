<?php
require_once BASE_PATH . 'model/User.php';
require_once BASE_PATH . 'model/ActivityLog.php';
require_once BASE_PATH . 'model/Notification.php';

class AdminController {
    public static function checkAdminAccess() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }
    }

    public static function dashboard() {
        self::checkAdminAccess();
        
        $userModel = new User();
        $activityLog = new ActivityLog();
        
        // Get system statistics
        $totalUsers = count($userModel->getAllUsers());
        $recentLogs = $activityLog->getRecentLogs(10);
        $activityStats = $activityLog->getStats();
        
        require BASE_PATH . 'view/admin/dashboard.php';
    }

    public static function userManagement() {
        self::checkAdminAccess();
        
        $userModel = new User();
        $users = $userModel->getAllUsers();
        
        require BASE_PATH . 'view/admin/users.php';
    }

    public static function updateUserRole() {
        self::checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $role = $_POST['role'] ?? null;
            
            if (!$userId || !$role) {
                header("Location: " . BASE_URL . "?page=admin-users&error=1");
                exit();
            }
            
            $userModel = new User();
            $activityLog = new ActivityLog();
            
            if ($userModel->updateUserRole($userId, $role)) {
                $activityLog->logAdminAction($_SESSION['user']['id'], 'update_user_role', "Updated user $userId role to $role");
                header("Location: " . BASE_URL . "?page=admin-users&success=1");
            } else {
                header("Location: " . BASE_URL . "?page=admin-users&error=1");
            }
            exit();
        }
    }

    public static function deleteUser() {
        self::checkAdminAccess();
        
        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            header("Location: " . BASE_URL . "?page=admin-users&error=1");
            exit();
        }
        
        $userModel = new User();
        $activityLog = new ActivityLog();
        
        if ($userModel->deleteUser($userId)) {
            $activityLog->logAdminAction($_SESSION['user']['id'], 'delete_user', "Deleted user $userId");
            header("Location: " . BASE_URL . "?page=admin-users&success=1");
        } else {
            header("Location: " . BASE_URL . "?page=admin-users&error=1");
        }
        exit();
    }

    public static function activityLogs() {
        self::checkAdminAccess();
        
        $activityLog = new ActivityLog();
        
        // Handle filters
        $filters = [];
        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        if (!empty($_GET['ip_address'])) $filters['ip_address'] = $_GET['ip_address'];
        
        $page = $_GET['page'] ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $logs = $activityLog->getLogs($filters, $limit, $offset);
        $stats = $activityLog->getStats($filters);
        
        require BASE_PATH . 'view/admin/activity_logs.php';
    }

    public static function exportLogs() {
        self::checkAdminAccess();
        
        $activityLog = new ActivityLog();
        
        $filters = [];
        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        
        $format = $_GET['format'] ?? 'csv';
        $activityLog->exportLogs($filters, $format);
    }

    public static function systemSettings() {
        self::checkAdminAccess();
        
        $db = new mysqli('localhost', 'root', '', 'webtech');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = [
                'site_name' => $_POST['site_name'] ?? '',
                'site_description' => $_POST['site_description'] ?? '',
                'max_file_size' => $_POST['max_file_size'] ?? '25',
                'allowed_file_types' => $_POST['allowed_file_types'] ?? '',
                'email_notifications' => $_POST['email_notifications'] ?? '1',
                'maintenance_mode' => $_POST['maintenance_mode'] ?? '0'
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->bind_param("sss", $key, $value, $value);
                $stmt->execute();
            }
            
            header("Location: " . BASE_URL . "?page=admin-settings&success=1");
            exit();
        }
        
        // Get current settings
        $result = $db->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        require BASE_PATH . 'view/admin/settings.php';
    }

    public static function getSetting($key, $default = '') {
        $db = new mysqli('localhost', 'root', '', 'webtech');
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['setting_value'];
        }
        
        return $default;
    }

    public static function contactSubmissions() {
        self::checkAdminAccess();
        
        $db = new mysqli('localhost', 'root', '', 'webtech');
        
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $result = $db->query("SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $submissions = $result->fetch_all(MYSQLI_ASSOC);
        
        $totalResult = $db->query("SELECT COUNT(*) as total FROM contact_submissions");
        $total = $totalResult->fetch_assoc()['total'];
        
        require BASE_PATH . 'view/admin/contact_submissions.php';
    }

    public static function updateSubmissionStatus() {
        self::checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submissionId = $_POST['submission_id'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$submissionId || !$status) {
                header("Location: " . BASE_URL . "?page=admin-contact&error=1");
                exit();
            }
            
            $db = new mysqli('localhost', 'root', '', 'webtech');
            $stmt = $db->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $submissionId);
            
            if ($stmt->execute()) {
                header("Location: " . BASE_URL . "?page=admin-contact&success=1");
            } else {
                header("Location: " . BASE_URL . "?page=admin-contact&error=1");
            }
            exit();
        }
    }

    public static function deleteSubmission() {
        self::checkAdminAccess();
        
        $submissionId = $_GET['id'] ?? null;
        if (!$submissionId) {
            header("Location: " . BASE_URL . "?page=admin-contact&error=1");
            exit();
        }
        
        $db = new mysqli('localhost', 'root', '', 'webtech');
        $stmt = $db->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->bind_param("i", $submissionId);
        
        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "?page=admin-contact&success=1");
        } else {
            header("Location: " . BASE_URL . "?page=admin-contact&error=1");
        }
        exit();
    }

    public static function notifications() {
        self::checkAdminAccess();
        
        $notificationModel = new Notification();
        
        $page = $_GET['page'] ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $db = new mysqli('localhost', 'root', '', 'webtech');
        $result = $db->query("SELECT n.*, u.name as user_name FROM notifications n LEFT JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC LIMIT $limit OFFSET $offset");
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        
        $totalResult = $db->query("SELECT COUNT(*) as total FROM notifications");
        $total = $totalResult->fetch_assoc()['total'];
        
        require BASE_PATH . 'view/admin/notifications.php';
    }

    public static function deleteNotification() {
        self::checkAdminAccess();
        
        $notificationId = $_GET['id'] ?? null;
        if (!$notificationId) {
            header("Location: " . BASE_URL . "?page=admin-notifications&error=1");
            exit();
        }
        
        $notificationModel = new Notification();
        
        if ($notificationModel->delete($notificationId, null)) {
            header("Location: " . BASE_URL . "?page=admin-notifications&success=1");
        } else {
            header("Location: " . BASE_URL . "?page=admin-notifications&error=1");
        }
        exit();
    }

    public static function cleanupOldData() {
        self::checkAdminAccess();
        
        $activityLog = new ActivityLog();
        
        if ($activityLog->cleanupOldLogs(90)) {
            header("Location: " . BASE_URL . "?page=admin-dashboard&success=1");
        } else {
            header("Location: " . BASE_URL . "?page=admin-dashboard&error=1");
        }
        exit();
    }
} 