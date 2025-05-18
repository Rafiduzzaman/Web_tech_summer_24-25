<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-form {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 300px;
    }

    .login-form h2 {
      margin-bottom: 1rem;
      text-align: center;
    }

    .login-form label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
    }

    .login-form input[type="text"],
    .login-form input[type="password"] {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .login-form input[type="submit"] {
      width: 100%;
      padding: 0.6rem;
      background: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }

    .login-form input[type="submit"]:hover {
      background: #0056b3;
    }

    .error-message {
      color: red;
      margin-bottom: 1rem;
      text-align: center;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

  <form class="login-form" method="post" action="../logCheck.php">
    <h2>Login</h2>

    

    <label for="id">User ID</label>
    <input type="text" name="id" id="id">

    <label for="pass">Password</label>
    <input type="password" name="pass" id="pass">
    <?php
      if (isset($_SESSION['error'])) {
          echo '<div class="error-message">'.$_SESSION['error'].'</div>';
          unset($_SESSION['error']);
      }
    ?>
    <input type="submit" name="submit" value="Login">
  </form>

</body>
</html>
