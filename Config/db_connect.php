<?php
$servername = "db";  // Docker service name
$username = "root";
$password = "";      // Empty password
$dbname = "yoxplore";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} catch(PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}
?>