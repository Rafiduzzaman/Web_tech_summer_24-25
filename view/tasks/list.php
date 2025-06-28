<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks | Your App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .task-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .task-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .task-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .task-checkbox {
            margin-right: 15px;
        }
        .task-priority {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .priority-high { background: #dc3545; }
        .priority-medium { background: #fd7e14; }
        .priority-low { background: #28a745; }
        .task-due {
            font-size: 12px;
            color: #6c757d;
            margin-left: auto;
        }
        .task-actions {
            margin-left: 15px;
            display: flex;
            gap: 10px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
        }
        .status-pending { background: #6c757d; }
        .status-in_progress { background: #17a2b8; }
        .status-completed { background: #28a745; }
        .btn {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="task-header">
        <h1>My Tasks</h1>
        <a href="?page=create-task" class="btn">+ New Task</a>
    </div>

    <div class="task-filters">
        <select id="filterPriority">
            <option value="">All Priorities</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
        <select id="filterStatus">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
        <input type="text" id="searchTasks" placeholder="Search tasks...">
    </div>

    <div class="task-list">
        <?php foreach ($tasks as $task): ?>
        <div class="task-item" data-priority="<?= $task['priority'] ?>" data-status="<?= $task['status'] ?>">
            <div class="task-priority priority-<?= $task['priority'] ?>"></div>
            <div class="task-content" style="flex-grow: 1;">
                <strong><?= htmlspecialchars($task['title']) ?></strong>
                <?php if (!empty($task['description'])): ?>
                <p><?= htmlspecialchars($task['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="task-due">
                Due: <?= date('M d, Y', strtotime($task['due_date'])) ?>
            </div>
            <div class="status-badge status-<?= $task['status'] ?>">
                <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
            </div>
            <div class="task-actions">
                <a href="?page=edit-task&id=<?= $task['task_id'] ?>" class="btn" style="background: #ffc107;">Edit</a>
                <a href="?page=delete-task&id=<?= $task['task_id'] ?>" class="btn" style="background: #dc3545;">Delete</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Enhanced client-side filtering
        document.getElementById('filterPriority').addEventListener('change', filterTasks);
        document.getElementById('filterStatus').addEventListener('change', filterTasks);
        document.getElementById('searchTasks').addEventListener('input', filterTasks);

        function filterTasks() {
            const priority = document.getElementById('filterPriority').value;
            const status = document.getElementById('filterStatus').value;
            const search = document.getElementById('searchTasks').value.toLowerCase();

            document.querySelectorAll('.task-item').forEach(task => {
                const taskPriority = task.dataset.priority;
                const taskStatus = task.dataset.status;
                const taskText = task.textContent.toLowerCase();
                
                const priorityMatch = priority === '' || taskPriority === priority;
                const statusMatch = status === '' || taskStatus === status;
                const searchMatch = taskText.includes(search);
                
                task.style.display = (priorityMatch && statusMatch && searchMatch) ? '' : 'none';
            });
        }
    </script>
</body>
</html>