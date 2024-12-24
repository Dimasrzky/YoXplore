<?php
try {
    // Konfigurasi untuk Docker
    $host = 'db';  // nama service di docker-compose
    $dbname = 'yoxplore';
    $username = 'root';
    $password = '';
    
    // Buat koneksi PDO
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}