<?php
$host = "localhost";
$dbname = "clinic";
$username = "root";  // Change if your XAMPP/MySQL setup has a different user
$password = "";      // Change if your MySQL user has a password

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
