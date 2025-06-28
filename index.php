<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/Web_tech_spring_24-25/');
define('BASE_PATH', __DIR__ . '/');

$publicPages = ['login', 'register', 'index']; // Add 'index' to public pages

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
    // Add to your switch statement
case 'tasks':
    require BASE_PATH . 'controller/TaskController.php';
    TaskController::showAll();
    break;
    
case 'create-task':
    require BASE_PATH . 'controller/TaskController.php';
    TaskController::create();
    break;
        
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
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
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
      </div>
    </div>
  </section>

  <section id="contact" class="py-5 bg-light">
    <div class="container">
      <div class="text-center mb-4">
        <h2>Contact Us</h2>
        <p class="mb-0">Have questions or feedback? We'd love to hear from you!</p>
      </div>
      <form method="post" action="/contact">
        <div class="row g-3">
          <div class="col-md-6">
            <input type="text" name="name" class="form-control" placeholder="Your Name" required />
          </div>
          <div class="col-md-6">
            <input type="email" name="email" class="form-control" placeholder="Your Email" required />
          </div>
          <div class="col-12">
            <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary px-4 py-2">Send Message</button>
          </div>
        </div>
      </form>
    </div>
  </section>

  <footer class="container">
    <p class="mb-0">&copy; 2025 TaskMaster. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>