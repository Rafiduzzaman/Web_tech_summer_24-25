<?php
// view/tasks/details.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$task = [
    'title' => '',
    'description' => '',
    'priority' => 'medium',
    'due_date' => date('Y-m-d'),
    'subtasks' => [],
    'comments' => []
];

if (isset($_GET['task_id'])) {
    // $task = loadTaskFromDatabase($_GET['task_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $task['title'] ? htmlspecialchars($task['title']) : 'New Task' ?> | Task Manager</title>
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
        .high { background: var(--danger); } .medium { background: var(--warning); } .low { background: var(--success); }
        .task-description { background: var(--light-gray); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
        .empty-state { color: var(--gray); text-align: center; padding: 2rem; background: var(--light-gray); border-radius: 8px; }
        
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
    </style>
</head>
<body>
    <div class="container">
        <div class="task-header">
            <h1><?= $task['title'] ? htmlspecialchars($task['title']) : 'New Task' ?></h1>
            <div class="priority-selector" id="prioritySelector">
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

        <div class="task-meta">
            <div><strong>Due:</strong> <?= date('M j, Y', strtotime($task['due_date'])) ?></div>
            <div><strong>Status:</strong> <span style="color: var(--primary)">Pending</span></div>
        </div>
        
        <div class="task-description">
            <?= $task['description'] ? nl2br(htmlspecialchars($task['description'])) : '<em>No description</em>' ?>
        </div>

        <div class="subtasks">
            <h3>Subtasks</h3>
            <?php if ($task['subtasks']): ?>
                <?php foreach ($task['subtasks'] as $subtask): ?>
                <div class="subtask">
                    <input type="checkbox" <?= $subtask['completed'] ? 'checked' : '' ?>>
                    <?= htmlspecialchars($subtask['title']) ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No subtasks</div>
            <?php endif; ?>
        </div>

        <div class="comments">
            <h3>Comments</h3>
            <?php if ($task['comments']): ?>
                <?php foreach ($task['comments'] as $comment): ?>
                <div class="comment">
                    <strong><?= htmlspecialchars($comment['author']) ?></strong>
                    <p><?= htmlspecialchars($comment['text']) ?></p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No comments</div>
            <?php endif; ?>
        </div>
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