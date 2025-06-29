<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/Web_tech_spring_24-25/');
define('BASE_PATH', __DIR__ . '/');

// Include all model files
require_once BASE_PATH . 'model/User.php';
require_once BASE_PATH . 'model/Task.php';
require_once BASE_PATH . 'model/Notification.php';
require_once BASE_PATH . 'model/ActivityLog.php';
require_once BASE_PATH . 'model/Profile.php';

$publicPages = ['login', 'register', 'index', 'contact', 'forgot-password', 'reset-password']; // Add public pages

// Default to showing landing page if no specific page requested
$page = $_GET['page'] ?? 'index';

// Only redirect to login if trying to access protected pages while not logged in
if (!isset($_SESSION['user']) && !in_array($page, $publicPages)) {
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

switch ($page) {
    case 'index':
        // Show the landing page HTML that was at the bottom of your file
        break;
        
    case 'login':
    case 'register':
        require BASE_PATH . 'controller/AuthController.php';
        break;
        
    case 'forgot-password':
        require BASE_PATH . 'controller/AuthController.php';
        // Handle forgot password
        break;
        
    case 'reset-password':
        require BASE_PATH . 'controller/AuthController.php';
        // Handle password reset
        break;
        
    case 'dashboard':
        require BASE_PATH . 'view/dashboard.php';
        break;
        
    case 'profile':
        require BASE_PATH . 'controller/ProfileController.php';
        ProfileController::show();
        break;
        
    case 'profile-update':
        require BASE_PATH . 'controller/ProfileController.php';
        ProfileController::update();
        break;
        
    case 'profile-change-password':
        require BASE_PATH . 'controller/ProfileController.php';
        ProfileController::changePassword();
        break;
        
    case 'update-avatar':
        require BASE_PATH . 'controller/ProfileController.php';
        ProfileController::updateAvatar();
        break;
        
    // Task Management Routes
    case 'tasks':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::showAll();
        break;
    
    case 'create-task':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::create();
        break;
        
    case 'task-details':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::showDetails();
        break;
        
    case 'edit-task':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::update();
        break;
        
    case 'delete-task':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::delete();
        break;
        
    case 'update-task-status':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::updateStatus();
        break;
        
    case 'add-subtask':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::addSubtask();
        break;
        
    case 'toggle-subtask':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::toggleSubtask();
        break;
        
    case 'share-task':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::shareTask();
        break;
        
    case 'search-tasks':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::search();
        break;
        
    case 'task-calendar':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::calendar();
        break;
        
    case 'export-tasks':
        require BASE_PATH . 'controller/TaskController.php';
        TaskController::export();
        break;
        
    // Admin Panel Routes
    case 'admin-dashboard':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::dashboard();
        break;
        
    case 'admin-users':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::userManagement();
        break;
        
    case 'admin-update-user-role':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::updateUserRole();
        break;
        
    case 'admin-delete-user':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::deleteUser();
        break;
        
    case 'admin-activity-logs':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::activityLogs();
        break;
        
    case 'admin-export-logs':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::exportLogs();
        break;
        
    case 'admin-settings':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::systemSettings();
        break;
        
    case 'admin-contact':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::contactSubmissions();
        break;
        
    case 'admin-update-submission':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::updateSubmissionStatus();
        break;
        
    case 'admin-delete-submission':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::deleteSubmission();
        break;
        
    case 'admin-notifications':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::notifications();
        break;
        
    case 'admin-delete-notification':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::deleteNotification();
        break;
        
    case 'admin-cleanup':
        require BASE_PATH . 'controller/AdminController.php';
        AdminController::cleanupOldData();
        break;
        
    // Notification Routes
    case 'notifications':
        require BASE_PATH . 'view/notifications.php';
        break;
        
    case 'mark-notification-read':
        require BASE_PATH . 'controller/NotificationController.php';
        NotificationController::markAsRead();
        break;
        
    case 'mark-all-notifications-read':
        require BASE_PATH . 'controller/NotificationController.php';
        NotificationController::markAllAsRead();
        break;
        
    // Contact Form
    case 'contact':
        require BASE_PATH . 'controller/ContactController.php';
        ContactController::show();
        break;
        
    case 'submit-contact':
        require BASE_PATH . 'controller/ContactController.php';
        ContactController::submit();
        break;
        
    // Logout
    case 'logout':
        session_destroy();
        header('Location: ' . BASE_URL . '?page=login');
        exit;
        
    default:
        http_response_code(404);
        require BASE_PATH . 'view/errors/404.php';
        exit;
}

// Only exit if we've handled a controller action
if ($page !== 'index') {
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TaskMaster - Manage Your Tasks Efficiently</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .hero {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      padding: 4rem 1rem;
      text-align: center;
    }
    .features-section {
      padding: 3rem 1rem;
    }
    .feature-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s;
      margin-bottom: 1rem;
    }
    .feature-card:hover {
      transform: translateY(-5px);
    }
    footer {
      padding: 2rem 0;
      background-color: #f8f9fa;
      text-align: center;
    }
    .navbar-toggler {
      border: none;
    }
    .btn-get-started {
      margin: 1rem 0;
    }
    @media (max-width: 768px) {
      .hero {
        padding: 3rem 1rem;
      }
      .display-4 {
        font-size: 2.5rem;
      }
      .lead {
        font-size: 1.25rem;
      }
      .features-section {
        padding: 2rem 1rem;
      }
      .feature-card {
        margin-bottom: 1.5rem;
      }
    }
    @media (max-width: 576px) {
      .hero {
        padding: 2.5rem 1rem;
      }
      .display-4 {
        font-size: 2rem;
      }
      .lead {
        font-size: 1.1rem;
      }
      .btn-get-started {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="#">TaskMaster</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="?page=contact">Contact</a></li>
          <li class="nav-item"><a class="btn btn-primary ms-lg-2 mt-2 mt-lg-0" href="?page=login">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero">
    <div class="container">
      <h1 class="display-4 mb-3">Simplify Your Productivity</h1>
      <p class="lead mb-4">Organize tasks, set priorities, and track progress with TaskMaster.</p>
      <a href="?page=login" class="btn btn-light btn-lg btn-get-started">Get Started - It's Free</a>
    </div>
  </header>

  <section id="features" class="features-section">
    <div class="container">
      <div class="text-center mb-5">
        <h2>Powerful Features for Smarter Task Management</h2>
      </div>
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="card feature-card p-4 h-100">
            <h5 class="mb-3">Create & Prioritize Tasks</h5>
            <p class="mb-0">Easily add new tasks and assign priority levels to stay on track.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card feature-card p-4 h-100">
            <h5 class="mb-3">Calendar Integration</h5>
            <p class="mb-0">Visualize tasks in a calendar view and manage deadlines efficiently.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card feature-card p-4 h-100">
            <h5 class="mb-3">Collaborate in Real-Time</h5>
            <p class="mb-0">Share tasks with your team and stay synced across all devices.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card feature-card p-4 h-100">
            <h5 class="mb-3">Subtasks & Progress Tracking</h5>
            <p class="mb-0">Break down complex tasks into manageable subtasks and track completion.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card feature-card p-4 h-100">
            <h5 class="mb-3">File Attachments</h5>
            <p class="mb-0">Attach documents and files to your tasks for better organization.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card feature-card p-4 h-100">
            <h5 class="mb-3">Smart Notifications</h5>
            <p class="mb-0">Get timely reminders and notifications for important deadlines.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="contact" class="py-5 bg-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2>Get in Touch</h2>
        <p class="lead">Have questions? We'd love to hear from you.</p>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-body p-4">
              <form action="?page=submit-contact" method="POST">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="subject" class="form-label">Subject</label>
                  <input type="text" class="form-control" id="subject" name="subject" required>
                </div>
                <div class="mb-3">
                  <label for="message" class="form-label">Message</label>
                  <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5>TaskMaster</h5>
          <p>Efficient task management for teams and individuals.</p>
        </div>
        <div class="col-md-6 text-md-end">
          <p>&copy; 2024 TaskMaster. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>