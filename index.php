<?php
// index.php
session_start(); // IMPORTANT: Start session to check if user is logged in
include('config/db_connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Max Star Food Ordering System</title>
    <link rel="stylesheet" href="assets/member_style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <h2>Max Star Food Ordering System - Home</h2>
        </div>
        <div class="nav-links">
            <a href="my_orders.php"> Order History</a>
            <a href="index.php"style="color: white; border-bottom: 2px solid white;">Home</a>
            <a href="menu.php">Menu</a>

            <?php 
                $count = 0;
                if(isset($_SESSION['cart'])) {
                    foreach($_SESSION['cart'] as $item) {
                        $count += $item['quantity'];
                    }
                }
            ?>
            <a href="cart.php">Cart (<?php echo $count; ?>)</a>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1>Welcome to Max Star Online Food Ordering</h1>        
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <p style="font-size: 1.2em;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
        <?php else: ?>
            <p>
                Server Status: 
                <?php 
                    if($conn) {
                        echo "<strong class='success'>In Operation...</strong>";
                    } 
                ?>
            </p>
        <?php endif; ?>

        <br>
        <p>Order your delicious meals today.</p>
        <br>
        
        <!-- show 'Get Started' registration button if user are not logged in -->
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn">Get Started</a>
        <?php endif; ?>

        <a href="menu.php" class="btn" style="background-color: #ff9f43;">View Menu</a>
        <a href="my_orders.php" class="btn" style="background-color: #333;">Order History</a>

        <br>
        <br>

        <h2>About Us</h2>
        <img src="assets/images/maxstar.png" alt="Logo Image" width="600">

        <br>
        <br>
        
        <p>
            Welcome to <strong>Max Star</strong> – your trusted food ordering system.<br>
            We believe that food is more than just a meal.<br>
            From local favorites to delicious fast food, from refreshing drinks to tasty desserts, <br>
            our mission is to make ordering food simple, fast, and enjoyable.
        </p>
        <br>
        <p>
            All in all, this is our college 
            <a href="https://www.tarc.edu.my/">TAR UMT</a>
             assignment project and we do not own a real stall in school.<br>
            The system is developed for learning purposes and demonstrations only.
        </p>

        <br>
        <br>

        <h2>Our Location</h2>
        <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.137085227218!2d100.2848745!3d5.4532052!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304ac2c0305a5483%3A0xfeb1c7560c785259!2sTAR%20University%20College!5e0!3m2!1sen!2smy!4v1726488290000!5m2!1sen!2smy"
                width="600"
                height="450"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
        </iframe>
        </div>

    </div>

</body>
</html>