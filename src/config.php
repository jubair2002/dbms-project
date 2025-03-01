<?php
// config.php - Database configuration

$servername = "localhost";
$username = "root";  // Default username for WAMP/XAMPP
$password = "";      // Default password for WAMP/XAMPP
$dbname = "testdb";  // Change this to your actual database name

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
