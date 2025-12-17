<?php
// admin/add_food.php
session_start();
include('../config/db_connect.php');
include('../config/s3_setup.php'); // Include S3 settings

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$errors = array('name'=>'', 'price'=>'', 'image'=>'', 'general'=>'');
$success_msg = '';

// 2. Handle Form Submission
if(isset($_POST['submit'])){
    
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Image Setup
    $file = $_FILES['image'];
    $file_name = basename($file['name']); // e.g. "burger.jpg"
    $temp_file_location = $file['tmp_name']; // The temp file on server

    // Validate inputs
    if(empty($name) || empty($price) || empty($file_name)){
        $errors['general'] = "Please fill in all fields.";
    } else {
        
        // --- S3 UPLOAD LOGIC ---
        try {
            // 1. Upload to S3
            // We use the $s3 object we created in s3_setup.php
            $result = $s3->putObject([
                'Bucket' => $bucket_name,
                'Key'    => 'food-images/' . $file_name, // Folder inside bucket
                'SourceFile' => $temp_file_location,
                'ACL'    => 'public-read' // Make file public so users can see it
            ]);

            // 2. Get the Public URL from S3 (e.g. https://bucket.s3.../burger.jpg)
            $s3_url = $result['ObjectURL'];

            // 3. Save the FULL URL to Database
            $sql = "INSERT INTO food_items (name, description, price, category, image_path) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdss", $name, $description, $price, $category, $s3_url);
            
            if($stmt->execute()){
                $success_msg = "Food Item uploaded to S3 successfully!";
            } else {
                $errors['general'] = "Database Error: " . $conn->error;
            }

        } catch (Aws\S3\Exception\S3Exception $e) {
            $errors['image'] = "S3 Upload Failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Food - Admin</title>
    <!-- Using Admin Style -->
    <link rel="stylesheet" href="../assets/admin_style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo"><h2>Admin Panel - Add New Food Item</h2></div>
        <div class="nav-links">
            <a href="manage_food.php">Back to Menu List</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1>Add New Food Item</h1>
            
            <?php if($success_msg): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if($errors['general']): ?>
                <div class="error"><?php echo $errors['general']; ?></div>
            <?php endif; ?>
            <?php if($errors['image']): ?>
                <div class="error"><?php echo $errors['image']; ?></div>
            <?php endif; ?>

            <form action="add_food.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Price (RM):</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Category:</label>
                    <select name="category">
                        <option value="Western">Western</option>
                        <option value="Local">Local</option>
                        <option value="Beverage">Beverage</option>
                        <option value="Snack">Snack</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image:</label>
                    <input type="file" name="image" required>
                </div>
                <button type="submit" name="submit" class="btn" style="width: 100%; background-color: #4CAF50;">Add New Food Item</button>
            </form>
        </div>
    </div>
</body>
</html>