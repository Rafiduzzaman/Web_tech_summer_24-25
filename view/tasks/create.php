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
    <title>Create New Task - TaskMaster</title>
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
        .priority-high { color: #dc3545; }
        .priority-medium { color: #fd7e14; }
        .priority-low { color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="task-form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle me-2"></i>Create New Task</h2>
                <a href="<?= BASE_URL ?>?page=tasks" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Tasks
                </a>
            </div>

            <form action="?page=create-task" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Task Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" class="priority-low">Low</option>
                                <option value="medium" class="priority-medium" selected>Medium</option>
                                <option value="high" class="priority-high">High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category" value="General" list="categories">
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
                            <input type="datetime-local" class="form-control" id="due_date" name="due_date">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe your task..."></textarea>
                </div>

                <!-- Subtasks Section -->
                <div class="mb-3">
                    <label class="form-label">Subtasks</label>
                    <div id="subtasks-container">
                        <div class="subtask-item">
                            <input type="text" class="form-control" name="subtasks[]" placeholder="Add a subtask...">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSubtask(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addSubtask()">
                        <i class="fas fa-plus me-1"></i>Add Subtask
                    </button>
                </div>

                <!-- File Attachments -->
                <div class="mb-3">
                    <label for="attachments" class="form-label">Attachments</label>
                    <input type="file" class="form-control" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                    <div class="form-text">Maximum file size: 25MB. Allowed types: Images, PDF, Word documents</div>
                    <div id="attachment-preview" class="mt-2"></div>
                </div>

                <!-- Sharing -->
                <div class="mb-3">
                    <label for="shared_with" class="form-label">Share with (comma-separated emails)</label>
                    <input type="text" class="form-control" id="shared_with" name="shared_with" placeholder="email1@example.com, email2@example.com">
                    <div class="form-text">Enter email addresses separated by commas to share this task</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= BASE_URL ?>?page=tasks" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Task
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

        // Set default due date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(9, 0, 0, 0);
        document.getElementById('due_date').value = tomorrow.toISOString().slice(0, 16);
    </script>
</body>
</html> 