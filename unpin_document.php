<?php
session_start();
include 'db_config.php'; // Ensure this file sets up the $conn variable

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Check if documentId is set in the request
    if (isset($_POST['documentId']) && !empty(trim($_POST['documentId']))) {
        $document_id = trim($_POST['documentId']);

        // Prepare the SQL statement to delete the pinned document
        $sql_unpin = "DELETE FROM pin_documents WHERE userId = ? AND documentId = ?";
        $stmt_unpin = $conn->prepare($sql_unpin);

        if ($stmt_unpin) {
            $stmt_unpin->bind_param("ii", $user_id, $document_id);

            // Execute the statement and check for success
            if ($stmt_unpin->execute()) {
                echo htmlspecialchars("Document successfully unpinned.");
            } else {
                http_response_code(500); // Internal Server Error
                echo htmlspecialchars("Error executing the SQL statement: " . $stmt_unpin->error);
            }
            $stmt_unpin->close();
        } else {
            http_response_code(500); // Internal Server Error
            echo htmlspecialchars("Error preparing the SQL statement: " . $conn->error);
        }
    } else {
        http_response_code(400); // Bad Request
        echo htmlspecialchars("Invalid request. Document ID is missing or empty.");
    }
} else {
    http_response_code(403); // Forbidden
    echo htmlspecialchars("User not logged in.");
}

$conn->close();
?>
