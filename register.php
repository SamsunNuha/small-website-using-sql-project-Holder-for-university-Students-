<?php
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Start output buffering to capture any unwanted output (warnings, whitespace)
ob_start();

require 'db_connect.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('No input data received');
    }

    if (isset($input['username']) && isset($input['email']) && isset($input['password'])) {
        $username = $conn->real_escape_string($input['username']);
        $email = $conn->real_escape_string($input['email']);
        $password = password_hash($input['password'], PASSWORD_DEFAULT);

        // Check if user exists
        $checkQuery = "SELECT * FROM users WHERE email = '$email' OR username = '$username'";
        $result = $conn->query($checkQuery);

        if (!$result) {
            throw new Exception("Database error: " . $conn->error);
        }

        if ($result->num_rows > 0) {
            $response = ['success' => false, 'message' => 'Username or Email already exists'];
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                $response = ['success' => true, 'message' => 'Registration successful'];
            } else {
                throw new Exception("Error registering user: " . $conn->error);
            }
        }
    } else {
        $response = ['success' => false, 'message' => 'Invalid input: Missing required fields'];
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// Clean the buffer (removing any accidental text/whitespace) and output strictly JSON
ob_end_clean();
echo json_encode($response);
$conn->close();
?>
