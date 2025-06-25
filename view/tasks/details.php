<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details | Your App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .priority {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            color: white;
        }
        .high { background: #dc3545; }
        .medium { background: #fd7e14; }
        .low { background: #28a745; }
        .subtasks {
            margin: 20px 0;
            padding-left: 20px;
        }
        .comments {
            margin-top: 30px;
        }
        .comment {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <div class="task-header">
        <h1><?php echo htmlspecialchars($task['title']); ?></h1>
        <span class="priority <?php echo $task['priority']; ?>">
            <?php echo ucfirst($task['priority']); ?>
        </span>
    </div>

    <p><strong>Due:</strong> <?php echo date('M d, Y', strtotime($task['due_date'])); ?></p>
    
    <div class="task-description">
        <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
    </div>

    <div class="subtasks">
        <h3>Subtasks</h3>
        <?php foreach ($task['subtasks'] as $subtask): ?>
        <div>
            <input type="checkbox" <?php echo $subtask['completed'] ? 'checked' : ''; ?>>
            <?php echo htmlspecialchars($subtask['title']); ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="comments">
        <h3>Comments</h3>
        <?php foreach ($task['comments'] as $comment): ?>
        <div class="comment">
            <strong><?php echo htmlspecialchars($comment['author']); ?></strong>
            <p><?php echo htmlspecialchars($comment['text']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>