<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Your App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .admin-nav {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .admin-nav a {
            padding: 8px 15px;
            background: #e9ecef;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .admin-nav a.active {
            background: #4285f4;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
        }
        .badge-admin { background: #dc3545; color: white; }
        .badge-editor { background: #fd7e14; color: white; }
        .badge-user { background: #28a745; color: white; }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Admin Panel</h1>
        <div>
            <a href="/dashboard" class="btn">Back to Dashboard</a>
        </div>
    </div>

    <div class="admin-nav">
        <a href="/admin/users" class="active">User Management</a>
        <a href="/admin/settings">System Settings</a>
        <a href="/admin/audit">Audit Logs</a>
    </div>

    <div class="search-bar">
        <input type="text" placeholder="Search users..." id="userSearch">
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <span class="badge badge-<?php echo strtolower($user['role']); ?>">
                        <?php echo htmlspecialchars($user['role']); ?>
                    </span>
                </td>
                <td>
                    <select onchange="handleAction(this, <?php echo $user['id']; ?>)">
                        <option value="">Select action</option>
                        <option value="edit">Edit</option>
                        <option value="role">Change Role</option>
                        <option value="delete">Delete</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function handleAction(selectElement, userId) {
            const action = selectElement.value;
            if (!action) return;
            
            switch(action) {
                case 'edit':
                    window.location.href = `/admin/users/${userId}/edit`;
                    break;
                case 'role':
                    const newRole = prompt('Enter new role (Admin/Editor/User):');
                    if (newRole) {
                        // AJAX call to update role would go here
                        console.log(`Changing user ${userId} to ${newRole}`);
                    }
                    break;
                case 'delete':
                    if (confirm('Are you sure?')) {
                        // AJAX call to delete would go here
                        console.log(`Deleting user ${userId}`);
                    }
                    break;
            }
            selectElement.value = ''; // Reset dropdown
        }

        // Simple search functionality
        document.getElementById('userSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                row.style.display = (name.includes(searchTerm) || email.includes(searchTerm)) 
                    ? '' 
                    : 'none';
            });
        });
    </script>
</body>
</html>