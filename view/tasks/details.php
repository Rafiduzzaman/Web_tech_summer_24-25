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
    <title><?= $isEditMode && !empty($task['title']) ? htmlspecialchars($task['title']) : 'New Task' ?> | Task Manager</title>
    <style>
        :root {
            --primary: #4361ee; --primary-light: #e0e7ff;
            --danger: #f72585; --warning: #f8961e; --success: #4cc9f0;
            --gray: #6c757d; --light-gray: #f8f9fa; --dark: #212529;
        }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; }
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .task-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--light-gray); }
        .priority { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .high { background: var(--danger); color: white; } 
        .medium { background: var(--warning); color: white; } 
        .low { background: var(--success); color: white; }
        .task-description { background: var(--light-gray); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
        .error-message {
            color: var(--danger);
            background-color: #fee;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        /* Priority Selector */
        .priority-selector { position: relative; }
        .priority-display { cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }
        .priority-options { display: none; position: absolute; right: 0; top: 100%; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10; min-width: 120px; }
        .priority-selector.active .priority-options { display: block; }
        .priority-option { padding: 0.5rem 1rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }
        .priority-option:hover { background: #f5f7fa; }
        .priority-option::before { content: ""; display: inline-block; width: 12px; height: 12px; border-radius: 50%; }
        .priority-option.high::before { background: var(--danger); }
        .priority-option.medium::before { background: var(--warning); }
        .priority-option.low::before { background: var(--success); }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="text"], textarea, select, input[type="date"] {
            width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;
            font-family: inherit; font-size: inherit;
        }
        textarea { min-height: 120px; resize: vertical; }
        .btn { 
            padding: 0.75rem 1.5rem; border: none; border-radius: 6px; 
            cursor: pointer; font-weight: 600; font-size: 1rem;
            transition: background-color 0.2s ease;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #3a56d4; }
        .btn-secondary { background: var(--gray); color: white; margin-right: 1rem; }
        .btn-secondary:hover { background: #5a6268; }
        .form-actions { margin-top: 2rem; display: flex; justify-content: flex-end; }
        @media (max-width: 768px) {
            .task-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .priority-selector { align-self: flex-end; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="task-header">
                <div class="form-group" style="flex-grow: 1;">
                    <label for="title">Task Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
                </div>
                
                <div class="priority-selector" id="prioritySelector">
                    <label>Priority</label>
                    <div class="priority-display" onclick="togglePriorityOptions()">
                        <span class="priority <?= $task['priority'] ?>"><?= ucfirst($task['priority']) ?></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                    <div class="priority-options" id="priorityOptions">
                        <div class="priority-option high <?= $task['priority'] === 'high' ? 'selected' : '' ?>" onclick="setPriority('high')">High</div>
                        <div class="priority-option medium <?= $task['priority'] === 'medium' ? 'selected' : '' ?>" onclick="setPriority('medium')">Medium</div>
                        <div class="priority-option low <?= $task['priority'] === 'low' ? 'selected' : '' ?>" onclick="setPriority('low')">Low</div>
                    </div>
                    <input type="hidden" name="priority" id="priorityInput" value="<?= $task['priority'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="<?= $task['due_date'] ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>

            <div class="form-actions">
                <a href="list.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEditMode ? 'Update Task' : 'Create Task' ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePriorityOptions() {
            document.getElementById('prioritySelector').classList.toggle('active');
        }
        
        function setPriority(priority) {
            const display = document.querySelector('.priority-display .priority');
            display.className = 'priority ' + priority;
            display.textContent = priority.charAt(0).toUpperCase() + priority.slice(1);
            document.getElementById('priorityInput').value = priority;
            document.getElementById('prioritySelector').classList.remove('active');
        }
        
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.priority-selector')) {
                document.getElementById('prioritySelector').classList.remove('active');
            }
        });
    </script>
</body>
</html>