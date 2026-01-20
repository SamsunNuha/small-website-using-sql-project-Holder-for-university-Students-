<?php
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('No input data received');
    }

    if (isset($input['username']) && isset($input['password'])) { 
        $username = $conn->real_escape_string($input['username']);
        $password = $input['password'];

        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($sql);

        if (!$result) {
            throw new Exception("Database query failed: " . $conn->error);
        }

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Store role in session
                echo json_encode([
                    'success' => true, 
                    'username' => $user['username'],
                    'role' => $user['role'] // Return role to frontend
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input: Missing username or password']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
