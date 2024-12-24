<?php
// Simpan sebagai debug.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'db';
$username = 'root';
$password = '';
$database = 'yoxplore';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connected successfully<br>";
    
    // Test query ke tabel client
    $stmt = $conn->query("SELECT * FROM client");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total clients: " . count($results);
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>