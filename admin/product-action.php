<?php
// product-action.php (FINAL WORKING CODE with CREATE, UPDATE, and DELETE logic)

session_start();
require_once '../connection.php'; 

// --- 1. CONFIGURATION & SECURITY CHECK ---
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php"); 
    exit();
}

$db_path_prefix = "assets/images/"; 
$target_dir = __DIR__ . "/../assets/images/"; 
$redirect_page = "admin-product-management.php";


// ==========================================================
// A. HANDLE POST REQUEST (CREATE or UPDATE Logic)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : null;
    $name       = trim($_POST['name']); 
    $pattern    = $_POST['pattern'];
    $brand      = $_POST['brand'];
    $price      = $_POST['price']; 
    $description = trim($_POST['description']);
    
    $image_path_for_db = NULL; 
    $upload_ok = true;
    $is_updating_image = false;

    // --- FILE UPLOAD HANDLING ---
    if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === 0 && !empty($_FILES['dish_image']['name'])) {
        
        $is_updating_image = true; 
        
        if (!is_dir($target_dir)) {
            $_SESSION['message'] = "Error: Image folder 'assets/images/' does not exist. Check path: {$target_dir}";
            $_SESSION['message_type'] = 'error';
            $upload_ok = false;
        }
        
        if ($upload_ok) {
            $file_name = basename($_FILES["dish_image"]["name"]);
            $unique_filename = uniqid() . "_" . $file_name;
            $target_file_upload = $target_dir . $unique_filename; 
            $image_path_for_db = $db_path_prefix . $unique_filename; 
            
            $imageFileType = strtolower(pathinfo($target_file_upload, PATHINFO_EXTENSION));

            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                $_SESSION['message'] = "Error: Only JPG, JPEG, & PNG files are allowed for the image.";
                $_SESSION['message_type'] = 'error';
                $upload_ok = false;
            } 
            else if (!move_uploaded_file($_FILES["dish_image"]["tmp_name"], $target_file_upload)) {
                $_SESSION['message'] = "CRITICAL ERROR: Failed to move uploaded file. Check FOLDER PERMISSIONS.";
                $_SESSION['message_type'] = 'error';
                $upload_ok = false;
            }
            
            // ⭐ NEW: IF UPDATE & SUCCESSFUL UPLOAD: DELETE OLD IMAGE FILE
            if ($upload_ok && !empty($product_id)) {
                 $sql_fetch_old = "SELECT image FROM product_table WHERE product_id = ?";
                 $stmt_fetch_old = $conn->prepare($sql_fetch_old);
                 $stmt_fetch_old->bind_param("i", $product_id);
                 $stmt_fetch_old->execute();
                 $result_old = $stmt_fetch_old->get_result();
                 if ($row_old = $result_old->fetch_assoc()) {
                     $old_image_path = __DIR__ . "/../" . $row_old['image'];
                     // Tinitiyak na may path at existing ang file bago i-delete
                     if (file_exists($old_image_path) && !empty($row_old['image'])) {
                         unlink($old_image_path);
                     }
                 }
                 $stmt_fetch_old->close();
            }
        }
    } 

    // --- DATABASE OPERATION ---

    if ($upload_ok) { 
        
        if (empty($product_id)) { 
            // CREATE LOGIC
            $sql_query = "INSERT INTO product_table (name, pattern, brand, price, description, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_query);
            
            // FIX: 6 variables -> 6 strings ("ssssss")
            $stmt->bind_param("ssssss", $name, $pattern, $brand, $price, $description, $image_path_for_db); 
            
            $success_message = "Product '{$name}' added successfully!";
            $error_message = "Error adding product to database: " . $stmt->error;
            
        } else {
            // UPDATE LOGIC
            
            // Image Retention Logic (Only runs if no new image was uploaded)
            if (!$is_updating_image) {
                $sql_fetch_old_image = "SELECT image FROM product_table WHERE product_id = ?";
                $stmt_fetch = $conn->prepare($sql_fetch_old_image);
                $stmt_fetch->bind_param("i", $product_id);
                $stmt_fetch->execute();
                $result_fetch = $stmt_fetch->get_result();
                
                if ($row = $result_fetch->fetch_assoc()) {
                    $image_path_for_db = $row['image']; 
                }
                $stmt_fetch->close();
            }

            $sql_query = "UPDATE product_table SET name=?, pattern=?, brand=?, price=?, description=?, image=? WHERE product_id=?";
            $stmt = $conn->prepare($sql_query);
            
            // FIX: 7 variables -> ssssssi (6 strings + 1 integer). Solves ArgumentCountError.
            $stmt->bind_param("ssssssi", $name, $pattern, $brand, $price, $description, $image_path_for_db, $product_id); 
            
            $success_message = "Product ID {$product_id} updated successfully!";
            $error_message = "Error updating product: " . $stmt->error;
        }

        // Execute Statement
        if ($stmt->execute()) {
            $_SESSION['message'] = $success_message;
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $error_message;
            $_SESSION['message_type'] = 'error';
        }

        if (isset($stmt)) $stmt->close();
    }
} 

// ==========================================================
// B. HANDLE GET REQUEST (DELETE Logic)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    
    $product_id_to_delete = (int)$_GET['delete_id'];
    
    if (!$conn) {
        $_SESSION['message'] = "Database connection failed for delete.";
        $_SESSION['message_type'] = 'error';
    } else {
        // Step 1: FETCH IMAGE PATH
        $sql_fetch = "SELECT image FROM product_table WHERE product_id = ?";
        $stmt_fetch = $conn->prepare($sql_fetch);
        $stmt_fetch->bind_param("i", $product_id_to_delete);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        
        $image_path_to_delete = null;
        if ($row = $result_fetch->fetch_assoc()) {
            $image_path_to_delete = $row['image'];
        }
        $stmt_fetch->close();

        // Step 2: DELETE RECORD FROM DATABASE
        $sql_delete = "DELETE FROM product_table WHERE product_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $product_id_to_delete);
        
        if ($stmt_delete->execute()) {
            
            // Step 3: DELETE PHYSICAL FILE
            if (!empty($image_path_to_delete)) {
                // Konbersyon ng Web Path (assets/images/...) patungong Server Path
                $server_file_path = __DIR__ . "/../" . $image_path_to_delete; 
                
                if (file_exists($server_file_path)) {
                    unlink($server_file_path);
                }
            }
            
            $_SESSION['message'] = "Product ID {$product_id_to_delete} successfully deleted!";
            $_SESSION['message_type'] = 'success';
            
        } else {
            $_SESSION['message'] = "Error deleting product: " . $stmt_delete->error;
            $_SESSION['message_type'] = 'error';
        }
        
        if (isset($stmt_delete)) $stmt_delete->close();
    }
}


if (isset($conn) && is_object($conn)) $conn->close();

// --- 5. REDIRECT ---
header("Location: " . $redirect_page);
exit();
?>