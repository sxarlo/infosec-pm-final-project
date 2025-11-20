<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "connection.php";

// ------------------------
// Password Validation Function
// ------------------------
function validatePasswordStrength($password) {
    $errors = [];

    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters long.";
    }

    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = "Password must contain at least one letter.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }

    if (!preg_match('/^[A-Za-z0-9]+$/', $password)) {
        $errors[] = "Password must not contain special characters.";
    }

    return $errors;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    // ------------------------
    // Debug: Show submitted data
    // ------------------------
    // var_dump($_POST);

    // ------------------------
    // Validate password FIRST
    // ------------------------
    $passwordErrors = validatePasswordStrength($password);

    if (!empty($passwordErrors)) {
        $error = implode("<br>", $passwordErrors);
    } else {

        // If password is valid → Hash it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // ------------------------
        // Insert into DB
        // ------------------------
        $stmt = $conn->prepare("INSERT INTO users_table (username, password, role, status) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ssss", $username, $hashedPassword, $role, $status);

            if ($stmt->execute()) {
                $success = "User created successfully!";
            } else {
                $error = "Execute failed: " . $stmt->error;
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create user</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .main-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 7px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 400px;
        }

        .main-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 28px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
        }

        input, select {
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.2s, box-shadow 0.2s;
        }

        input:focus, select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
            outline: none;
        }

        button[type="submit"] {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #007bff;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
        }

        button[type="submit"]:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        p {
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
        }

        p[style*="red"] {
            color: #e74c3c;
        }

        p[style*="green"] {
            color: #2ecc71;
        }

        @media(max-width: 480px){
            .main-container {
                padding: 25px 20px;
            }
            .main-container h2 {
                font-size: 24px;
            }
        }
    </style>

<div clas

</head>
<body>
    <div class="main-container">
        <h2>Create Account</h2>
        <form class="form-container" action="" method="POST" id="createForm">
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>

            <label for="username">Email:</label>
            <input type="email" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <p id="passwordMessage" style="color:red; font-size: 0.9em;"></p>

            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
                <option value="customer">Customer</option>
            </select>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button type="submit" id="submitBtn">Create User</button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordMessage = document.getElementById('passwordMessage');
        const submitBtn = document.getElementById('submitBtn');

        function validatePassword(password) {
            const errors = [];

            if (password.length < 12) {
                errors.push("Password must be at least 12 characters long.");
            }

            if (!/[A-Za-z]/.test(password)) {
                errors.push("Password must contain at least one letter.");
            }

            if (!/[0-9]/.test(password)) {
                errors.push("Password must contain at least one number.");
            }

            if (/[^A-Za-z0-9]/.test(password)) {
                errors.push("Password must not contain special characters.");
            }

            return errors;
        }

        passwordInput.addEventListener('input', () => {
            const errors = validatePassword(passwordInput.value);
            if (errors.length > 0) {
                passwordMessage.innerHTML = errors.join("<br>");
                submitBtn.disabled = true;
            } else {
                passwordMessage.innerHTML = "";
                submitBtn.disabled = false;
            }
        });
    </script>
</body>

</html>

