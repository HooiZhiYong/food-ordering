<?php
// admin/view_orders.php
session_start();
include('../config/db_connect.php');

// 1. Security Check: Block unauthorized users
// If session role is NOT 'admin', kick them out to login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 2. Handle Status Update (When admin clicks "Update" button)
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];
    
    $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()){
        // Success message could go here
    }
}

// 3. Fetch All Orders (Join with Users table to get the customer's name)
$sql = "SELECT orders.*, users.username 
        FROM orders 
        JOIN users ON orders.user_id = users.user_id 
        ORDER BY orders.order_date DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - View Orders</title>
    <!-- Note the ../ to go back up one level to assets -->
    <link rel="stylesheet" href="../assets/admin_style.css">
    <style>
        /* Specific styles for the Admin Table */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #333; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        /* Status badge colors */
        .status-pending { color: orange; font-weight: bold; }
        .status-completed { color: green; font-weight: bold; }
        .status-cancelled { color: red; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Admin Panel - Home | Orders</h2></div>
        <div class="nav-links">
            <a href="view_orders.php"style="color: white; border-bottom: 2px solid white;">Orders</a>
            <a href="users_list.php">Users List</a>
            <a href="manage_food.php">Manage Food Item</a>
            <a href="add_food.php">Add Food Item</a>
            <a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        </div>
    </nav>

    <div class="container">
        <h1>Customer Orders</h1>
        <p>Welcome back, <strong><?php echo $_SESSION['username']; ?></strong></p>

        <?php if (mysqli_num_rows($result) > 0): ?>
            
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total (RM)</th>
                        <th>Date</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td>RM <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></td>
                            <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
                            
                            <!-- Display Status Text -->
                            <td>
                                <span class="status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>

                            <!-- Update Status Form -->
                            <td>
                                <form action="view_orders.php" method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="order_status">
                                        <option value="pending" <?php echo $order['order_status']=='pending'?'selected':''; ?>>Pending</option>
                                        <option value="completed" <?php echo $order['order_status']=='completed'?'selected':''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['order_status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn" style="padding: 5px 10px; font-size: 12px;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p style="margin-top: 20px;">No orders have been placed yet.</p>
        <?php endif; ?>

    </div>

</body>
</html>