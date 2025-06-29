<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - TaskMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .contact-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #2575fc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="contact-container">
            <h2 class="text-center mb-4">Contact Us</h2>
            <p class="text-center mb-4">Have questions or feedback? We'd love to hear from you!</p>

            <?php if (!empty($_SESSION['contact_success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['contact_success'] ?>
                </div>
                <?php unset($_SESSION['contact_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['contact_errors'])): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($_SESSION['contact_errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['contact_errors']); ?>
            <?php endif; ?>

            <form action="?page=submit-contact" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($_SESSION['contact_data']['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($_SESSION['contact_data']['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject *</label>
                    <input type="text" class="form-control" id="subject" name="subject" 
                           value="<?= htmlspecialchars($_SESSION['contact_data']['subject'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message *</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required><?= htmlspecialchars($_SESSION['contact_data']['message'] ?? '') ?></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-4">Send Message</button>
                    <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary px-4 ms-2">Back to Home</a>
                </div>
            </form>

            <?php unset($_SESSION['contact_data']); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>