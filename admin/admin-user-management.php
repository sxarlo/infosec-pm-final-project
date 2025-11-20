<?php
// admin-user-management.php

session_start();

require "../connection.php"; // Tiyakin ang tamang path

// =========================================================
// A. AUTHENTICATION & AUTHORIZATION CHECK (CRITICAL!)
// =========================================================
if (!isset($_SESSION['username'])) {
    header("Location: /customer_side/login.php"); 
    exit();
}

$allowed_roles = ['admin'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: unauthorized.php"); 
    exit();
}

// =========================================================
// B. MESSAGE INITIALIZATION
// =========================================================

$display_message = '';
$message_type = 'info';

// =========================================================
// C. UPDATE LOGIC (Handle POST request mula sa Update Form)
// =========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    
    $user_id  = $_POST['user_id'];
    $username = $_POST['username'];
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    $sql_update = "UPDATE users_table SET username=?, role=?, status=? WHERE user_id=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssi", $username, $role, $status, $user_id);

    if ($stmt_update->execute()) {
        $display_message = "User ID: {$user_id} updated successfully!";
        $message_type = 'success';
    } else {
        $display_message = "Error updating user: " . $stmt_update->error;
        $message_type = 'error';
    }

    $stmt_update->close();
}

// =========================================================
// D. DELETE MESSAGE (Galing sa delete-user.php)
// =========================================================

if (isset($_SESSION['delete_message'])) {
    $display_message = $_SESSION['delete_message'];
    $message_type = (strpos($display_message, 'Error') === false) ? 'success' : 'error';
    unset($_SESSION['delete_message']); 
} 

// =========================================================
// E. FETCH DATA LOGIC (Para sa User Logs Table)
// =========================================================

$sql = "SELECT user_id, username, role, status FROM users_table ORDER BY user_id ASC";
$result = $conn->query($sql);

$conn->close(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="admin-user-management.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&family=Nabla&family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Orbitron:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Sansation:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Zen+Dots&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
</head>
<body>

    <header>
        <input type="checkbox" id="sidebar-toggle">
        <label for="sidebar-toggle" class="hamburger">&#9776;</label>
        
        <div class="side-bar">
            <h2>Admin Panel</h2>
            <ul>
                <?php if (isset($_SESSION['role'])): ?>
                    <li class="welcome">
                        Welcome, 
                        <?php
                            switch($_SESSION['role']){
                                case 'admin': echo 'Admin'; break;
                                case 'employee': echo 'Employee'; break;
                                case 'customer': echo 'Customer'; break;
                                default: echo 'User';
                            }
                        ?>!
                    </li>
                <?php endif; ?>

                <li><a href="admin-panel.php">Dashboard</a></li>
                <li><a href="admin-user-management.php">User Management</a></li>
                <li><a href="admin-product-management.php">Product Management</a></li>
                <li><a href="admin-reports.php">Reports</a></li>
                <li><a href="admin-settings.php">Settings</a></li>

                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="/customer_side/log-out.php" class="logout-link">Log Out</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <main>
        <h1>User Logs</h1>
        
        <?php if (!empty($display_message)): ?>
            <?php 
                // Set colors based on message type
                $bg_color = ($message_type == 'success') ? '#d4edda' : ($message_type == 'error' ? '#f8d7da' : '#fff3cd');
                $text_color = ($message_type == 'success') ? '#155724' : ($message_type == 'error' ? '#721c24' : '#856404');
                $border_color = ($message_type == 'success') ? '#c3e6cb' : ($message_type == 'error' ? '#f5c6cb' : '#ffeeba');
            ?>
            <div id="status-message" style="
                background-color: <?= $bg_color; ?>; color: <?= $text_color; ?>; padding: 10px; 
                border: 1px solid <?= $border_color; ?>; margin: 10px auto 20px auto; border-radius: 5px;
                text-align: center; font-weight: bold; max-width: 800px;
            ">
                <?= htmlspecialchars($display_message); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['user_id']; ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= $row['role']; ?></td>
                                <td><?= $row['status']; ?></td>
                                <td class="action-btn">
                                    <a class="update-btn" href="update-form.php?user_id=<?= $row['user_id']; ?>">Edit</a>
                                    <a class="delete-btn" href="delete-user.php?user_id=<?= $row['user_id']; ?>"
                                    onclick="return confirm('Are you sure you want to delete user ID: <?= $row['user_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const messageDiv = document.getElementById('status-message');

        if (messageDiv) {
            // Itatago ang message pagkatapos ng 4000 milliseconds (4 seconds)
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 4000); 
        }
    </script>
</body>
</html>