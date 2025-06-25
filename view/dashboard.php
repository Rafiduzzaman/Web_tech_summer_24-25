<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Your App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            color: var(--dark);
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .widget-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .widget {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .widget:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        .widget h2 {
            font-size: 18px;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--dark);
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
        }
        
        .quick-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn:hover {
            background: var(--secondary);
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary-light);
        }
        
        .notification-badge {
            background: var(--danger);
            color: white;
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 12px;
            margin-left: 8px;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stat-item {
            background: var(--primary-light);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin: 5px 0;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--gray);
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item::before {
            content: "•";
            color: var(--primary);
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .widget-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>! 👋</h1>
            <div class="header-actions">
                <a href="/notifications" class="btn btn-outline">
                    Notifications 
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="/profile" class="btn btn-outline">Profile</a>
                <a href="/logout" class="btn">Logout</a>
            </div>
        </div>

        <div class="widget-container">
            <!-- Stats Widget -->
            <div class="widget">
                <h2>Your Stats</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['tasks_completed'] ?? 0; ?></div>
                        <div class="stat-label">Tasks Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['projects'] ?? 0; ?></div>
                        <div class="stat-label">Projects</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="widget">
                <h2>Recent Activity</h2>
                <ul class="activity-list">
                    <?php foreach ($recentActivities as $activity): ?>
                        <li class="activity-item"><?php echo htmlspecialchars($activity); ?></li>
                    <?php endforeach; ?>
                    <?php if (empty($recentActivities)): ?>
                        <li class="activity-item">No recent activity</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Quick Actions -->
            <div class="widget">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="/tasks/create" class="btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        New Task
                    </a>
                    <a href="/projects" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Projects
                    </a>
                    <a href="/reports" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>