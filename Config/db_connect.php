<?php
try {
    $host = 'localhost';
    $dbname = 'yoxplore_db';
    $username = 'root';
    $password = '';
    
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    throw new Exception("Database connection failed");
}