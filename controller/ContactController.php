<?php
class ContactController {
    public static function show() {
        require BASE_PATH . 'view/contact.php';
    }

    public static function submit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            // Validation
            $errors = [];
            if (empty($name)) $errors[] = 'Name is required';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
            if (empty($subject)) $errors[] = 'Subject is required';
            if (empty($message)) $errors[] = 'Message is required';
            
            if (!empty($errors)) {
                $_SESSION['contact_errors'] = $errors;
                $_SESSION['contact_data'] = $_POST;
                header('Location: ' . BASE_URL . '?page=contact');
                exit();
            }
            
            // Save to database
            $db = new mysqli('localhost', 'root', '', 'webtech');
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $stmt = $db->prepare("INSERT INTO contact_submissions (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $subject, $message, $ipAddress);
            
            if ($stmt->execute()) {
                // Send email notification to admin (optional)
                self::sendEmailNotification($name, $email, $subject, $message);
                
                $_SESSION['contact_success'] = 'Thank you for your message! We will get back to you soon.';
                header('Location: ' . BASE_URL . '?page=contact');
            } else {
                $_SESSION['contact_errors'] = ['Failed to submit message. Please try again.'];
                $_SESSION['contact_data'] = $_POST;
                header('Location: ' . BASE_URL . '?page=contact');
            }
            exit();
        }
        
        // If not POST, redirect to contact page
        header('Location: ' . BASE_URL . '?page=contact');
        exit();
    }
    
    private static function sendEmailNotification($name, $email, $subject, $message) {
        // This is a basic email notification
        // In production, you would use a proper email library like PHPMailer
        
        $to = 'admin@taskmaster.com'; // Replace with your admin email
        $emailSubject = "New Contact Form Submission: $subject";
        $emailBody = "
        New contact form submission received:
        
        Name: $name
        Email: $email
        Subject: $subject
        
        Message:
        $message
        
        ---
        This message was sent from the TaskMaster contact form.
        ";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Uncomment the line below to actually send emails (requires proper email configuration)
        // mail($to, $emailSubject, $emailBody, $headers);
    }
} 