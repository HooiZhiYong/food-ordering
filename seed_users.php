<?php
// seed_users.php
// Run this file in your browser (http://localhost/food-ordering/seed_users.php)
// It will create 3 customer accounts with IDs 3, 4, and 5.

include('config/db_connect.php');

$users_to_add = [
    [
        'id' => 3, 
        'username' => 'Sarah Lee', 
        'email' => 'sarah@student.tarc.edu.my', 
        'password' => '123456', 
        'phone' => '0123456780', 
        'address' => 'Block A, Room 101, College Hostel'
    ],
    [
        'id' => 4,
        'username' => 'Ahmad Ali', 
        'email' => 'ahmad@student.tarc.edu.my', 
        'password' => '123456', 
        'phone' => '0198765432', 
        'address' => '15, Jalan Setapak, Kuala Lumpur'
    ],
    [
        'id' => 5,
        'username' => 'Mei Ling', 
        'email' => 'mei@student.tarc.edu.my', 
        'password' => '123456', 
        'phone' => '0175556666', 
        'address' => 'Condo PV12, Unit 5-4'
    ]
];

echo "<h1>Seeding Users...</h1>";

foreach ($users_to_add as $user) {
    // 1. Hash the password securely
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    
    // 2. Check if ID exists first to prevent errors
    $check = mysqli_query($conn, "SELECT user_id FROM users WHERE user_id = " . $user['id']);
    
    if (mysqli_num_rows($check) > 0) {
        echo "<p style='color:red'>User ID " . $user['id'] . " already exists. Skipped.</p>";
    } else {
        // 3. Insert with Specific ID
        $sql = "INSERT INTO users (user_id, username, email, password, phone_number, address, role) 
                VALUES (?, ?, ?, ?, ?, ?, 'customer')";
        
        $stmt = $conn->prepare($sql);
        // 'isssss' means Integer, String, String, String, String, String
        $stmt->bind_param("isssss", $user['id'], $user['username'], $user['email'], $hashed_password, $user['phone'], $user['address']);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>Created User: <strong>" . $user['username'] . "</strong> (ID: " . $user['id'] . ")</p>";
        } else {
            echo "<p style='color:red'>Error creating " . $user['username'] . ": " . $conn->error . "</p>";
        }
    }
}

echo "<br><hr>";
echo "<p>Done! You can now check the Admin Panel.</p>";
echo "<a href='admin/user_list.php'>Go to User List</a>";
?>