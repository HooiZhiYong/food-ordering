<?php
// admin/user_list.php
session_start();
include('../config/db_connect.php');

// 1. Check Admin Role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 2. Handle Delete Request
if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    
    // Safety: Prevent Admin from deleting themselves
    if($delete_id == $_SESSION['user_id']){
        echo "<script>alert('You cannot delete your own account!'); window.location='users_list.php';</script>";
    } else {
        // DELETE query
        $sql_delete = "DELETE FROM users WHERE user_id = $delete_id";
        if(mysqli_query($conn, $sql_delete)){
            echo "<script>alert('User deleted successfully.'); window.location='users_list.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

// 3. Fetch Users (With Search Logic)
$search = "";
if(isset($_GET['search'])){
    $search = $_GET['search'];
    // Prevent SQL Injection using mysqli_real_escape_string
    $safe_search = mysqli_real_escape_string($conn, $search);
    
    // Search in Username, Email, OR Phone Number
    $sql = "SELECT * FROM users 
            WHERE username LIKE '%$safe_search%' 
            OR email LIKE '%$safe_search%' 
            OR phone_number LIKE '%$safe_search%' 
            ORDER BY role, created_at DESC";
} else {
    // Default: Show all
    $sql = "SELECT * FROM users ORDER BY role, created_at DESC";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List - Admin</title>
    <!-- Use Admin Style -->
    <link rel="stylesheet" href="../assets/admin_style.css">
    <style>
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px;}
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
        th { background-color: #2c3e50; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .badge-admin { background-color: #e74c3c; color: white; }
        .badge-customer { background-color: #27ae60; color: white; }

        .btn-history { background-color: #2196F3; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.9em; margin-right: 5px; }
        .btn-delete { background-color: #f44336; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.9em; }

        /* Search Bar Styles */
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; max-width: 500px; }
        .search-box input { padding: 10px; flex: 1; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .search-box button:hover { background: #34495e; }
        .btn-reset { background: #95a5a6; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Admin Panel - Users List</h2></div>
        <div class="nav-links">
            <a href="view_orders.php">Orders</a>
            <a href="users_list.php" style="color: white; border-bottom: 2px solid white;">Users List</a>
            <a href="manage_food.php">Manage Food Item</a>
            <a href="add_food.php">Add Food Item</a>
            <a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        </div>
    </nav>

    <div class="container">
        <h1>Registered Users</h1>

        <!-- Search Form -->
        <form action="users_list.php" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search by Name, Email or Phone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            
            <?php if($search): ?>
                <a href="users_list.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['user_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['username']); ?></strong><br>
                                <small style="color:#666;"><?php echo htmlspecialchars($row['phone_number']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['role']; ?>">
                                    <?php echo ucfirst($row['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <!-- View History Button -->
                                <a href="order_history.php?id=<?php echo $row['user_id']; ?>" class="btn-history">History</a>
                                
                                <!-- Delete Button (Only show if not deleting self) -->
                                <?php if($row['user_id'] != $_SESSION['user_id']): ?>
                                    <a href="users_list.php?delete=<?php echo $row['user_id']; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure? This will delete the user AND their order history.');">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                <p>No users found matching "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>