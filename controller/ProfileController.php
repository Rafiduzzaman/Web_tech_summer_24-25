<?php
class ProfileController {
    public static function show() {
    if (!isset($_SESSION['user'])) {
        header("Location: " . BASE_URL . "?page=login");
        exit();
    }

    require_once BASE_PATH . 'model/User.php';
    $userModel = new User();
    
    // Get complete user data including avatar
    $user = $userModel->getUserById($_SESSION['user']['id']);
    
    if (!$user) {
        header("Location: " . BASE_URL . "?page=login");
        exit();
    }

    require BASE_PATH . 'view/profile/profile.php';
}

public static function updateAvatar() {
    if (!isset($_SESSION['user'])) {
        header("Location: " . BASE_URL . "?page=login");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
        require_once BASE_PATH . 'model/User.php';
        $userModel = new User();

        $uploadDir = BASE_PATH . 'assets/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file (2MB max, only images)
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if ($_FILES['avatar']['size'] > $maxFileSize) {
            header("Location: " . BASE_URL . "?page=profile&error=file_too_large");
            exit();
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fileInfo, $_FILES['avatar']['tmp_name']);
        
        if (!in_array($mime, $allowedTypes)) {
            header("Location: " . BASE_URL . "?page=profile&error=invalid_type");
            exit();
        }

        // Generate unique filename while preserving extension
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $_SESSION['user']['id'] . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
            // Update database and session
            if ($userModel->updateAvatar($_SESSION['user']['id'], $filename)) {
                $_SESSION['user']['avatar'] = $filename;
                header("Location: " . BASE_URL . "?page=profile&success=avatar_updated");
                exit();
            }
        }
    }

    header("Location: " . BASE_URL . "?page=profile&error=upload_failed");
    exit();
}

    public static function update() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        require_once BASE_PATH . 'model/Profile.php';
        $profile = new Profile($_SESSION['user']['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            
            if ($profile->updateProfile($name, $email)) {
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                
                if (!empty($_FILES['avatar']['name'])) {
                    $uploadDir = BASE_PATH . 'assets/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                        $profile->updateAvatar($fileName);
                        $_SESSION['user']['avatar'] = $fileName;
                    }
                }
                
                header("Location: " . BASE_URL . "?page=profile&success=1");
                exit();
            }
            
            header("Location: " . BASE_URL . "?page=profile&error=1");
            exit();
        }
    }

    public static function changePassword() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "?page=login");
            exit();
        }

        require_once BASE_PATH . 'model/Profile.php';
        $profile = new Profile($_SESSION['user']['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            
            if ($profile->changePassword($currentPassword, $newPassword)) {
                header("Location: " . BASE_URL . "?page=profile&pw_success=1");
                exit();
            }
            
            header("Location: " . BASE_URL . "?page=profile&pw_error=1");
            exit();
        }
    }
}