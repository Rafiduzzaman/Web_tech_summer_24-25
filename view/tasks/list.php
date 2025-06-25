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
        }
    </style>
</head>
<body>
    <div class="task-header">
        <h1>My Tasks</h1>
        <a href="/tasks/create" class="btn">+ New Task</a>
    </div>

    <div class="task-filters">
        <select id="filterPriority">
            <option value="">All Priorities</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
        <select id="filterCategory">
            <option value="">All Categories</option>
            <option value="work">Work</option>
            <option value="personal">Personal</option>
        </select>
        <input type="text" id="searchTasks" placeholder="Search tasks...">
    </div>

    <div class="task-list">
        <?php foreach ($tasks as $task): ?>
        <div class="task-item">
            <input type="checkbox" class="task-checkbox" <?php echo $task['completed'] ? 'checked' : ''; ?>>
            <div class="task-priority priority-<?php echo $task['priority']; ?>"></div>
            <div class="task-content">
                <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                <?php if (!empty($task['description'])): ?>
                <p><?php echo htmlspecialchars($task['description']); ?></p>
                <?php endif; ?>
            </div>
            <div class="task-due">
                Due: <?php echo date('M d', strtotime($task['due_date'])); ?>
            </div>
            <div class="task-actions">
                <a href="/tasks/<?php echo $task['id']; ?>/edit">Edit</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Simple client-side filtering
        document.getElementById('filterPriority').addEventListener('change', filterTasks);
        document.getElementById('filterCategory').addEventListener('change', filterTasks);
        document.getElementById('searchTasks').addEventListener('input', filterTasks);

        function filterTasks() {
            const priority = document.getElementById('filterPriority').value;
            const category = document.getElementById('filterCategory').value;
            const search = document.getElementById('searchTasks').value.toLowerCase();

            document.querySelectorAll('.task-item').forEach(task => {
                const taskPriority = task.querySelector('.task-priority').className.includes(priority);
                const taskText = task.textContent.toLowerCase();
                const show = (priority === '' || taskPriority) && 
                             taskText.includes(search);
                task.style.display = show ? '' : 'none';
            });
        }
    </script>
</body>
</html>