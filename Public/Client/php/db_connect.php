<?php
$host = 'localhost';
$dbname = 'yoxplore';
$username = 'root';
$password = ''; // Sesuaikan dengan password database Anda jika ada

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>