<?php
// cart.php
session_start();
include('config/db_connect.php');

// remove and add cart
if (isset($_POST['action'])) {
    
    // remove item
    if ($_POST['action'] == 'remove') {
        $id_to_remove = $_POST['food_id'];
        unset($_SESSION['cart'][$id_to_remove]);
        
        echo "<script>alert('Item removed!'); window.location='cart.php';</script>";
    }

    // update number of food
    if ($_POST['action'] == 'update') {
        $id_to_update = $_POST['food_id'];
        $new_quantity = $_POST['quantity'];
        
        if($new_quantity > 0) {
            $_SESSION['cart'][$id_to_update]['quantity'] = $new_quantity;
        } else {
            // if food = 0 it auto remove
            unset($_SESSION['cart'][$id_to_update]);
        }
        
        echo "<script>window.location='cart.php';</script>";
    }
}

// calculate total price
$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart - Food Ordering</title>
    <link rel="stylesheet" href="assets/member_style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 15px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #333; color: white; }
        
        .cart-summary {
            margin-top: 30px;
            text-align: right;
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        
        .grand-total {
            font-size: 1.5em;
            color: #ff6b6b;
            font-weight: bold;
        }

        .btn-update { background-color: #4CAF50; padding: 5px 10px; font-size: 0.9em; }
        .btn-remove { background-color: #f44336; padding: 5px 10px; font-size: 0.9em; }
        .btn-checkout { background-color: #ffdb29ff; font-size: 1.2em; padding: 15px 30px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Max Star - Your Cart</h2></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            
            <?php 
                $count = 0;
                if(isset($_SESSION['cart'])) {
                    foreach($_SESSION['cart'] as $item) $count += $item['quantity'];
                }
            ?>
            <a href="cart.php" style="color: white; border-bottom: 2px solid white;">Cart (<?php echo $count; ?>)</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a><?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1>Shopping Cart</h1>

        <!-- check cart is empty -->
        <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
            
            <div style="text-align: center; padding: 50px;">
                <h2>Your cart is empty.</h2>
                <p>Looks like you haven't made your choice yet.</p>
                <br>
                <a href="menu.php" class="btn">Go to Menu</a>
            </div>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Food Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($_SESSION['cart'] as $food_id => $item): ?>
                        <?php 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total_price += $subtotal;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td>RM <?php echo number_format($item['price'], 2); ?></td>
                            
                            <!-- update quantity form -->
                            <td>
                                <form action="cart.php" method="POST" style="display:flex; align-items:center;">
                                    <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="20" style="width: 50px; padding: 5px; margin-right: 5px;">
                                    <button type="submit" class="btn btn-update">Update</button>
                                </form>
                            </td>

                            <td>RM <?php echo number_format($subtotal, 2); ?></td>

                            <!-- remove item form -->
                            <td>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-remove">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <h3>Total Amount: <span class="grand-total">RM <?php echo number_format($total_price, 2); ?></span></h3>
                <br>
                <a href="menu.php" class="btn" style="background-color: #555;">Continue Shopping</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-checkout">Login to Checkout</a>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>

</body>
</html>