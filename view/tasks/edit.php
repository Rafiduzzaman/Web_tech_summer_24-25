<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '?page=login');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - TaskMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .task-form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #2575fc;
        }
        .subtask-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .attachment-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #e3f2fd;
            border-radius: 5px;
        }
        .existing-attachment {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .priority-high { color: #dc3545; }
        .priority-medium { color: #fd7e14; }
        .priority-low { color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="task-form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit me-2"></i>Edit Task</h2>
                <a href="<?= BASE_URL ?>?page=task-details&id=<?= $task['task_id'] ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Task
                </a>
            </div>

            <form action="?page=edit-task&id=<?= $task['task_id'] ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Task Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" class="priority-low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" class="priority-medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" class="priority-high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($task['category'] ?? 'General') ?>" list="categories">
                            <datalist id="categories">
                                <option value="General">
                                <option value="Work">
                                <option value="Personal">
                                <option value="Shopping">
                                <option value="Health">
                                <option value="Education">
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="datetime-local" class="form-control" id="due_date" name="due_date" 
                                   value="<?= $task['due_date'] ? date('Y-m-d\TH:i', strtotime($task['due_date'])) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe your task..."><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                </div>

                <!-- Subtasks Section -->
                <div class="mb-3">
                    <label class="form-label">Subtasks</label>
                    <div id="subtasks-container">
                        <?php 
                        $subtasks = json_decode($task['subtasks'] ?? '[]', true);
                        if (!empty($subtasks)):
                            foreach ($subtasks as $subtask):
                        ?>
                            <div class="subtask-item" data-id="<?= $subtask['id'] ?>">
                                <input type="checkbox" class="form-check-input" name="subtask_completed[]" value="<?= $subtask['id'] ?>" 
                                       <?= $subtask['completed'] ? 'checked' : '' ?>>
                                <input type="text" class="form-control" name="subtasks[]" value="<?= htmlspecialchars($subtask['text']) ?>" placeholder="Subtask text">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSubtask(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="subtask-item">
                                <input type="text" class="form-control" name="subtasks[]" placeholder="Add a subtask...">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSubtask(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addSubtask()">
                        <i class="fas fa-plus me-1"></i>Add Subtask
                    </button>
                </div>

                <!-- Existing Attachments -->
                <?php 
                $attachments = json_decode($task['attachments'] ?? '[]', true);
                if (!empty($attachments)): ?>
                    <div class="mb-3">
                        <label class="form-label">Existing Attachments</label>
                        <?php foreach ($attachments as $attachment): ?>
                            <div class="existing-attachment">
                                <i class="fas fa-file"></i>
                                <span><?= htmlspecialchars($attachment['name']) ?></span>
                                <small class="text-muted">(<?= number_format($attachment['size'] / 1024 / 1024, 2) ?> MB)</small>
                                <div class="ms-auto">
                                    <a href="<?= BASE_URL . $attachment['path'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAttachment('<?= $attachment['id'] ?>')">
                                        <i class="fas fa-trash me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- New File Attachments -->
                <div class="mb-3">
                    <label for="attachments" class="form-label">Add New Attachments</label>
                    <input type="file" class="form-control" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                    <div class="form-text">Maximum file size: 25MB. Allowed types: Images, PDF, Word documents</div>
                    <div id="attachment-preview" class="mt-2"></div>
                </div>

                <!-- Sharing -->
                <div class="mb-3">
                    <label for="shared_with" class="form-label">Share with (comma-separated emails)</label>
                    <input type="text" class="form-control" id="shared_with" name="shared_with" 
                           value="<?= htmlspecialchars(implode(', ', array_column(json_decode($task['shared_with'] ?? '[]', true), 'email'))) ?>" 
                           placeholder="email1@example.com, email2@example.com">
                    <div class="form-text">Enter email addresses separated by commas to share this task</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= BASE_URL ?>?page=task-details&id=<?= $task['task_id'] ?>" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addSubtask() {
            const container = document.getElementById('subtasks-container');
            const subtaskItem = document.createElement('div');
            subtaskItem.className = 'subtask-item';
            subtaskItem.innerHTML = `
                <input type="text" class="form-control" name="subtasks[]" placeholder="Add a subtask...">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSubtask(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(subtaskItem);
        }

        function removeSubtask(button) {
            const subtaskItem = button.parentElement;
            const container = document.getElementById('subtasks-container');
            
            // Don't remove if it's the last subtask
            if (container.children.length > 1) {
                subtaskItem.remove();
            } else {
                // Clear the input instead of removing
                subtaskItem.querySelector('input').value = '';
            }
        }

        function removeAttachment(attachmentId) {
            if (confirm('Are you sure you want to remove this attachment?')) {
                fetch('<?= BASE_URL ?>?page=remove-attachment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'task_id=<?= $task['task_id'] ?>&attachment_id=' + attachmentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to remove attachment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }

        // File attachment preview
        document.getElementById('attachments').addEventListener('change', function(e) {
            const preview = document.getElementById('attachment-preview');
            preview.innerHTML = '';
            
            for (let file of e.target.files) {
                const filePreview = document.createElement('div');
                filePreview.className = 'attachment-preview';
                filePreview.innerHTML = `
                    <i class="fas fa-file"></i>
                    <span>${file.name}</span>
                    <small class="text-muted">(${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                `;
                preview.appendChild(filePreview);
            }
        });
    </script>
</body>
</html> 