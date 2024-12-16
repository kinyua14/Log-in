<?php
$host = "localhost";
$dbUserName = "root"; // Default username for XAMPP/WAMP
$dbPassword = ""; // Leave empty for XAMPP/WAMP
$dbName = "registration";

$conn = mysqli_connect($host, $dbUserName, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Database connected successfully!";
