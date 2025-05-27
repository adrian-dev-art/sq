<?php
// db_connection.php
$dbHost = "localhost";
$dbUser = "root";
$dbPass = ""; // Your database password, if any
$dbName = "login"; // Your database name

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>