<?php
// Database configuration
$host = 'localhost';
$db   = 'user_management';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Create connection without specifying database first
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Now continue with PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['age'])) {
    $name = $_POST['name'];
    $age = (int)$_POST['age'];
    
    $stmt = $pdo->prepare("INSERT INTO users (name, age) VALUES (?, ?)");
    $stmt->execute([$name, $age]);
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle status toggle (AJAX will call this)
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['id'];
    $newStatus = (int)$_GET['new_status'];
    
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    
    echo json_encode(['success' => true]);
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $id = (int)$_POST['id'];
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
    exit();
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
            min-height: 100vh;
            color: #f0f0f0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            background: rgba(15, 15, 15, 0.8);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.6);
            overflow: hidden;
            border: 1px solid rgba(120, 90, 40, 0.3);
            backdrop-filter: blur(10px);
            position: relative;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #c19b52, #d4af37);
        }
        
        header {
            text-align: center;
            padding: 40px 30px 20px;
            position: relative;
        }
        
        h1 {
            font-size: 3.5rem;
            margin-bottom: 10px;
            background: linear-gradient(to right, #d4af37, #f9e076, #d4af37);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(212, 175, 55, 0.3);
            letter-spacing: 1px;
        }
        
        .subtitle {
            color: #b0b0b0;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.5), transparent);
            margin: 25px 0;
        }
        
        .form-container {
            padding: 0 30px 30px;
        }
        
        .form-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            color: #d4af37;
            font-size: 1.5rem;
        }
        
        .form-title i {
            font-size: 1.8rem;
        }
        
        #userForm {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 250px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: #d4af37;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(30, 30, 30, 0.8);
            border: 1px solid rgba(100, 100, 100, 0.3);
            border-radius: 10px;
            color: #f0f0f0;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        
        button {
            background: linear-gradient(135deg, #d4af37 0%, #c19b52 100%);
            color: #1a1a1a;
            border: none;
            padding: 16px 35px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            align-self: flex-end;
            min-width: 150px;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }
        
        .table-container {
            padding: 0 30px 40px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(20, 20, 20, 0.6);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
        
        thead {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.7) 0%, rgba(193, 155, 82, 0.7) 100%);
            color: #1a1a1a;
        }
        
        th {
            padding: 20px;
            text-align: left;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        tbody tr {
            border-bottom: 1px solid rgba(80, 80, 80, 0.3);
            transition: all 0.3s ease;
        }
        
        tbody tr:hover {
            background: rgba(40, 40, 40, 0.5);
        }
        
        td {
            padding: 18px 20px;
            font-size: 1.05rem;
        }
        
        .status-toggle {
            display: inline-block;
            position: relative;
            width: 70px;
            height: 34px;
        }
        
        .status-toggle input {
            display: none;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e74c3c;
            transition: .4s;
            border-radius: 34px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        input:checked + .slider {
            background-color: #2ecc71;
        }
        
        input:checked + .slider:before {
            transform: translateX(35px);
        }
        
        .status-text {
            display: inline-block;
            margin-left: 15px;
            font-weight: 500;
            min-width: 80px;
        }
        
        .status-active {
            color: #2ecc71;
        }
        
        .status-inactive {
            color: #e74c3c;
        }
        
        .actions {
            display: flex;
            gap: 12px;
        }
        
        .toggle-container {
            display: flex;
            align-items: center;
        }
        
        .delete-btn {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            padding: 10px 18px;
            min-width: auto;
            font-size: 0.95rem;
            color: white;
        }
        
        .delete-btn:hover {
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        
        .gold-accent {
            color: #d4af37;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 5rem;
            margin-bottom: 20px;
            color: rgba(212, 175, 55, 0.3);
        }
        
        .empty-state h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #d4af37;
        }
        
        .empty-state p {
            font-size: 1.2rem;
            color: #b0b0b0;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .logo {
            position: absolute;
            top: 25px;
            right: 30px;
            font-size: 1.8rem;
            color: #d4af37;
            font-weight: bold;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-count {
            display: inline-block;
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 1rem;
            margin-left: 15px;
        }
        
        footer {
            text-align: center;
            padding: 25px;
            color: #888;
            font-size: 0.9rem;
            border-top: 1px solid rgba(80, 80, 80, 0.3);
        }
        
        @media (max-width: 768px) {
            #userForm {
                flex-direction: column;
            }
            
            button {
                width: 100%;
            }
            
            h1 {
                font-size: 2.5rem;
            }
            
            .logo {
                position: relative;
                top: 0;
                right: 0;
                justify-content: center;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                
                
                <span class="user-count">Users: <?= count($users) ?></span>
            </div>
            <h1>User Management System</h1>
            <p class="subtitle"></p>
        </header>
        
        <div class="divider"></div>
        
        <div class="form-container">
            <div class="form-title">
                <i class="fas fa-user-plus gold-accent"></i>
                <h2>Add New User</h2>
            </div>
            <form method="POST" id="userForm">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user gold-accent"></i> Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label for="age"><i class="fas fa-birthday-cake gold-accent"></i> Age</label>
                    <input type="number" id="age" name="age" placeholder="Enter age" min="1" max="120" required>
                </div>
                <button type="submit">
                    <i class="fas fa-plus-circle"></i> Add User
                </button>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <h3>No Users Found</h3>
                                    <p>Add your first  user using the form above</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr data-id="<?= $user['id'] ?>">
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= $user['age'] ?></td>
                            <td>
                                <div class="toggle-container">
                                    <label class="status-toggle">
                                        <input type="checkbox" <?= $user['status'] ? 'checked' : '' ?> 
                                            onchange="toggleStatus(<?= $user['id'] ?>, this.checked)">
                                        <span class="slider"></span>
                                    </label>
                                    <span class="status-text <?= $user['status'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $user['status'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="actions">
                                <button class="delete-btn" onclick="deleteUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <footer>
            <p>© <?= date('Y') ?> </p>
        </footer>
    </div>

    <script>
        // Toggle user status
        function toggleStatus(userId, newStatus) {
            const statusValue = newStatus ? 1 : 0;
            
            // Send AJAX request to update status
            fetch(`?toggle_status=1&id=${userId}&new_status=${statusValue}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the status text immediately
                        const row = document.querySelector(`tr[data-id="${userId}"]`);
                        if (row) {
                            const statusText = row.querySelector('.status-text');
                            if (statusText) {
                                statusText.textContent = newStatus ? 'Active' : 'Inactive';
                                statusText.className = `status-text ${newStatus ? 'status-active' : 'status-inactive'}`;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated!',
                            text: `User status changed to ${newStatus ? 'Active' : 'Inactive'}`,
                            background: '#1a1a1a',
                            color: '#f0f0f0',
                            confirmButtonColor: '#d4af37',
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Failed to update user status',
                            background: '#1a1a1a',
                            color: '#f0f0f0',
                            confirmButtonColor: '#d4af37'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred: ' + error.message,
                        background: '#1a1a1a',
                        color: '#f0f0f0',
                        confirmButtonColor: '#d4af37'
                    });
                });
        }
        
        // Delete user
        function deleteUser(userId) {
            Swal.fire({
                title: 'Confirm Deletion',
                text: `Are you sure you want to delete user #${userId}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d4af37',
                cancelButtonColor: '#e74c3c',
                confirmButtonText: 'Yes, delete',
                background: '#1a1a1a',
                color: '#f0f0f0'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete user
                    const formData = new FormData();
                    formData.append('delete_user', 'true');
                    formData.append('id', userId);
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the row from the table
                            const row = document.querySelector(`tr[data-id="${userId}"]`);
                            if (row) {
                                row.remove();
                                
                                // Update user count
                                const userCount = document.querySelector('.user-count');
                                const userRows = document.querySelectorAll('#userTableBody tr[data-id]');
                                userCount.textContent = `Users: ${userRows.length}`;
                                
                                // If no users left, show empty state
                                if (userRows.length === 0) {
                                    userTableBody.innerHTML = `
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state">
                                                    <i class="fas fa-users-slash"></i>
                                                    <h3>No Users Found</h3>
                                                    <p>Add your first premium user using the form above</p>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                }
                            }
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'User has been removed from the system.',
                                background: '#1a1a1a',
                                color: '#f0f0f0',
                                confirmButtonColor: '#d4af37',
                                timer: 2000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Deletion Failed',
                                text: 'Failed to delete user',
                                background: '#1a1a1a',
                                color: '#f0f0f0',
                                confirmButtonColor: '#d4af37'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred: ' + error.message,
                            background: '#1a1a1a',
                            color: '#f0f0f0',
                            confirmButtonColor: '#d4af37'
                        });
                    });
                }
            });
        }
        
        // Handle form submission feedback
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])): ?>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'User Added!',
                    text: 'User has been added to the system',
                    background: '#1a1a1a',
                    color: '#f0f0f0',
                    confirmButtonColor: '#d4af37',
                    timer: 2000
                });
            });
        <?php endif; ?>
    </script>
</body>
</html>