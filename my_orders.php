<?php
// my_orders.php
session_start();
include('config/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for this specific user
$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="assets/member_style.css">
    <style>
        .order-card { 
            background: white; 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px; }
        .order-header { 
            display: flex; 
            justify-content: space-between; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 10px; 
            margin-bottom: 10px; }
        .status { 
            padding: 5px 10px; 
            border-radius: 15px; 
            font-size: 0.8em; 
            font-weight: bold; }
        .status-pending { 
            background: #fff3cd; 
            color: #856404; }
        .status-completed { 
            background: #d4edda; 
            color: #155724; }
        .status-cancelled { 
            background: #f8d7da; 
            color: #721c24; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Max Star Food Ordering System - Order History</h2></div>
        <div class="nav-links">
            <a href="my_orders.php"style="color: white; border-bottom: 2px solid white;"> Order History</a>
            <a href="index.php">Home</a>
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
            <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        </div>
    </nav>

    <div class="container">
        <h1>Your Order History</h1>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($result)): ?>
                
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <strong>Order #<?php echo $order['order_id']; ?></strong><br>
                            <small><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></small>
                        </div>
                        <div style="text-align: right;">
                            <span class="status status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                            <br>
                            <span style="font-weight: bold; color: #333;">Total: RM <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>

                    <!-- Fetch Items for this specific order -->
                    <?php 
                        $oid = $order['order_id'];
                        $sql_items = "SELECT order_details.*, food_items.name 
                                      FROM order_details 
                                      JOIN food_items ON order_details.food_id = food_items.food_id 
                                      WHERE order_id = $oid";
                        $res_items = mysqli_query($conn, $sql_items);
                    ?>
                    
                    <ul style="list-style: none; padding-left: 0; color: #555;">
                        <?php while($item = mysqli_fetch_assoc($res_items)): ?>
                            <li>
                                <?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>You haven't placed any orders yet.</p>
            <a href="menu.php" class="btn">Order Now</a>
        <?php endif; ?>
    </div>

</body>
</html>