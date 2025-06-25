<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Your App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .contact-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .captcha {
            background: #eee;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Contact Us</h1>
    <div class="contact-form">
        <form action="/contact/submit" method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <div class="captcha">
                <p>What is 2 + 3? (Anti-spam)</p>
                <input type="text" name="captcha" required>
            </div>
            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>