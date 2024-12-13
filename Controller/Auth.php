\<?php
// auth.php
require_once 'Config/db_connect.php';

class Auth {
    private $conn;

    public function __construct() {
        $this->conn = connectDB();
    }

    // Handle Register
    public function register($username, $email, $password, $confirm_password) {
        // Validasi input
        $error = $this->validateRegistration($username, $email, $password, $confirm_password);
        
        if (!empty($error)) {
            return [
                'status' => 'error',
                'message' => $error
            ];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert ke database
        $stmt = $this->conn->prepare("INSERT INTO client (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            return [
                'status' => 'success',
                'message' => 'Registration successful! Please login.'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    // Handle Login
    public function login($email, $password) {
        // Validasi input
        if (empty($email) || empty($password)) {
            return [
                'status' => 'error',
                'message' => 'Email and password are required'
            ];
        }

        // Cek user di database
        $stmt = $this->conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                return [
                    'status' => 'success',
                    'message' => 'Login successful!',
                    'redirect' => 'dashboard.php'
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Invalid email or password'
        ];
    }

    // Validasi Register
    private function validateRegistration($username, $email, $password, $confirm_password) {
        $error_message = "";

        // Validasi username
        if (empty($username)) {
            $error_message .= "Username wajib diisi. ";
        } elseif (strlen($username) < 3) {
            $error_message .= "Username minimal 3 karakter. ";
        }

        // Validasi email
        if (empty($email)) {
            $error_message .= "Email wajib diisi. ";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message .= "Format email tidak valid. ";
        } else {
            // Cek email sudah terdaftar
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error_message .= "Email sudah terdaftar. ";
            }
        }

        // Validasi password
        if (empty($password)) {
            $error_message .= "Password wajib diisi. ";
        } elseif (strlen($password) < 6) {
            $error_message .= "Password minimal 6 karakter. ";
        }

        // Cek konfirmasi password
        if ($password !== $confirm_password) {
            $error_message .= "Konfirmasi password tidak sesuai. ";
        }

        return $error_message;
    }

    // Logout
    public function logout() {
        session_start();
        session_destroy();
        return [
            'status' => 'success',
            'message' => 'Logout successful',
            'redirect' => 'login.html'
        ];
    }

    // Destructor untuk menutup koneksi
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Handle requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auth = new Auth();
    $response = [];

    // Determine action (login or register)
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'register':
            $response = $auth->register(
                trim($_POST['username']),
                trim($_POST['email']),
                $_POST['password'],
                $_POST['com_password']
            );
            break;

        case 'login':
            $response = $auth->login(
                trim($_POST['email']),
                $_POST['password']
            );
            break;

        case 'logout':
            $response = $auth->logout();
            break;

        default:
            $response = [
                'status' => 'error',
                'message' => 'Invalid action'
            ];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>