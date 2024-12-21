<?php
$host = 'localhost';
$dbname = 'yoxplore';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => "Connection failed: " . $e->getMessage()
    ]));
}