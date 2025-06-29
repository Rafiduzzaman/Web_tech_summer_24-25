<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '?page=login');
    exit();
}

require_once BASE_PATH . 'model/Notification.php';
$notificationModel = new Notification();
$notifications = $notificationModel->getAll($_SESSION['user']['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - TaskMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .notification-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .notification-item {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .notification-item.unread {
            border-left-color: #007bff;
            background-color: #f8f9ff;
        }
        .notification-item.info {
            border-left-color: #17a2b8;
        }
        .notification-item.success {
            border-left-color: #28a745;
        }
        .notification-item.warning {
            border-left-color: #ffc107;
        }
        .notification-item.error {
            border-left-color: #dc3545;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .notification-title {
            font-weight: 600;
            margin: 0;
            color: #333;
        }
        .notification-time {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .notification-message {
            color: #666;
            margin: 0;
            line-height: 1.5;
        }
        .notification-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="notification-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell me-2"></i>Notifications</h2>
                <div>
                    <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                        <i class="fas fa-check-double me-1"></i>Mark All as Read
                    </button>
                    <a href="<?= BASE_URL ?>?page=dashboard" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h4>No notifications yet</h4>
                    <p>You're all caught up! New notifications will appear here.</p>
                </div>
            <?php else: ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?> <?= $notification['type'] ?>" 
                             data-id="<?= $notification['id'] ?>">
                            <div class="notification-header">
                                <h6 class="notification-title"><?= htmlspecialchars($notification['title']) ?></h6>
                                <span class="notification-time">
                                    <?= date('M d, Y g:i A', strtotime($notification['created_at'])) ?>
                                </span>
                            </div>
                            <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                            <div class="notification-actions">
                                <?php if (!$notification['is_read']): ?>
                                    <button class="btn btn-primary btn-sm" onclick="markAsRead(<?= $notification['id'] ?>)">
                                        <i class="fas fa-check me-1"></i>Mark as Read
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteNotification(<?= $notification['id'] ?>)">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAsRead(notificationId) {
            fetch('<?= BASE_URL ?>?page=mark-notification-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark notification as read');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function markAllAsRead() {
            if (!confirm('Mark all notifications as read?')) return;
            
            fetch('<?= BASE_URL ?>?page=mark-all-notifications-read', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark all notifications as read');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) return;
            
            fetch('<?= BASE_URL ?>?page=delete-notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete notification');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</body>
</html>