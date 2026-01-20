<?php
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "SEUSL";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit();
}

