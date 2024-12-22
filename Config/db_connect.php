<?php
try {
    $host = 'localhost';
    $dbname = 'yoxplore';
    $username = 'root';
    $password = '';
    
    // Tambahkan opsi PDO untuk menangani timeout
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 60,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_MAX_BUFFER_SIZE => 1024 * 1024 * 16  // 16MB buffer
    );

    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        $options
    );

} catch(PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}