<?php
session_start();

if (isset($_POST['submit'])) {
    $id = trim($_POST['id']);
    $pass = trim($_POST['pass']);

    if (empty($id) || empty($pass)) {
        $_SESSION['error'] = "ID or Password cannot be empty.";
        header("Location: view/loginView.php");
        exit();
    } else {
        // Here you can do real authentication logic
        $_SESSION['status'] = true;
        header("Location: home.php");
        exit();
    }
} else {
    header("Location: view/loginView.php");
    exit();
}
