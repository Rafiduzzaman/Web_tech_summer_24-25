<?php
if (isset($_GET['page']) && $_GET['page'] === 'login') {
    header('Location: controller/AuthController.php?page=login');
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
      padding: 80px 20px;
      text-align: center;
    }
    .features-section {
      padding: 60px 20px;
    }
    .feature-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s;
    }
    .feature-card:hover {
      transform: translateY(-5px);
    }
    footer {
      padding: 20px 0;
      background-color: #f8f9fa;
      text-align: center;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="#">TaskMaster</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item"><a class="btn btn-primary" href="?page=login">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero">
    <div class="container">
      <h1 class="display-4">Simplify Your Productivity</h1>
      <p class="lead">Organize tasks, set priorities, and track progress with TaskMaster.</p>
      <a href="/register" class="btn btn-lg btn-light">Get Started - It's Free</a>
    </div>
  </header>

  <section id="features" class="features-section container">
    <div class="text-center mb-5">
      <h2>Powerful Features for Smarter Task Management</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card feature-card p-4 h-100">
          <h5>Create & Prioritize Tasks</h5>
          <p>Easily add new tasks and assign priority levels to stay on track.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card feature-card p-4 h-100">
          <h5>Calendar Integration</h5>
          <p>Visualize tasks in a calendar view and manage deadlines efficiently.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card feature-card p-4 h-100">
          <h5>Collaborate in Real-Time</h5>
          <p>Share tasks with your team and stay synced across all devices.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="contact" class="py-5 bg-light">
    <div class="container">
      <div class="text-center mb-4">
        <h2>Contact Us</h2>
        <p>Have questions or feedback? We'd love to hear from you!</p>
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
            <button type="submit" class="btn btn-primary px-4">Send Message</button>
          </div>
        </div>
      </form>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 TaskMaster. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
