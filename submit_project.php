<?php
// Disable error display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Buffer output to catch any unwanted text/warnings
ob_start();

require 'db_connect.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access. Please login.');
    }

    // Check for empty POST (often caused by exceeding post_max_size)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)) {
        throw new Exception('File too large or request payload exceeded server limit.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['user_id'];
        
        // Validate inputs
        if (!isset($_POST['title']) || !isset($_POST['description'])) {
            throw new Exception('Missing required fields.');
        }

        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        // Use 'features' if set (it might be optional or missing if DB not updated yet, but we should handle it)
        $features = isset($_POST['features']) ? $conn->real_escape_string($_POST['features']) : '';
        $filePath = null;

        // Handle File Upload
        if (isset($_FILES['projectFile'])) {
            if ($_FILES['projectFile']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0777, true)) {
                        throw new Exception('Failed to create uploads directory.');
                    }
                }

                $fileName = basename($_FILES['projectFile']['name']);
                $uniqueName = uniqid() . '_' . $fileName; 
                $targetPath = $uploadDir . $uniqueName;

                if (move_uploaded_file($_FILES['projectFile']['tmp_name'], $targetPath)) {
                    $filePath = $targetPath;
                } else {
                    throw new Exception('Failed to move uploaded file.');
                }
            } elseif ($_FILES['projectFile']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Map PHP upload errors to messages
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize.',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE.',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
                ];
                $errMsg = $uploadErrors[$_FILES['projectFile']['error']] ?? 'Unknown upload error.';
                throw new Exception($errMsg);
            }
        }

        // Insert into database
        // We check if 'features' column exists or assume the user followed instructions. 
        // If the query fails due to missing column, the exception will catch it.
        $sql = "INSERT INTO projects (user_id, title, description, features, file_path) VALUES ('$userId', '$title', '$description', '$features', '$filePath')";

        if (!$conn->query($sql)) {
            throw new Exception('Database error: ' . $conn->error);
        }

        // Send success JSON
        echo json_encode(['success' => true, 'message' => 'Project submitted successfully']);
    } else {
        throw new Exception('Invalid request method.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
