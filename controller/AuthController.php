<?php
require_once __DIR__ . '/../model/User.php';

// Start session at the beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Handle Login
if (isset($_GET['page']) && $_GET['page'] === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $userModel = new User();
        $user = $userModel->verifyCredentials($email, $password);
        
        if ($user) {
            // Login successful
            $_SESSION['user'] = $user;
            header('Location: /Web_tech_spring_24-25/?page=dashboard');
            exit;
        } else {
            // Login failed
            $error = "Invalid email or password";
            include __DIR__ . '/../view/auth/login.php';
            exit;
        }
    }
    
    // Show login form
    include __DIR__ . '/../view/auth/login.php';
    exit;
}
// Handle actions (login, register, logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Login handling
    if (isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['name'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $user = new User();
        $loggedInUser = $user->login($email, $password);
        
        if ($loggedInUser) {
            $_SESSION['user'] = $loggedInUser;
            header('Location: /Web_tech_spring_24-25/?page=dashboard');
            exit;
        } else {
            $error = "Invalid email or password";
            include __DIR__ . '/../view/auth/login.php';
            exit;
        }
    }
    // Registration handling
    elseif (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $errors = [];
        if (empty($name)) $errors[] = 'Full name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($password) || strlen($password) < 4) $errors[] = 'Password must be at least 4 characters';
        
        if (empty($errors)) {
            $user = new User();
            if ($user->register($name, $email, $password)) {
                header('Location: /Web_tech_spring_24-25/?page=login&registered=1');
                exit;
            } else {
                $errors[] = 'Registration failed. Email may already exist.';
            }
        }
        
        $registerErrors = $errors;
        include __DIR__ . '/../view/auth/register.php';
        exit;
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /Web_tech_spring_24-25/?page=login');
    exit;
}

// Show login/register pages
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'login':
            include __DIR__ . '/../view/auth/login.php';
            exit;
        case 'register':
            include __DIR__ . '/../view/auth/register.php';
            exit;
    }
}

// Default redirect if nothing matches
header('Location: /Web_tech_spring_24-25/?page=login');
exit;