<?php
// menu.php
session_start();
include('config/db_connect.php');

// --- ADD TO CART LOGIC ---
if (isset($_POST['add_to_cart'])) {
    
    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please login to add items to cart.'); window.location='login.php';</script>";
        exit();
    }

    // 2. Get Info from Form
    $food_id = $_POST['food_id'];
    $food_name = $_POST['food_name'];
    $food_price = $_POST['food_price'];
    $quantity = $_POST['quantity'];

    // 3. Initialize Cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // 4. Add or Update Item in Cart
    if (isset($_SESSION['cart'][$food_id])) {
        $_SESSION['cart'][$food_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$food_id] = array(
            'name' => $food_name,
            'price' => $food_price,
            'quantity' => $quantity
        );
    }
    
    echo "<script>alert('Added $food_name to cart!');</script>";
}

// --- FETCH MENU ITEMS ---
$sql = "SELECT * FROM food_items WHERE is_active = 1";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu - Food Ordering</title>
    <link rel="stylesheet" href="assets/member_style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Max Star Food Ordering System - Menu</h2></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="menu.php"style="color: white; border-bottom: 2px solid white;">Menu</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- Calculate Cart Count -->
                <?php 
                    $count = 0;
                    if(isset($_SESSION['cart'])) {
                        foreach($_SESSION['cart'] as $item) {
                            $count += $item['quantity'];
                        }
                    }
                ?>
                <a href="cart.php">Cart (<?php echo $count; ?>)</a>
                <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1>Our Menu</h1>
        
        <div class="food-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($food = mysqli_fetch_assoc($result)): ?>
                    
                    <form action="menu.php" method="POST" class="food-card">
                        
                        <!-- HYBRID IMAGE LOGIC -->
                        <?php 
                            $img_src = $food['image_path'];
                            
                            // If path exists AND it DOES NOT start with 'http', assume it is a local file
                            if (!empty($img_src) && strpos($img_src, 'http') !== 0) {
                                $img_src = "assets/images/" . $img_src;
                            }
                            
                            // Fallback if empty
                            if(empty($img_src)) { 
                                $img_src = "https://via.placeholder.com/250x180?text=No+Image"; 
                            }
                        ?>
                        
                        <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($food['name']); ?>" 
                             onerror="this.src='https://via.placeholder.com/250x180?text=Food';">

                        <h3><?php echo htmlspecialchars($food['name']); ?></h3>
                        <span class="category"><?php echo htmlspecialchars($food['category']); ?></span>
                        <p style="font-size: 0.9em; color: #666; height: 50px; overflow: hidden;">
                            <?php echo htmlspecialchars($food['description']); ?>
                        </p>
                        
                        <span class="price">RM <?php echo number_format($food['price'], 2); ?></span>

                        <!-- Inputs for Cart Logic -->
                        <div style="margin-top: 10px;">
                            <input type="hidden" name="food_id" value="<?php echo $food['food_id']; ?>">
                            <input type="hidden" name="food_name" value="<?php echo htmlspecialchars($food['name']); ?>">
                            <input type="hidden" name="food_price" value="<?php echo $food['price']; ?>">
                            
                            <input type="number" name="quantity" value="1" min="1" max="10">
                            <button type="submit" name="add_to_cart" class="btn">Add</button>
                        </div>

                    </form>

                <?php endwhile; ?>
            <?php else: ?>
                <p>No food items available at the moment.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>