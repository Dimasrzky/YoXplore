<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['com_password'];
    $error = false;
    $error_message = "";

    // Validate username
    if (empty($username)) {
        $error = true;
        $error_message .= "Username is required. ";
    }

    // Validate email
    if (empty($email)) {
        $error = true;
        $error_message .= "Email is required. ";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $error_message .= "Invalid email format. ";
    }

    // Validate password
    if (empty($password)) {
        $error = true;
        $error_message .= "Password is required. ";
    } elseif (strlen($password) < 6) {
        $error = true;
        $error_message .= "Password must be at least 6 characters. ";
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = true;
        $error_message .= "Passwords do not match. ";
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM client WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $error = true;
        $error_message .= "Username already exists. ";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM client WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = true;
        $error_message .= "Email already exists. ";
    }

    if (!$error) {
        try {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO client (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $hashed_password]);

            // Redirect to login page with success message
            header("Location: /Client/Login.html?registration=success");
            exit();
        } catch(PDOException $e) {
            $error_message = "Registration failed. Please try again later.";
        }
    }

    // If there were errors, redirect back to registration page with error message
    header("Location: /Client/Register.html?error=" . urlencode($error_message));
    exit();
}
?>