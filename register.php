<?php
// register.php
include('config/db_connect.php');

// --- FIX: RESET VARIABLES ---
// We define these as empty strings immediately to overwrite the 
// $username="root" that came from the db_connect.php file.
$username = '';
$email = '';
$phone = '';
$address = '';

$errors = array('username' => '', 'email' => '', 'password' => '', 'phone' => '', 'general' => '');

// Check if form is submitted
if (isset($_POST['submit'])) {

    // 1. Get data from form
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password']; 
    
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : ''; 

    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);

    // A. Check for empty fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($phone)) {
        $errors['general'] = "Please fill in all required fields.";
    } 
    else {
        // B. Password Validation (Min 6, Max 10)
        if (strlen($password) < 6 || strlen($password) > 10) {
            $errors['password'] = "Password must be between 6 and 10 characters long.";
        }
        // B2. Confirm Password Check
        elseif ($password !== $confirm_password) {
            $errors['password'] = "Passwords do not match.";
        }

        // C. Phone Validation (10 or 11 digits, numbers only)
        elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = "Phone number must be 10 or 11 digits (numbers only).";
        }

        // D. Email Domain Validation (Must be @student.tarc.edu.my)
        elseif (!preg_match('/@student\.tarc\.edu\.my$/', $email) && !preg_match('/@gmail\.com$/', $email)) {
            $errors['email'] = "Email must use the domain @student.tarc.edu.my or @gmail.com";
        }

        // E. Check if email already exists
        else {
            $sql_check = "SELECT * FROM users WHERE email = '$email'";
            $result_check = mysqli_query($conn, $sql_check);

            if (mysqli_num_rows($result_check) > 0) {
                $errors['email'] = "duplicated.";
            } else {
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (username, email, password, phone_number, address, role) VALUES (?, ?, ?, ?, ?, 'customer')";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $address);

                if ($stmt->execute()) {
                    header('Location: login.php?status=registered');
                    exit();
                } else {
                    $errors['general'] = "Query Error: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Account</title>
    <link rel="stylesheet" href="assets/member_style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Max Star - Register</h2></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center;">Create an Account</h2>
            
            <?php if($errors['general']): ?>
                <div class="error"><?php echo $errors['general']; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Username (Required):</label>
                    <input type="text" name="username" required value="<?php echo htmlspecialchars($username); ?> " placeholder="your nice name">
                </div>

                <div class="form-group">
                    <label>Email (@student.tarc.edu.my):</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" placeholder="name@student.tarc.edu.my">
                    <div class="error"><?php echo $errors['email']; ?></div>
                </div>

                <div class="form-group">
                    <label>Password (6-10 Characters):</label>
                    <input type="password" name="password" require placeholder="Enter a secure password">
                </div>

                <div class="form-group">
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required placeholder="Reenter the password again">
                    <div class="error"><?php echo $errors['password']; ?></div>
                </div>

                <div class="form-group">
                    <label>Phone Number (10-11 Digits):</label>
                    <input type="number" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="01234567890">
                    <div class="error"><?php echo $errors['phone']; ?></div>
                </div>

                <div class="form-group">
                    <label>Address (Optional):</label>
                    <textarea name="address" rows="3" placeholder="Block B, Room 101, TutorialClass..."><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <button type="submit" name="submit" class="btn" style="width: 100%; background-color: #333;">Register</button>
            </form>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

</body>
</html>