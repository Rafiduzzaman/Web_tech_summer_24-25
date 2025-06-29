<?php
require_once BASE_PATH . 'model/Task.php';
require_once BASE_PATH . 'model/Notification.php';
require_once BASE_PATH . 'model/ActivityLog.php';

class TaskController {
    public static function create() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskModel = new Task($_SESSION['user']['id']);
            $notificationModel = new Notification();
            $activityLog = new ActivityLog();

            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $dueDate = $_POST['due_date'] ?? '';
            $priority = $_POST['priority'] ?? 'medium';
            $status = $_POST['status'] ?? 'pending';
            $category = $_POST['category'] ?? 'General';
            $subtasks = [];
            $attachments = [];
            $sharedWith = [];

            // Handle subtasks
            if (!empty($_POST['subtasks'])) {
                foreach ($_POST['subtasks'] as $subtask) {
                    if (!empty(trim($subtask))) {
                        $subtasks[] = [
                            'id' => uniqid(),
                            'text' => trim($subtask),
                            'completed' => false,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Handle file attachments
            if (!empty($_FILES['attachments']['name'][0])) {
                $uploadDir = BASE_PATH . 'assets/uploads/tasks/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
                    $fileName = $_FILES['attachments']['name'][$key];
                    $fileSize = $_FILES['attachments']['size'][$key];
                    $fileType = $_FILES['attachments']['type'][$key];
                    
                    // Validate file size (25MB limit)
                    if ($fileSize > 25 * 1024 * 1024) {
                        continue;
                    }
                    
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    if (!in_array($fileType, $allowedTypes)) {
                        continue;
                    }
                    
                    $newFileName = uniqid() . '_' . $fileName;
                    $filePath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $attachments[] = [
                            'id' => uniqid(),
                            'name' => $fileName,
                            'path' => 'assets/uploads/tasks/' . $newFileName,
                            'size' => $fileSize,
                            'type' => $fileType,
                            'uploaded_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            // Handle sharing
            if (!empty($_POST['shared_with'])) {
                $emails = array_filter(array_map('trim', explode(',', $_POST['shared_with'])));
                foreach ($emails as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $sharedWith[] = [
                            'email' => $email,
                            'permissions' => ['view'],
                            'shared_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }

            if ($taskModel->create($title, $description, $dueDate, $priority, $status, $category, $subtasks, $attachments, $sharedWith)) {
                // Create notification
                $notificationModel->createTaskNotification($_SESSION['user']['id'], $title, 'created');
                
                // Log activity
                $activityLog->logTaskCreate($_SESSION['user']['id'], $title);
                
                header("Location: " . BASE_URL . "?page=tasks&success=1");
            } else {
                header("Location: " . BASE_URL . "?page=tasks&error=1");
            }
            exit();
        }

        // Show create task form
        require BASE_PATH . 'view/tasks/create.php';
    }

    public static function showAll() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        $taskModel = new Task($_SESSION['user']['id']);
        
        // Handle filters
        $filters = [];
        if (!empty($_GET['priority'])) $filters['priority'] = $_GET['priority'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['sort'])) $filters['sort'] = $_GET['sort'];

        $tasks = $taskModel->getAll($filters);
        $categories = $taskModel->getCategories();
        $stats = $taskModel->getStats();

        require BASE_PATH . 'view/tasks/list.php';
    }

    public static function showDetails() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        $taskId = $_GET['id'] ?? null;
        if (!$taskId) {
            header("Location: " . BASE_URL . "?page=tasks&error=1");
            exit();
        }

        $taskModel = new Task($_SESSION['user']['id']);
        $task = $taskModel->getById($taskId);

        if (!$task) {
            header("Location: " . BASE_URL . "?page=tasks&error=1");
            exit();
        }

        require BASE_PATH . 'view/tasks/details.php';
    }

    public static function update() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskModel = new Task($_SESSION['user']['id']);
            $notificationModel = new Notification();
            $activityLog = new ActivityLog();

            $taskId = $_POST['task_id'] ?? null;
            if (!$taskId) {
                header("Location: " . BASE_URL . "?page=tasks&error=1");
                exit();
            }

            $task = $taskModel->getById($taskId);
            if (!$task) {
                header("Location: " . BASE_URL . "?page=tasks&error=1");
                exit();
            }

            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $dueDate = $_POST['due_date'] ?? '';
            $priority = $_POST['priority'] ?? 'medium';
            $status = $_POST['status'] ?? 'pending';
            $category = $_POST['category'] ?? 'General';

            if ($taskModel->update($taskId, $title, $description, $dueDate, $priority, $status, $category)) {
                // Create notification
                $notificationModel->createTaskNotification($_SESSION['user']['id'], $title, 'updated');
                
                // Log activity
                $activityLog->logTaskUpdate($_SESSION['user']['id'], $title);
                
                header("Location: " . BASE_URL . "?page=task-details&id=$taskId&success=1");
            } else {
                header("Location: " . BASE_URL . "?page=task-details&id=$taskId&error=1");
            }
            exit();
        }
    }

    public static function delete() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        $taskId = $_GET['id'] ?? null;
        if (!$taskId) {
            header("Location: " . BASE_URL . "?page=tasks&error=1");
            exit();
        }

        $taskModel = new Task($_SESSION['user']['id']);
        $notificationModel = new Notification();
        $activityLog = new ActivityLog();

        $task = $taskModel->getById($taskId);
        if (!$task) {
            header("Location: " . BASE_URL . "?page=tasks&error=1");
            exit();
        }

        if ($taskModel->delete($taskId)) {
            // Create notification
            $notificationModel->createTaskNotification($_SESSION['user']['id'], $task['title'], 'deleted');
            
            // Log activity
            $activityLog->logTaskDelete($_SESSION['user']['id'], $task['title']);
            
            header("Location: " . BASE_URL . "?page=tasks&success=1");
        } else {
            header("Location: " . BASE_URL . "?page=tasks&error=1");
        }
        exit();
    }

    public static function updateStatus() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$taskId || !$status) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing parameters']);
                exit();
            }

            $taskModel = new Task($_SESSION['user']['id']);
            $task = $taskModel->getById($taskId);

            if (!$task) {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found']);
                exit();
            }

            if ($taskModel->updateStatus($taskId, $status)) {
                $notificationModel = new Notification();
                $activityLog = new ActivityLog();
                
                $notificationModel->createTaskNotification($_SESSION['user']['id'], $task['title'], "marked as $status");
                $activityLog->logTaskUpdate($_SESSION['user']['id'], $task['title']);
                
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Update failed']);
            }
            exit();
        }
    }

    public static function addSubtask() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $subtask = $_POST['subtask'] ?? null;

            if (!$taskId || !$subtask) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing parameters']);
                exit();
            }

            $taskModel = new Task($_SESSION['user']['id']);
            
            if ($taskModel->addSubtask($taskId, $subtask)) {
                $task = $taskModel->getById($taskId);
                $subtasks = json_decode($task['subtasks'] ?? '[]', true);
                echo json_encode(['success' => true, 'subtasks' => $subtasks]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add subtask']);
            }
            exit();
        }
    }

    public static function toggleSubtask() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $subtaskId = $_POST['subtask_id'] ?? null;

            if (!$taskId || !$subtaskId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing parameters']);
                exit();
            }

            $taskModel = new Task($_SESSION['user']['id']);
            
            if ($taskModel->toggleSubtask($taskId, $subtaskId)) {
                $task = $taskModel->getById($taskId);
                $subtasks = json_decode($task['subtasks'] ?? '[]', true);
                echo json_encode(['success' => true, 'subtasks' => $subtasks]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to toggle subtask']);
            }
            exit();
        }
    }

    public static function shareTask() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? null;
            $userEmail = $_POST['user_email'] ?? null;

            if (!$taskId || !$userEmail) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing parameters']);
                exit();
            }

            $taskModel = new Task($_SESSION['user']['id']);
            $notificationModel = new Notification();
            
            if ($taskModel->shareTask($taskId, $userEmail)) {
                $task = $taskModel->getById($taskId);
                $notificationModel->createSharedTaskNotification($_SESSION['user']['id'], $task['title'], $_SESSION['user']['name']);
                
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to share task']);
            }
            exit();
        }
    }

    public static function search() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        $query = $_GET['q'] ?? '';
        if (empty($query)) {
            header("Location: " . BASE_URL . "?page=tasks");
            exit();
        }

        $taskModel = new Task($_SESSION['user']['id']);
        $tasks = $taskModel->searchTasks($query);

        require BASE_PATH . 'view/tasks/search.php';
    }

    public static function calendar() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        $taskModel = new Task($_SESSION['user']['id']);
        
        $startDate = $_GET['start'] ?? date('Y-m-01');
        $endDate = $_GET['end'] ?? date('Y-m-t');
        
        $tasks = $taskModel->getTasksByDateRange($startDate, $endDate);

        require BASE_PATH . 'view/tasks/calendar.php';
    }

    public static function export() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        $taskModel = new Task($_SESSION['user']['id']);
        $format = $_GET['format'] ?? 'csv';
        
        $filters = [];
        if (!empty($_GET['priority'])) $filters['priority'] = $_GET['priority'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];

        $tasks = $taskModel->getAll($filters);

        if ($format === 'csv') {
            $filename = 'tasks_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Title', 'Description', 'Due Date', 'Priority', 'Status', 'Category', 'Created']);

            foreach ($tasks as $task) {
                fputcsv($output, [
                    $task['title'],
                    $task['description'],
                    $task['due_date'],
                    $task['priority'],
                    $task['status'],
                    $task['category'],
                    $task['created_at']
                ]);
            }

            fclose($output);
            exit();
        }
    }
} 