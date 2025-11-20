<?php
// delete-user.php - Ito ang file na naghe-handle ng pag-delete ng user.

session_start();
include '../connection.php'; // I-check ang tamang path

// AUTHENTICATION & AUTHORIZATION CHECK (Security First)

if (!isset($_SESSION['username'])) {
    header("Location: /customer_side/login.php"); 
    exit();
}

$allowed_roles = ['admin'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: unauthorized.php"); 
    exit();
}

// DELETE LOGIC

if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id_to_delete = $_GET['user_id'];
    
    // Security Check: Pigilan ang Admin na burahin ang sarili niyang account habang naka-login
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id_to_delete) {
        $_SESSION['delete_message'] = "Error: You cannot delete your own active account.";
        header("Location: admin-user-management.php");
        exit();
    }

    // Prepared statement para sa DELETE (Secure laban sa SQL Injection)
    $sql = "DELETE FROM users_table WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id_to_delete);

    if ($stmt->execute()) {
        $_SESSION['delete_message'] = "User ID: {$user_id_to_delete} has been successfully deleted.";
    } else {
        $_SESSION['delete_message'] = "Error deleting user: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['delete_message'] = "Error: Invalid user ID provided for deletion.";
}

$conn->close();

// I-redirect pabalik sa user logs table
header("Location: admin-user-management.php");
exit();
?>