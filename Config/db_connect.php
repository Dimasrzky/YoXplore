<?php
function connectDB() {
    $host = "localhost";
    $username = "your_username";
    $password = "your_password";
    $database = "yoxplore";

    // Membuat koneksi
    $conn = new mysqli($host, $username, $password, $database);

    // Cek koneksi
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>