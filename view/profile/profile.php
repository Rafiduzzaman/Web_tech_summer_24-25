<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Your App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #ddd;
        }
        .section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4285f4;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .tab-container {
            display: flex;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #ddd;
            background: #f5f5f5;
        }
        .tab.active {
            background: white;
            border-bottom: 2px solid #4285f4;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="profile-header">
        <img src="/assets/default-avatar.jpg" alt="Profile Picture" class="avatar" id="avatar-preview">
        <h1><?php echo htmlspecialchars($user['name'] ?? 'User Profile'); ?></h1>
    </div>

    <div class="tab-container">
        <div class="tab active" onclick="switchTab('view')">View Profile</div>
        <div class="tab" onclick="switchTab('edit')">Edit Profile</div>
        <div class="tab" onclick="switchTab('password')">Change Password</div>
    </div>

    <!-- View Profile Tab -->
    <div class="tab-content active" id="view-tab">
        <div class="section">
            <h2>Personal Information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name'] ?? 'Not set'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></p>
            <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($user['created_at'] ?? 'now')); ?></p>
        </div>
    </div>

    <!-- Edit Profile Tab -->
    <div class="tab-content" id="edit-tab">
        <div class="section">
            <h2>Edit Profile</h2>
            <form action="/profile/update" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="avatar">Profile Picture</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                </div>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Change Password Tab -->
    <div class="tab-content" id="password-tab">
        <div class="section">
            <h2>Change Password</h2>
            <form action="/profile/change-password" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Update Password</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Update tab styling
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }

        // Preview avatar when selected
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('avatar-preview').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>