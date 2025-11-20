<?php
// update-form.php
include '../connection.php'; // I-check kung tama ang path

// 1. KUNIN ANG USER ID MULA SA URL AT I-FETCH ANG DATA
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id_to_edit = $_GET['user_id'];

    $sql = "SELECT user_id, username, role, status FROM users_table WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Ang $user variable na gagamitin sa HTML form
        $user = $result->fetch_assoc();
    } else {
        // Error handling kung walang nakitang user
        echo "User not found or invalid ID.";
        exit();
    }
    $stmt->close();
} else {
    // Error handling kung walang user_id sa URL
    echo "No user ID provided.";
    exit();
}
// HINDI na kailangan ang $conn->close() dito dahil gagamitin pa ito ng admin-user-management.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&family=Nabla&family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Orbitron:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Sansation:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Zen+Dots&display=swap" rel="stylesheet">
    <title>Update User Form</title>
    <style>
        /* CSS styles from your original code */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        .update-form-card {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #929292;
        }

        .updated-form-container {
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: space-around;
            width: auto;
            max-width: 500px;
            height: 50vh; /* Tumaas ng kaunti ang height */
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
        }

        form {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 1rem; /* Bawasan ang gap para magkasya */
        }
        
        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        button {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            background-color: #007bff; /* Example color */
            color: white;
            cursor: pointer;
            margin-top: 10px;
        }

        input, select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="update-form-card">
        <section class="updated-form-container">
            <h1>User Update Form</h1>

            <form action="admin-user-management.php" method="POST">
                
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']); ?>">

                <div class="field">
                    <label>Username:</label>
                    <input type="text" name="username"
                        value="<?= htmlspecialchars($user['username']); ?>"
                        required>
                </div>

                <div class="field">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="admin"    <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="employee" <?= ($user['role'] == 'employee') ? 'selected' : '' ?>>Employee</option>
                        <option value="customer" <?= ($user['role'] == 'customer') ? 'selected' : '' ?>>Customer</option>
                    </select>
                </div>

                <div class="field">
                    <label>Status:</label>
                    <select name="status" required>
                        <option value="active"   <?= ($user['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($user['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <button type="submit">Update User</button>
            </form>
        </section>
    </div>
</body>
</html>