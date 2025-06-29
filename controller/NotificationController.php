<?php
require_once BASE_PATH . 'model/Notification.php';

class NotificationController {
    public static function markAsRead() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationId = $_POST['notification_id'] ?? null;

            if (!$notificationId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing notification ID']);
                exit();
            }

            $notificationModel = new Notification();
            
            if ($notificationModel->markAsRead($notificationId, $_SESSION['user']['id'])) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to mark notification as read']);
            }
            exit();
        }
    }

    public static function markAllAsRead() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationModel = new Notification();
            
            if ($notificationModel->markAllAsRead($_SESSION['user']['id'])) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to mark all notifications as read']);
            }
            exit();
        }
    }

    public static function getUnreadCount() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $notificationModel = new Notification();
        $count = $notificationModel->getUnreadCount($_SESSION['user']['id']);
        
        echo json_encode(['count' => $count]);
        exit();
    }

    public static function delete() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationId = $_POST['notification_id'] ?? null;

            if (!$notificationId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing notification ID']);
                exit();
            }

            $notificationModel = new Notification();
            
            if ($notificationModel->delete($notificationId, $_SESSION['user']['id'])) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete notification']);
            }
            exit();
        }
    }
} 