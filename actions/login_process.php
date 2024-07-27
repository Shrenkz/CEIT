<?php
session_start();
include 'db_config.php'; // Include your database configuration

// Get form input
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Prepare SQL query
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verify user and password
if ($user && $password) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    header("Location: ../index.php"); // Redirect to the main page or dashboard
    exit();
} else {
    echo "Invalid email or password.";
}

// Close connection
$stmt->close();
$conn->close();
?>
