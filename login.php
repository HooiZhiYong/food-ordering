<?php
// login.php
// Start session at the very top!
session_start();
include('config/db_connect.php');

$error = '';

if (isset($_POST['login'])) {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // 1. Find user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // 2. Verify Password
        // password_verify(plain_text, hashed_from_db)
        if (password_verify($password, $row['password'])) {
            // 3. Password Correct - Set Session Variables
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // 'admin' or 'customer'

            // 4. Redirect based on role
            if ($row['role'] == 'admin') {
                header('Location: admin/view_orders.php'); // We will build this later
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Account</title>
    <link rel="stylesheet" href="assets/member_style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Max Star - Account Login</h2></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center;">AccountLogin</h2>
            
            <?php 
                // Show success message if redirected from register
                if(isset($_GET['status']) && $_GET['status'] == 'registered') {
                    echo "<p class='success'>Account created! Please login.</p>";
                }
                
                if($error) {
                    echo "<div class='error'>$error</div>";
                }
            ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required placeholder="name@student.tarc.edu.my">
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="your password">
                </div>

                <button type="submit" name="login" class="btn" style="width: 100%; background-color: #333;">Login</button>
            </form>

            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>

</body>
</html>