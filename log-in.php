<?php
session_start();
require "connection.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $passwordTypedByUser = $_POST['password'];

    // Fetch user data including role
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users_table WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Password check (hashed)
        if (password_verify($passwordTypedByUser, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Role-based redirection
            if ($row['role'] === 'admin') {
                header("Location: admin/admin-product-management.php");
            } else if ($row['role'] === 'employee') {
                header("Location: admin/admin-product-management.php"); // replace with your main website URL
            } else if ($row['role'] === 'customer') {
                header("Location: index.html"); // replace with your main website URL
            } else {
                // fallback in case of unknown role
                $error = "Unknown user role.";
            }
            exit;
        } else {
            $error = "Wrong password.";
        }
    } else {
        $error = "No user found.";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Sansation", sans-serif;
    font-style: normal;
}

body {
    padding: 50px;
}

.log-in-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 80vh;
    width: 70%;;
    margin: 0 auto;
    border-radius: 20px;
    background-image: url("contact-img.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.logo-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.logo-container h1 {
    cursor: pointer;
}

.input-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 20px;
    margin-top: 100px;
    border-radius: 15px;
    max-width: 400px;
    width: 350px;
    height: auto;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.15); 
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.4);
}

.btn-group {
    display: flex;
    margin-top: 10px;
    gap: 1.5rem;
    justify-content: center;
}

.btn-group button {
    padding: 7px 20px;
    border-radius: 7px;
    padding: 10px 30px;
    border: none;
    cursor: pointer;
    background-color: #1e4bb8;
    color: white;
    box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.5);
    transition: all 0.3s ease;
}

.btn-group button:hover {
    background-color: rgb(255, 255, 255);
    color: black;
}

.btn-group type {
    text-decoration: none;
    color: white;
    font-weight: bold;
}

#email, #password {
    padding: 7px;
    border-radius:7px;
    border: none;
    box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.5);
}
    </style>
</head>


<body>
    <div class="log-in-container">
        <form action="" method="POST">
            <div class="input-container">
                <div class="logo-container">
                    <h1>Log In</h1>
                </div>

                <?php if (!empty($error)): ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>

                <label for="email">Email:</label>
                <input type="email" id="email" placeholder="email" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" placeholder="password" name="password" required>

                <div class="btn-group">
                    <button type="submit">Log In</button>
                    <button type="reset">Clear</button>
                </div>
            </div>         
        </form>
    </div>

</body>
</html>