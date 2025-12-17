<?php
// admin/user_orders.php
session_start();
include('../config/db_connect.php');

// 1. Check Admin Role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 2. Get User ID from URL
if(!isset($_GET['id'])){
    header('Location: user_list.php');
    exit();
}

$user_id = $_GET['id'];

// 3. Get User Details (for the title)
$sql_user = "SELECT * FROM users WHERE user_id = $user_id";
$res_user = mysqli_query($conn, $sql_user);
$user_info = mysqli_fetch_assoc($res_user);

if(!$user_info){
    die("User not found.");
}

// 4. Fetch Orders for this User
$sql_orders = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$result = mysqli_query($conn, $sql_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History - <?php echo htmlspecialchars($user_info['username']); ?></title>
    <link rel="stylesheet" href="../assets/admin_style.css">
    <style>
        .order-card { background: white; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .order-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        .status-badge { padding: 4px 10px; border-radius: 15px; font-size: 0.8em; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Admin Panel - Order History</h2></div>
        <div class="nav-links">
            <a href="users_list.php">Back to User List</a>
        </div>
    </nav>

    <div class="container">
        <h1>Order History: <span style="color: #2980b9;"><?php echo htmlspecialchars($user_info['username']); ?></span></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?> | <strong>Phone:</strong> <?php echo htmlspecialchars($user_info['phone_number']); ?></p>
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($result)): ?>
                
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <strong>Order #<?php echo $order['order_id']; ?></strong>
                            <br>
                            <span style="color: #777; font-size: 0.9em;"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div style="text-align: right;">
                            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                            <div style="margin-top: 5px; font-weight: bold; font-size: 1.1em;">
                                Total: RM <?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                        <strong>Items:</strong>
                        <ul style="margin: 5px 0 0 20px; color: #555;">
                            <?php 
                                $oid = $order['order_id'];
                                $sql_items = "SELECT order_details.*, food_items.name 
                                              FROM order_details 
                                              JOIN food_items ON order_details.food_id = food_items.food_id 
                                              WHERE order_id = $oid";
                                $res_items = mysqli_query($conn, $sql_items);
                                
                                while($item = mysqli_fetch_assoc($res_items)){
                                    echo "<li>" . $item['quantity'] . "x " . htmlspecialchars($item['name']) . "</li>";
                                }
                            ?>
                        </ul>
                        <div style="margin-top: 10px; font-size: 0.9em; color: #666;">
                            <strong>Deliver to:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #777;">
                <h3>No orders found for this user.</h3>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>