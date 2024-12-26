<?php
$host = "localhost";
$database = "yoxplore";
$username = "root";
$password = "";

try {
    $conn = mysqli_connect($host, $username, $password, $database);
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8mb4");
} catch (Exception $e) {
    die(json_encode(['error' => true, 'message' => $e->getMessage()]));
}