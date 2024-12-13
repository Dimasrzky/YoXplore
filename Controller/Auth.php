<?php
// Controller/Auth.php

require_once '../Config/db_connect.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Create session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                
                return ['status' => true, 'message' => 'Login successful', 'redirect' => '/Client/Home.html'];
            }
            
            return ['status' => false, 'message' => 'Invalid email or password'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function register($email, $password, $fullName) {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM client WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['status' => false, 'message' => 'Email already registered'];
            }
            
            // Hash password and create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO client (email, password, full_name) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashedPassword, $fullName]);
            
            return ['status' => true, 'message' => 'Registration successful'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function handleGoogleLogin($googleId, $email, $fullName) {
        try {
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE google_id = ? OR email = ?");
            $stmt->execute([$googleId, $email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // Create new user
                $stmt = $this->db->prepare("INSERT INTO users (google_id, email, full_name, is_verified) VALUES (?, ?, ?, TRUE)");
                $stmt->execute([$googleId, $email, $fullName]);
                $userId = $this->db->lastInsertId();
            } else {
                $userId = $user['id'];
            }
            
            // Create session
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            
            return ['status' => true, 'message' => 'Google login successful', 'redirect' => '/Client/Home.html'];
        } catch (PDOException $e) {
            return ['status' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $response = ['status' => false, 'message' => 'Invalid request'];
    
    switch ($_POST['action']) {
        case 'login':
            if (isset($_POST['email']) && isset($_POST['password'])) {
                $response = $auth->login($_POST['email'], $_POST['password']);
            }
            break;
            
        case 'register':
            if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['full_name'])) {
                $response = $auth->register($_POST['email'], $_POST['password'], $_POST['full_name']);
            }
            break;
            
        case 'google_login':
            if (isset($_POST['google_id']) && isset($_POST['email']) && isset($_POST['full_name'])) {
                $response = $auth->handleGoogleLogin($_POST['google_id'], $_POST['email'], $_POST['full_name']);
            }
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}