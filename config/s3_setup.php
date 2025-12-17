<?php
// config/s3_setup.php
require '../vendor/autoload.php'; // Load the AWS Library we installed

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// 1. S3 Settings
$bucket_name = 'your-unique-bucket-name-here'; // CHANGE THIS
$region = 'us-east-1'; // CHANGE THIS

// 2. Prepare Connection Arguments
$s3_args = [
    'version' => 'latest',
    'region'  => $region
];

// 3. Logic: Check if running on Localhost or AWS
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
    // --- LOCALHOST SETTINGS ---
    // On your laptop, we need actual keys because there is no IAM Role.
    // Get these from AWS Console > IAM > Users > Security Credentials
    $s3_args['credentials'] = [
        'key'    => 'PASTE_YOUR_ACCESS_KEY_HERE',
        'secret' => 'PASTE_YOUR_SECRET_KEY_HERE',
    ];
} else {
    // --- AWS EC2 SETTINGS ---
    // On EC2, we remove the 'credentials' key.
    // The SDK will automatically find the permissions from the attached IAM Role.
}

// 4. Create the S3 Client
$s3 = new S3Client($s3_args);

?>