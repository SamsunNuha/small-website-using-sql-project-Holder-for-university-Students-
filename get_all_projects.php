<?php
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

try {
    // We don't necessarily need session_id here if we want anyone logged in to see the global list
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access.');
    }

    // Join users and projects to get student names and titles
    $sql = "SELECT u.username, p.title 
            FROM projects p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.id DESC";
            
    $result = $conn->query($sql);

    $records = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
    }

    echo json_encode(['success' => true, 'records' => $records]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
