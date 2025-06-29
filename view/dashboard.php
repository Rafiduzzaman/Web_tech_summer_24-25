<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '?page=login');
    exit();
}

$user = $_SESSION['user'];
$taskModel = new Task($user['id']);
$notificationModel = new Notification();
$activityModel = new ActivityLog();

// Get user's task statistics
$stats = $taskModel->getUserStats($user['id']);

// Get recent tasks
$recentTasks = $taskModel->getRecentTasks($user['id'], 5);

// Get upcoming due tasks
$upcomingTasks = $taskModel->getUpcomingTasks($user['id'], 5);

// Get recent notifications
$notifications = $notificationModel->getUserNotifications($user['id'], 5);

// Get recent activities
$activities = $activityModel->getUserActivities($user['id'], 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TaskMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .dashboard-container {
            padding: 30px 0;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .stats-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stats-card.warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .stats-card.danger {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }
        .stats-card.info {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .task-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #dee2e6;
        }
        .task-card.high { border-left-color: #dc3545; }
        .task-card.medium { border-left-color: #fd7e14; }
        .task-card.low { border-left-color: #28a745; }
        .task-card.completed { border-left-color: #28a745; opacity: 0.7; }
        .priority-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .chart-container canvas {
            max-height: 300px;
        }
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1><i class="fas fa-tachometer-alt me-2"></i>Welcome back, <?= htmlspecialchars($user['name']) ?>!</h1>
            <p class="mb-0">Here's what's happening with your tasks today.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total'] ?></div>
                    <div class="stats-label">Total Tasks</div>
                    <i class="fas fa-tasks fa-2x mt-2" style="opacity: 0.3;"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="stats-number"><?= $stats['completed'] ?></div>
                    <div class="stats-label">Completed</div>
                    <i class="fas fa-check-circle fa-2x mt-2" style="opacity: 0.3;"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning">
                    <div class="stats-number"><?= $stats['pending'] ?></div>
                    <div class="stats-label">Pending</div>
                    <i class="fas fa-clock fa-2x mt-2" style="opacity: 0.3;"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card danger">
                    <div class="stats-number"><?= $stats['overdue'] ?></div>
                    <div class="stats-label">Overdue</div>
                    <i class="fas fa-exclamation-triangle fa-2x mt-2" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="quick-actions">
                    <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>?page=create-task" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create New Task
                        </a>
                        <a href="<?= BASE_URL ?>?page=tasks" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>View All Tasks
                        </a>
                        <a href="<?= BASE_URL ?>?page=calendar" class="btn btn-outline-info">
                            <i class="fas fa-calendar me-2"></i>Calendar View
                        </a>
                        <a href="<?= BASE_URL ?>?page=profile" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                    </div>
                </div>

                <!-- Recent Notifications -->
                <div class="quick-actions">
                    <h5><i class="fas fa-bell me-2"></i>Recent Notifications</h5>
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($notification['title']) ?></strong>
                                    <span class="activity-time"><?= date('M d', strtotime($notification['created_at'])) ?></span>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($notification['message']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent notifications</p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>?page=notifications" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Task Progress Chart -->
                <div class="chart-container">
                    <h5><i class="fas fa-chart-pie me-2"></i>Task Progress</h5>
                    <canvas id="taskChart" width="400" height="200"></canvas>
                </div>

                <!-- Upcoming Tasks -->
                <div class="chart-container">
                    <h5><i class="fas fa-calendar-day me-2"></i>Upcoming Tasks</h5>
                    <?php if (!empty($upcomingTasks)): ?>
                        <?php foreach ($upcomingTasks as $task): ?>
                            <div class="task-card <?= $task['priority'] ?> <?= $task['status'] === 'completed' ? 'completed' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($task['title']) ?></h6>
                                        <p class="text-muted mb-2"><?= htmlspecialchars($task['description']) ?></p>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="priority-badge bg-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'success') ?>">
                                                <?= ucfirst($task['priority']) ?>
                                            </span>
                                            <?php if ($task['category'] && $task['category'] !== 'General'): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($task['category']) ?></span>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('M d, Y', strtotime($task['due_date'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="<?= BASE_URL ?>?page=task-details&id=<?= $task['task_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>?page=edit-task&id=<?= $task['task_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No upcoming tasks</p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>?page=tasks" class="btn btn-outline-primary mt-2">View All Tasks</a>
                </div>

                <!-- Recent Activities -->
                <div class="chart-container">
                    <h5><i class="fas fa-history me-2"></i>Recent Activities</h5>
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($activity['action']) ?></strong>
                                        <?php if ($activity['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($activity['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="activity-time"><?= date('M d, g:i A', strtotime($activity['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent activities</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global variable to track if chart is already created
        let taskChart = null;
        
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Only create chart once
            if (taskChart !== null) {
                return;
            }
            
            try {
                // Check if Chart.js is loaded
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js not loaded');
                    return;
                }
                
                // Task Progress Chart
                const ctx = document.getElementById('taskChart');
                if (ctx) {
                    // Destroy existing chart if it exists
                    if (taskChart) {
                        taskChart.destroy();
                    }
                    
                    taskChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Completed', 'In Progress', 'Pending', 'Overdue'],
                            datasets: [{
                                data: [
                                    <?= $stats['completed'] ?>,
                                    <?= $stats['in_progress'] ?? 0 ?>,
                                    <?= $stats['pending'] ?>,
                                    <?= $stats['overdue'] ?>
                                ],
                                backgroundColor: [
                                    '#28a745',
                                    '#17a2b8',
                                    '#ffc107',
                                    '#dc3545'
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 1000
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error initializing chart:', error);
            }
        });
    </script>
</body>
</html>