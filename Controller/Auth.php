<?php
// Controller/Auth.php
session_start();
require_once '../Config/db_connect.php';

class Auth {
    private $conn;
    private $table = 'client';

    public function __construct() {
        $this->conn = connectDB();
    }

    /**
     * Handle Register
     */
    public function register($userData) {
        // Validasi input
        $validation = $this->validateRegistration($userData);
        if (!$validation['status']) {
            return [
                'status' => false,
                'message' => $validation['message']
            ];
        }

        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        try {
            // Insert user data
            $stmt = $this->conn->prepare("INSERT INTO {$this->table} (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $userData['username'], $userData['email'], $hashedPassword);

            if ($stmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Registration successful! Please login.',
                    'redirect' => '/Client/login.html'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Registration failed. Please try again.'
                ];
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'status' => false,
                'message' => 'An error occurred during registration.'
            ];
        }
    }

    /**
     * Handle Login
     */
    public function login($userData) {
        if (empty($userData['email']) || empty($userData['password'])) {
            return [
                'status' => false,
                'message' => 'Email and password are required.'
            ];
        }

        try {
            $stmt = $this->conn->prepare("SELECT id, username, email, password FROM {$this->table} WHERE email = ?");
            $stmt->bind_param("s", $userData['email']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($userData['password'], $user['password'])) {
                    // Set session data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['logged_in'] = true;

                    return [
                        'status' => true,
                        'message' => 'Login successful!',
                        'redirect' => '/Client/dashboard.html',
                        'user' => [
                            'username' => $user['username'],
                            'email' => $user['email']
                        ]
                    ];
                }
            }

            return [
                'status' => false,
                'message' => 'Invalid email or password.'
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'status' => false,
                'message' => 'An error occurred during login.'
            ];
        }
    }

    /**
     * Handle Logout
     */
    public function logout() {
        // Destroy session
        session_unset();
        session_destroy();

        return [
            'status' => true,
            'message' => 'Logout successful!',
            'redirect' => '/Client/login.html'
        ];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null
        ];
    }

    /**
     * Validate Registration Data
     */
    private function validateRegistration($data) {
        $errors = [];

        // Validate username
        if (empty($data['username'])) {
            $errors[] = "Username is required";
        } elseif (strlen($data['username']) < 3) {
            $errors[] = "Username must be at least 3 characters";
        }

        // Validate email
        if (empty($data['email'])) {
            $errors[] = "Email is required";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            // Check if email exists
            $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE email = ?");
            $stmt->bind_param("s", $data['email']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "Email already exists";
            }
        }

        // Validate password
        if (empty($data['password'])) {
            $errors[] = "Password is required";
        } elseif (strlen($data['password']) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }

        // Validate password confirmation
        if ($data['password'] !== $data['com_password']) {
            $errors[] = "Passwords do not match";
        }

        if (!empty($errors)) {
            return [
                'status' => false,
                'message' => implode(", ", $errors)
            ];
        }

        return [
            'status' => true,
            'message' => 'Validation successful'
        ];
    }

    /**
     * Close database connection
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Handle HTTP requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auth = new Auth();
    $response = [];

    try {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'register':
                $response = $auth->register([
                    'username' => trim($_POST['username'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'com_password' => $_POST['com_password'] ?? ''
                ]);
                break;

            case 'login':
                $response = $auth->login([
                    'email' => trim($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? ''
                ]);
                break;

            case 'logout':
                $response = $auth->logout();
                break;

            case 'check_auth':
                $response = [
                    'status' => true,
                    'logged_in' => $auth->isLoggedIn(),
                    'user' => $auth->getCurrentUser()
                ];
                break;

            default:
                $response = [
                    'status' => false,
                    'message' => 'Invalid action'
                ];
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $response = [
            'status' => false,
            'message' => 'An error occurred'
        ];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>