<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "CEIT";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>