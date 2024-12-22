<?php
header('Content-Type: application/json');
require_once('../Config/db_connect.php');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Validasi input
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception('Semua field harus diisi');
    }

    // Validasi email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }

    // Cek apakah username sudah ada
    $stmt = $conn->prepare("SELECT id FROM client WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Username sudah digunakan');
    }

    // Cek apakah email sudah ada
    $stmt = $conn->prepare("SELECT id FROM client WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email sudah digunakan');
    }

    // Hash password
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert user baru
    $stmt = $conn->prepare("
        INSERT INTO client (username, email, password, created_at)
        VALUES (?, ?, ?, CURRENT_TIMESTAMP)
    ");
    
    $stmt->execute([
        $_POST['username'],
        $_POST['email'],
        $hashedPassword
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'User berhasil ditambahkan'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}