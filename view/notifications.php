<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Your App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .notification-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .notification-tabs button {
            padding: 8px 15px;
            background: #e9ecef;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .notification-tabs button.active {
            background: #4285f4;
            color: white;
        }
        .notification-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: flex-start;
        }
        .notification-item.unread {
            background-color: #f0f7ff;
        }
        .notification-icon {
            margin-right: 15px;
            font-size: 20px;
            color: #4285f4;
        }
        .notification-content {
            flex: 1;
        }
        .notification-time {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }
        .notification-actions {
            margin-left: 15px;
        }
        .mark-all-read {
            text-align: right;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="notification-header">
        <h1>Notifications</h1>
        <a href="/dashboard" class="btn">Back to Dashboard</a>
    </div>

    <div class="notification-tabs">
        <button class="active" onclick="filterNotifications('all')">All</button>
        <button onclick="filterNotifications('unread')">Unread</button>
        <button onclick="filterNotifications('system')">System</button>
        <button onclick="filterNotifications('alerts')">Alerts</button>
    </div>

    <div class="mark-all-read">
        <button onclick="markAllAsRead()">Mark All as Read</button>
    </div>

    <div class="notification-list" id="notificationList">
        <?php foreach ($notifications as $notification): ?>
        <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
            <div class="notification-icon">
                <?php echo getNotificationIcon($notification['type']); ?>
            </div>
            <div class="notification-content">
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                <div class="notification-time">
                    <?php echo timeAgo($notification['created_at']); ?>
                </div>
            </div>
            <div class="notification-actions">
                <?php if (!$notification['is_read']): ?>
                    <button onclick="markAsRead(<?php echo $notification['id']; ?>)">Mark Read</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function filterNotifications(type) {
            // Update active tab
            document.querySelectorAll('.notification-tabs button').forEach(btn => {
                btn.classList.toggle('active', btn.textContent.toLowerCase() === type);
            });

            // Filter notifications (simplified client-side filtering)
            document.querySelectorAll('.notification-item').forEach(item => {
                const show = type === 'all' || 
                             (type === 'unread' && item.classList.contains('unread')) ||
                             (type === item.dataset.type);
                item.style.display = show ? '' : 'none';
            });
        }

        function markAsRead(notificationId) {
            // AJAX call to mark notification as read
            console.log(`Marking notification ${notificationId} as read`);
            // Update UI immediately
            const item = document.querySelector(`[data-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                item.querySelector('.notification-actions').innerHTML = '';
            }
        }

        function markAllAsRead() {
            // AJAX call to mark all as read
            console.log('Marking all notifications as read');
            // Update UI immediately
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('unread');
                item.querySelector('.notification-actions').innerHTML = '';
            });
        }
    </script>
</body>
</html>