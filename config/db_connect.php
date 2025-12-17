<?php
// config/db_connect.php

// ---------------------------------------------------------
// ENVIRONMENT SETTINGS
// ---------------------------------------------------------

// Check if we are running locally (XAMPP) or on the Server (AWS)
// $_SERVER['SERVER_NAME'] usually returns 'localhost' on your machine.
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
    // LOCAL SETTINGS (XAMPP)
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "food_ordering_db";
} else {
    // LIVE SETTINGS (AWS / LINUX)
    // You will fill these in when you create your RDS or EC2 database
    $servername = "your-aws-endpoint.rds.amazonaws.com"; 
    $username   = "admin_user"; 
    $password   = "your_secure_password"; 
    $dbname     = "food_ordering_db";
}

// ---------------------------------------------------------
// CONNECTION LOGIC
// ---------------------------------------------------------

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // On a live server, never show the actual SQL error to users for security.
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check system logs.");
}

// Set character set to UTF-8 to handle special characters (e.g., in food names)
$conn->set_charset("utf8");

?>