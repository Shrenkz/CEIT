<?php
session_start();
require 'db_config.php'; // Include your database connection file

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get document ID and user ID from POST data
    $documentId = $_POST['document_id'];
    $userId = $_POST['user_id'];

    // Validate and sanitize inputs
    $documentId = intval($documentId);
    $userId = intval($userId);

    // Prepare SQL query to insert pinned document
    $stmt = $conn->prepare("INSERT INTO pin_documents (userId, documentId) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $documentId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
