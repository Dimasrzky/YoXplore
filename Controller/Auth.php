<?php
session_start();
require_once __DIR__ . '/../Config/db_connect.php';

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $error = false;
            $error_message = "";

            // Validasi input
            if (empty($email)) {
                $error = true;
                $error_message .= "Email harus diisi. ";
            }

            if (empty($password)) {
                $error = true;
                $error_message .= "Password harus diisi. ";
            }

            if (!$error) {
                try {
                    // Cek user dengan email yang diinputkan
                    $stmt = $this->conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    
                    if ($stmt->rowCount() > 0) {
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Verifikasi password
                        if (password_verify($password, $user['password'])) {
                            // Set session
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                            
                            // Redirect ke halaman home
                            header("Location: /YoXplore/Client/Home.html");
                            exit();
                        } else {
                            $error_message = "Password yang Anda masukkan salah.";
                        }
                    } else {
                        $error_message = "Email tidak ditemukan.";
                    }
                } catch(PDOException $e) {
                    $error_message = "Login gagal. Silakan coba lagi nanti.";
                }
            }

            // Jika ada error, kembali ke halaman login dengan pesan error
            header("Location: /YoXplore/Client/Login.html?error=" . urlencode($error_message));
            exit();
        }
    }

    public function register() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['com_password'];
            $error = false;
            $error_message = "";

            // Validasi username
            if (empty($username)) {
                $error = true;
                $error_message .= "Username harus diisi. ";
            }

            // Validasi email
            if (empty($email)) {
                $error = true;
                $error_message .= "Email harus diisi. ";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = true;
                $error_message .= "Format email tidak valid. ";
            }

            // Validasi password
            if (empty($password)) {
                $error = true;
                $error_message .= "Password harus diisi. ";
            } elseif (strlen($password) < 6) {
                $error = true;
                $error_message .= "Password minimal 6 karakter. ";
            }

            // Cek kecocokan password
            if ($password !== $confirm_password) {
                $error = true;
                $error_message .= "Password tidak cocok. ";
            }

            // Cek username sudah ada atau belum
            $stmt = $this->conn->prepare("SELECT id FROM client WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $error = true;
                $error_message .= "Username sudah digunakan. ";
            }

            // Cek email sudah ada atau belum
            $stmt = $this->conn->prepare("SELECT id FROM client WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = true;
                $error_message .= "Email sudah digunakan. ";
            }

            if (!$error) {
                try {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Masukkan user baru
                    $stmt = $this->conn->prepare("INSERT INTO client (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$username, $email, $hashed_password]);

                    // Redirect ke halaman login dengan pesan sukses
                    // Menggunakan path absolut dari root website
                    header("Location: /YoXplore/Client/Login.html?registration=success");
                    exit();
                } catch(PDOException $e) {
                    $error_message = "Registrasi gagal. Silakan coba lagi nanti.";
                }
            }

            // Jika ada error, kembali ke halaman registrasi dengan pesan error
            header("Location: /YoXplore/Client/Register.html?error=" . urlencode($error_message));
            exit();
        }
    }
}

// Handler untuk request
$auth = new Auth($conn);

// Cek tipe action yang diminta
if (isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'login':
            $auth->login();
            break;
        case 'register':
            $auth->register();
            break;
    }
}
?>