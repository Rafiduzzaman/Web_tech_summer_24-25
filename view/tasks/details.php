<?php
// view/tasks/details.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../model/User.php';

$user = new User();
$isEditMode = isset($_GET['task_id']) && is_numeric($_GET['task_id']);
$taskId = $isEditMode ? (int)$_GET['task_id'] : null;
$error = null;

// Initialize task data
$task = [
    'title' => '',
    'description' => '',
    'priority' => 'medium',
    'due_date' => date('Y-m-d'),
    'status' => 'pending'
];

// Load task data if in edit mode
if ($isEditMode) {
    try {
        $userId = $_SESSION['user']['id'];
        $taskTable = "user_{$userId}_tasks";
        
        $stmt = $user->getDB()->prepare("SELECT * FROM `$taskTable` WHERE task_id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $task = $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Error loading task: " . $e->getMessage());
        $error = "Failed to load task data.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskData = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'priority' => $_POST['priority'] ?? 'medium',
        'due_date' => $_POST['due_date'] ?? date('Y-m-d'),
        'status' => $_POST['status'] ?? 'pending'
    ];
    
    // Basic validation
    if (empty($taskData['title'])) {
        $error = "Task title is required.";
    } else {
        try {
            $userId = $_SESSION['user']['id'];
            $taskTable = "user_{$userId}_tasks";
            
            if ($isEditMode && $taskId) {
                // Update existing task
                $stmt = $user->getDB()->prepare("UPDATE `$taskTable` SET 
                    title = ?, description = ?, priority = ?, due_date = ?, status = ?
                    WHERE task_id = ?");
                $stmt->bind_param("sssssi", 
                    $taskData['title'], $taskData['description'], $taskData['priority'],
                    $taskData['due_date'], $taskData['status'], $taskId);
            } else {
                // Create new task
                $stmt = $user->getDB()->prepare("INSERT INTO `$taskTable` 
                    (title, description, priority, due_date, status) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", 
                    $taskData['title'], $taskData['description'], $taskData['priority'],
                    $taskData['due_date'], $taskData['status']);
            }
            
            if ($stmt->execute()) {
                // Redirect to prevent form resubmission
                $redirectId = $isEditMode ? $taskId : $stmt->insert_id;
                header("Location: details.php?task_id=" . $redirectId);
                exit();
            } else {
                $error = "Failed to save task. Please try again.";
            }
        } catch (Exception $e) {
            error_log("Error saving task: " . $e->getMessage());
            $error = "Failed to save task. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - TaskMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .task-details-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .task-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .priority-badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
        .priority-high { background-color: #dc3545; color: white; }
        .priority-medium { background-color: #fd7e14; color: white; }
        .priority-low { background-color: #28a745; color: white; }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
        .status-pending { background-color: #6c757d; color: white; }
        .status-in_progress { background-color: #17a2b8; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .subtask-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin-bottom: 8px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #dee2e6;
        }
        .subtask-item.completed {
            background-color: #d4edda;
            border-left-color: #28a745;
            text-decoration: line-through;
            opacity: 0.7;
        }
        .attachment-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin-bottom: 8px;
            background-color: #e3f2fd;
            border-radius: 5px;
        }
        .shared-user {
            display: inline-block;
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 15px;
            margin: 2px;
            font-size: 0.875rem;
        }
        .task-meta {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .task-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="task-details-container">
            <!-- Task Header -->
            <div class="task-header">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2><?= htmlspecialchars($task['title']) ?></h2>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="priority-badge priority-<?= $task['priority'] ?>">
                                <i class="fas fa-flag me-1"></i><?= ucfirst($task['priority']) ?> Priority
                            </span>
                            <span class="status-badge status-<?= $task['status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                            </span>
                            <?php if ($task['category'] && $task['category'] !== 'General'): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($task['category']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>?page=edit-task&id=<?= $task['task_id'] ?>" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="<?= BASE_URL ?>?page=tasks" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Task Meta Information -->
            <div class="task-meta">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Created:</strong> <?= date('M d, Y g:i A', strtotime($task['created_at'])) ?>
                    </div>
                    <div class="col-md-6">
                        <?php if ($task['due_date']): ?>
                            <strong>Due Date:</strong> <?= date('M d, Y g:i A', strtotime($task['due_date'])) ?>
                            <?php 
                            $dueDate = new DateTime($task['due_date']);
                            $now = new DateTime();
                            if ($dueDate < $now && $task['status'] !== 'completed'): ?>
                                <span class="badge bg-danger ms-2">Overdue</span>
                            <?php elseif ($dueDate->diff($now)->days <= 1 && $task['status'] !== 'completed'): ?>
                                <span class="badge bg-warning ms-2">Due Soon</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Task Description -->
            <?php if (!empty($task['description'])): ?>
                <div class="mb-4">
                    <h5>Description</h5>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                </div>
            <?php endif; ?>

            <!-- Subtasks -->
            <?php 
            $subtasks = json_decode($task['subtasks'] ?? '[]', true);
            if (!empty($subtasks)): ?>
                <div class="mb-4">
                    <h5>Subtasks</h5>
                    <div id="subtasks-list">
                        <?php foreach ($subtasks as $subtask): ?>
                            <div class="subtask-item <?= $subtask['completed'] ? 'completed' : '' ?>" data-id="<?= $subtask['id'] ?>">
                                <input type="checkbox" class="form-check-input" 
                                       <?= $subtask['completed'] ? 'checked' : '' ?>
                                       onchange="toggleSubtask('<?= $subtask['id'] ?>')">
                                <span><?= htmlspecialchars($subtask['text']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Attachments -->
            <?php 
            $attachments = json_decode($task['attachments'] ?? '[]', true);
            if (!empty($attachments)): ?>
                <div class="mb-4">
                    <h5>Attachments</h5>
                    <?php foreach ($attachments as $attachment): ?>
                        <div class="attachment-item">
                            <i class="fas fa-file"></i>
                            <span><?= htmlspecialchars($attachment['name']) ?></span>
                            <small class="text-muted">(<?= number_format($attachment['size'] / 1024 / 1024, 2) ?> MB)</small>
                            <a href="<?= BASE_URL . $attachment['path'] ?>" class="btn btn-sm btn-outline-primary ms-auto" target="_blank">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Shared With -->
            <?php 
            $sharedWith = json_decode($task['shared_with'] ?? '[]', true);
            if (!empty($sharedWith)): ?>
                <div class="mb-4">
                    <h5>Shared With</h5>
                    <?php foreach ($sharedWith as $share): ?>
                        <span class="shared-user">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($share['email']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Task Actions -->
            <div class="task-actions">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Quick Actions</h6>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" onchange="updateStatus(this.value)" style="width: auto;">
                                <option value="">Change Status</option>
                                <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                            <select class="form-select form-select-sm" onchange="updatePriority(this.value)" style="width: auto;">
                                <option value="">Change Priority</option>
                                <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="<?= BASE_URL ?>?page=delete-task&id=<?= $task['task_id'] ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('Are you sure you want to delete this task?')">
                            <i class="fas fa-trash me-1"></i>Delete Task
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSubtask(subtaskId) {
            fetch('<?= BASE_URL ?>?page=toggle-subtask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'task_id=<?= $task['task_id'] ?>&subtask_id=' + subtaskId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update subtask');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function updateStatus(status) {
            if (!status) return;
            
            fetch('<?= BASE_URL ?>?page=update-task-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'task_id=<?= $task['task_id'] ?>&status=' + status
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function updatePriority(priority) {
            if (!priority) return;
            
            fetch('<?= BASE_URL ?>?page=update-task-priority', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'task_id=<?= $task['task_id'] ?>&priority=' + priority
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update priority');
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