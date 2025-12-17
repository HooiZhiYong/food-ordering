<?php
// admin/edit_food.php
session_start();
include('../config/db_connect.php');
include('../config/s3_setup.php'); // Include S3 settings

// 1. Check Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// 2. Check if ID is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM food_items WHERE food_id = $id";
    $result = mysqli_query($conn, $sql);
    $food = mysqli_fetch_assoc($result);

    if (!$food) {
        die("Food item not found!");
    }
} else {
    // If no ID, go back to list
    header('Location: manage_food.php');
    exit();
}

$msg = "";

// 3. Handle Update Submission
if (isset($_POST['update'])) {
    
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    $id_to_update = $_POST['id_to_update'];
    
    // Default to existing path
    $image_path = $food['image_path']; 

    // A. Check if a NEW image was uploaded
    if (!empty($_FILES['image']['name'])) {
        $file_name = basename($_FILES['image']['name']);
        $temp_file = $_FILES['image']['tmp_name'];
        
        try {
            // --- S3 UPLOAD LOGIC ---
            $result = $s3->putObject([
                'Bucket' => $bucket_name,
                'Key'    => 'food-images/' . $file_name,
                'SourceFile' => $temp_file,
                'ACL'    => 'public-read'
            ]);

            // Get the new S3 URL and update the variable
            $image_path = $result['ObjectURL'];

        } catch (Aws\S3\Exception\S3Exception $e) {
            $msg = "S3 Error: " . $e->getMessage();
        }
    }

    // B. Update Database
    // Only proceed if no upload error occurred
    if (empty($msg)) {
        $sql_update = "UPDATE food_items SET name=?, description=?, price=?, category=?, image_path=? WHERE food_id=?";
        
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_path, $id_to_update);

        if ($stmt->execute()) {
            echo "<script>alert('Updated Successfully!'); window.location='manage_food.php';</script>";
        } else {
            $msg = "Error updating: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Food - Admin</title>
    <!-- Using Admin Style -->
    <link rel="stylesheet" href="../assets/admin_style.css">
    <style>
        .form-container { max-width: 600px; }
        .current-img { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; margin-top: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><h2>Admin Panel - Edit Food Item</h2></div>
        <div class="nav-links">
            <a href="manage_food.php">Back to List</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2>Edit Food Item</h2>
            
            <?php if($msg): ?>
                <div class="error"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form action="edit_food.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="id_to_update" value="<?php echo $food['food_id']; ?>">

                <div class="form-group">
                    <label>Food Name:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($food['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($food['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Price (RM):</label>
                    <input type="number" name="price" step="0.01" value="<?php echo $food['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="category" style="width: 100%; padding: 10px;">
                        <?php 
                            $cats = ['Western', 'Local', 'Beverage', 'Snack'];
                            foreach($cats as $c) {
                                $selected = ($c == $food['category']) ? 'selected' : '';
                                echo "<option value='$c' $selected>$c</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Change Image (Optional):</label>
                    <input type="file" name="image">
                    <br>
                    <p style="font-size: 0.9em; margin-top: 5px;">Current Image:</p>
                    
                    <!-- SMART IMAGE LOGIC: Handles both Old (Local) and New (S3) images -->
                    <?php 
                        $img_src = $food['image_path'];
                        // If it doesn't start with 'http', it's an old local file, so add the folder path
                        if (strpos($img_src, 'http') !== 0) {
                            $img_src = "../assets/images/" . $img_src;
                        }
                    ?>
                    <img src="<?php echo $img_src; ?>" class="current-img" 
                         onerror="this.src='https://via.placeholder.com/100?text=No+Img'">
                </div>

                <button type="submit" name="update" class="btn" style="width: 100%; background-color: #2196F3;">Update Food Item</button>

            </form>
        </div>
    </div>

</body>
</html>