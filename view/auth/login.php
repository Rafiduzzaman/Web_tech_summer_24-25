<?php
if (isset($_GET['page']) && $_GET['page'] === 'register') {
    header('Location: controller/AuthController.php?page=register');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - TaskMaster</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f5f7fa;
    }
    .login-container {
      max-width: 400px;
      margin: 80px auto;
      padding: 40px;
      background-color: #ffffff;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .form-control:focus {
      box-shadow: none;
      border-color: #2575fc;
    }
    .form-check-input:checked {
      background-color: #2575fc;
      border-color: #2575fc;
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="login-container">
      <h2 class="text-center mb-4">Login to TaskMaster</h2>

      <!-- Flash Message Placeholder -->
      <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"> <?= $error ?> </div>
      <?php endif; ?>

      <form action="?page=login" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="remember" name="remember">
          <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>

        <div class="mt-3 text-center">
          <a href="/forgot-password">Forgot Password?</a>
        </div>

        <div class="mt-2 text-center">
  Don’t have an account? <a href="?page=register">Sign up</a>
</div>

      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
