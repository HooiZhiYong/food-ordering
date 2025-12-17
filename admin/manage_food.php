<?php
// admin/manage_food.php
session_start();
include('../config/db_connect.php');
include('../config/s3_setup.php'); // Include S3 settings

// 1. Check Admin Role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 2. Handle Delete Request
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    
    // Step A: Get the image path BEFORE deleting the record
    $sql_check = "SELECT image_path FROM food_items WHERE food_id = $id_to_delete";
    $result_check = mysqli_query($conn, $sql_check);
    $item = mysqli_fetch_assoc($result_check);

    if ($item) {
        $img_path = $item['image_path'];

        // Step B: Check if it is an S3 URL (starts with http)
        if (strpos($img_path, 'http') === 0) {
            // It is an S3 image! Let's delete it from the bucket.
            // We need the "Key" (e.g., food-images/burger.jpg).
            // Since we know we store them in 'food-images/', we can reconstruct it:
            $file_name = basename($img_path);
            $s3_key = 'food-images/' . $file_name;

            try {
                $s3->deleteObject([
                    'Bucket' => $bucket_name,
                    'Key'    => $s3_key
                ]);
            } catch (Exception $e) {
                // If S3 delete fails, we just continue to delete the DB record
                // error_log("S3 Delete Error: " . $e->getMessage());
            }
        }
    }

    // Step C: Delete from Database
    $sql_delete = "DELETE FROM food_items WHERE food_id = $id_to_delete";
    if(mysqli_query($conn, $sql_delete)){
        echo "<script>alert('Food item deleted (and image removed from S3)!'); window.location='manage_food.php';</script>";
    } else {
        echo "Error deleting: " . mysqli_error($conn);
    }
}

// 3. Fetch All Food
$sql = "SELECT * FROM food_items ORDER BY category, name";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Food - Admin</title>
    <!-- Using Admin Style -->
    <link rel="stylesheet" href="../assets/admin_style.css">
    <style>
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px;}
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
        th { background-color: #2c3e50; color: white; }
        .btn-edit { background-color: #2196F3; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        .btn-delete { background-color: #f44336; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        img.thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Admin Panel - Manage Menu Item</h2></div>
        <div class="nav-links">
            <a href="view_orders.php">Orders</a>
            <a href="users_list.php">Users List</a>
            <a href="manage_food.php" style="color: white; border-bottom: 2px solid white;">Manage Food Item</a>
            <a href="add_food.php">Add Food Item</a>
            <a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        </div>
    </nav>

    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1>Manage Menu Items</h1>
            <a href="add_food.php" class="btn" style="background-color: #4CAF50;">+ Add New Food</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price (RM)</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <?php 
                                // Hybrid Display Logic:
                                // If path starts with 'http', it's S3. Otherwise, it's local.
                                $img_src = $row['image_path'];
                                if ($img_src && strpos($img_src, 'http') !== 0) {
                                    $img_src = "../assets/images/" . $img_src;
                                }
                            ?>
                            
                            <?php if($img_src): ?>
                                <img src="<?php echo $img_src; ?>" class="thumb" onerror="this.src='https://via.placeholder.com/50?text=x'">
                            <?php else: ?>
                                No Img
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo $row['price']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td>
                            <a href="edit_food.php?id=<?php echo $row['food_id']; ?>" class="btn-edit">Edit</a>
                            <a href="manage_food.php?delete=<?php echo $row['food_id']; ?>" 
                               class="btn-delete"
                               onclick="return confirm('Are you sure you want to delete this item? This will also delete the S3 image.');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>