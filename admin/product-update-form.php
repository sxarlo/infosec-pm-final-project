<?php
// product_update_form.php

session_start();
include '../connection.php'; 

// =========================================================
// A. AUTHENTICATION & AUTHORIZATION CHECK 
// =========================================================

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php"); 
    exit();
}

// =========================================================
// B. FETCH EXISTING PRODUCT DATA
// =========================================================

$product_data = null;
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0 && $conn) {
    // 1. Prepare Statement
    $sql = "SELECT product_id, name, pattern, brand, price, description, image FROM product_table WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id); // 'i' for integer

    // 2. Execute and Fetch Result
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $product_data = $result->fetch_assoc();
        } else {
            // Product not found
            $_SESSION['message'] = "Error: Product ID {$product_id} not found.";
            $_SESSION['message_type'] = 'error';
            header("Location: admin-product-management.php");
            exit();
        }
    } else {
        // SQL execution error
        $_SESSION['message'] = "Database error: Could not fetch product details.";
        $_SESSION['message_type'] = 'error';
        header("Location: admin-product-management.php");
        exit();
    }
    $stmt->close();
    $conn->close();
} else {
    // Invalid ID or no connection
    $_SESSION['message'] = "Error: Invalid product ID provided or database connection failed.";
    $_SESSION['message_type'] = 'error';
    header("Location: admin-product-management.php");
    exit();
}

// Check if data was actually fetched before proceeding to HTML
if (!$product_data) {
    header("Location: admin-product-management.php");
    exit();
}

// Set form title
$form_title = "Edit Product: " . htmlspecialchars($product_data['name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="admin-user-management.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $form_title; ?></title>

    <style>
        /* Re-using styles from admin-product-management.php */
        * { box-sizing: border-box; margin:0; padding:0; font-family:'Poppins', sans-serif;}
        body { background-color: #f0f0f0; padding: 20px; }
        h1 { margin-bottom: 20px; }
        main { margin-left: 270px; padding: 20px; } 
        .form-card {
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Aligned to top, since main content is not center aligned */
            background-color: #f0f0f0;
            padding: 20px 0;
            min-height: 80vh;
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
        label { font-weight: 500; margin-bottom: 5px; display: block; }
        input, select, textarea { padding: 10px; border-radius:5px; border:1px solid #ccc; width: 100%; }
        .submit-btn { background-color:#2563eb; color:white; padding: 10px; border: none; cursor: pointer; }
        .submit-btn:hover { background-color:#1e4bb8; }
        .current-image {
            max-width: 150px;
            height: auto;
            display: block;
            margin-top: 10px;
            border: 1px solid #ccc;
            padding: 5px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #2563eb;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <main>
        <a href="admin-product-management.php" class="back-link">&larr; Back to Product List</a>
        <h1><?= $form_title; ?></h1>

        <div class="form-card">
            <form class="form-container" action="product-action.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="product_id" value="<?= $product_data['product_id']; ?>">

                <label>Product Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product_data['name']); ?>" required>
                
                <label>Pattern:</label>
                <input type="text" name="pattern" value="<?= htmlspecialchars($product_data['pattern']); ?>" placeholder="e.g., HT, AT, All-Season">

                <label>Select Category:</label>
                <select name="brand" required>
                    <?php 
                        $categories = ['Tires', 'Battery', 'Other_Services'];
                        $current_category = $product_data['brand'];
                    ?>
                    <option value="" disabled>Select a Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat; ?>" <?= ($cat === $current_category) ? 'selected' : ''; ?>>
                            <?= $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Price:</label>
                <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($product_data['price']); ?>" required>

                <label>Description:</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($product_data['description']); ?></textarea>

                <label>Current Product Image:</label>
                <?php if (!empty($product_data['image'])): ?>
                    <img src="../<?= htmlspecialchars($product_data['image']); ?>" alt="Current Product Image" class="current-image">
                    <p style="font-size: 0.85em; color: #555; margin-top: 5px;">Leave file field blank to keep current image.</p>
                <?php else: ?>
                    <p>No current image available.</p>
                <?php endif; ?>
                
                <label style="margin-top: 15px;">Change Product Image:</label>
                <input type="file" name="dish_image" accept="image/*">
                
                <button type="submit" class="submit-btn">Save Changes</button>
            </form>
        </div>
    </main>

</body>
</html>