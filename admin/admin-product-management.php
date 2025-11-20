<?php
// admin-product-management.php (FIXED: Removed premature $conn->close())

session_start();
// Include the connection file
include '../connection.php'; 

// =========================================================
// A. AUTHENTICATION & AUTHORIZATION CHECK (Security)
// =========================================================

if (!isset($_SESSION['username'])) {
    // FIX: Changed /customer_side/login.php to /customer_side/log-in.php based on file structure
    header("Location: /customer_side/log-in.php"); 
    exit();
}

$allowed_roles = ['admin'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: unauthorized.php"); 
    exit();
}

// =========================================================
// B. MESSAGE DISPLAY (galing sa product-action.php)
// =========================================================

$display_message = '';
$message_type = 'info';

if (isset($_SESSION['message'])) {
    $display_message = $_SESSION['message'];
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// =========================================================
// C. FETCH PRODUCT DATA 
// =========================================================

if ($conn) {
    $sql = "SELECT product_id, name AS product_name, pattern AS patterns, brand AS category, price, description, image FROM product_table ORDER BY product_id DESC";
    $result = $conn->query($sql);
    // ✅ FIX: Inalis ang $conn->close(); dito. Hayaan na lang isara ng PHP ang connection pagkatapos ng script.
} else {
    // If connection failed, set result to null/false to prevent HTML errors
    $result = false;
    $display_message = "Database connection failed. Cannot fetch products.";
    $message_type = 'error';
}
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
    <title>Admin Panel - Product Management</title>

    <style>
        /* CSS styles (same as before) */
        * { box-sizing: border-box; margin:0; padding:0; font-family:'Poppins', sans-serif;}
        body { background-color: #f0f0f0; padding: 20px; }

        h1 { margin-bottom: 20px; }
        main { margin-left: 270px; padding: 20px; } 

        .table-container {
            overflow-x: auto;
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        th { background-color: #2563eb; color: white; }

        .side-bar {
            font-family: var(--primary-font);
            width: 250px;
            height: 100vh;
            background-color: #ffffff;
            color: rgb(0, 0, 0);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            gap: 3rem;
            align-items: center;
            padding-top: 20px;
            border-right: 2px solid black;
            transition: transform 0.3s ease;
        }

        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none; 
            display: inline-block;
            margin-right: 5px;
        }

        .edit-btn { background-color: #0025f7; color: white; }
        .delete-btn { background-color: #f70000; color: white; }
        .edit-btn:hover { background-color: #2646ff; }
        .delete-btn:hover { background-color: #dc2626; }

        .form-card {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
            padding: 20px 0;
        }

        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        label { font-weight: 500; margin-bottom: 5px; }
        input, select, textarea { padding: 10px; border-radius:5px; border:1px solid #ccc; width: 100%; }
        .submit-btn { background-color:#2563eb; color:white; }
        .submit-btn:hover { background-color:#1e4bb8; }
    </style>
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
        <h1>Product Management</h1>

        <?php if (!empty($display_message)): ?>
            <?php 
                $bg_color = ($message_type == 'success') ? '#d4edda' : ($message_type == 'error' ? '#f8d7da' : '#fff3cd');
                $text_color = ($message_type == 'success') ? '#155724' : ($message_type == 'error' ? '#721c24' : '#856404');
                $border_color = ($message_type == 'success') ? '#c3e6cb' : ($message_type == 'error' ? '#f5c6cb' : '#ffeeba');
            ?>
            <div id="status-message" style="
                background-color: <?= $bg_color; ?>; color: <?= $text_color; ?>; padding: 10px; 
                border: 1px solid <?= $border_color; ?>; margin: 10px auto 20px 0; border-radius: 5px;
                text-align: center; font-weight: bold; max-width: 800px;
            ">
                <?= htmlspecialchars($display_message); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Patterns</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($result) && $result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['product_id']; ?></td>
                                <td><?= htmlspecialchars($row['product_name']); ?></td>
                                <td><?= htmlspecialchars($row['patterns']); ?></td>
                                <td><?= htmlspecialchars($row['category']); ?></td>
                                <td><?= $row['price']; ?></td>
                                <td><?= htmlspecialchars($row['description']); ?></td>
                                <td>
                                    <a class="edit-btn" href="product-update-form.php?id=<?= $row['product_id']; ?>">Edit</a>
                                    <a class="delete-btn" href="product-action.php?delete_id=<?= $row['product_id']; ?>" 
                                        onclick="return confirm('Are you sure you want to delete product ID: <?= $row['product_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="form-card">
            <form class="form-container" action="product-action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="">

                <label>Product Name:</label>
                <input type="text" name="name" required>
                
                <label>Pattern:</label>
                <input type="text" name="pattern" placeholder="e.g., HT, AT, All-Season">

                <label>Select Category:</label>
                <select name="brand" required>
                    <option value="" disabled selected>Select a Category</option>
                    <option value="Tires">Tires</option>
                    <option value="Battery">Battery</option>
                    <option value="Other_Services">Other Services</option>
                </select>

                <label>Price:</label>
                <input type="number" name="price" step="0.01" required>

                <label>Description:</label>
                <textarea name="description" rows="3"></textarea>

                <label>Product Image:</label>
                <input type="file" name="dish_image" accept="image/*">
                
                <button type="submit" class="submit-btn">Add Product</button>
            </form>
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