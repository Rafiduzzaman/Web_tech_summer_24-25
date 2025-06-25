<!-- view/auth/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - TaskMaster</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4 text-center">Create an Account</h2>

    <form method="post" action="?page=register">

      <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" name="name" 
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required />
      </div>

      <button type="submit" class="btn btn-primary w-100">Register</button>
      
    </form>
  </div>
</body>
</html>