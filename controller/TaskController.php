<?php
class TaskController {
    public static function create() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once BASE_PATH . 'model/Task.php';
            $taskModel = new Task($_SESSION['user']['id']);

            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $dueDate = $_POST['due_date'] ?? '';
            $priority = $_POST['priority'] ?? 'medium';
            $status = $_POST['status'] ?? 'pending';

            if ($taskModel->create($title, $description, $dueDate, $priority, $status)) {
                header("Location: " . BASE_URL . "?page=tasks&success=1");
            } else {
                header("Location: " . BASE_URL . "?page=tasks&error=1");
            }
            exit();
        }
    }

    public static function showAll() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        require_once BASE_PATH . 'model/Task.php';
        $taskModel = new Task($_SESSION['user']['id']);
        $tasks = $taskModel->getAll();

        require BASE_PATH . 'view/tasks/list.php';
    }

    // Add similar methods for update, delete, etc.
}