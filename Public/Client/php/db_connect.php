<?php
$host = 'localhost';  // Biasanya 'localhost' untuk pengembangan lokal
$dbname = 'yoxplore';
$username = 'root';   // Ganti dengan username database Anda
$password = '';       // Ganti dengan password database Anda

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set mode error PDO ke exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
    die();
}
?>