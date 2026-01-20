<?php
require 'db_connect.php';

// Add role column if not exists
$sql = "SHOW COLUMNS FROM users LIKE 'role'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alterSql = "ALTER TABLE users ADD COLUMN role ENUM('student', 'admin') DEFAULT 'student'";
    if ($conn->query($alterSql) === TRUE) {
        echo "Column 'role' added successfully.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "Column 'role' already exists.<br>";
}

// Check/Add Admin User
$adminUser = 'admin';
$checkSql = "SELECT * FROM users WHERE username = '$adminUser'";
$checkRes = $conn->query($checkSql);

if ($checkRes->num_rows == 0) {
    $pass = password_hash('password123', PASSWORD_DEFAULT);
    $insertSql = "INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@fas.com', '$pass', 'admin')";
    if ($conn->query($insertSql) === TRUE) {
        echo "Admin user created successfully.<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
} else {
    // If admin exists, ensure role is admin
    $updateSql = "UPDATE users SET role='admin' WHERE username='$adminUser'";
    $conn->query($updateSql);
    echo "Admin user checked/updated.<br>";
}

echo "Database update complete.";
$conn->close();
?>
