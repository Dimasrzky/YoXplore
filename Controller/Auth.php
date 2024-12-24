<?php
session_start();
require_once __DIR__ . '/../Config/db_connect.php';

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function login() {
        header('Content-Type: application/json');
        
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            try {
                $stmt = $this->conn->prepare("SELECT * FROM client WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: ../Client/Home.html');
                    exit();
                } else {
                    header('Location: ../Client/Login.html?error=invalid');
                    exit();
                }
            } catch(PDOException $e) {
                header('Location: ../Client/Login.html?error=system');
                exit();
            }
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

    
    public function forgotPassword() {
        header('Content-Type: application/json');
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = trim($_POST['email']);
            $newPassword = $_POST['password'];
            
            try {
                // Cek username exist
                $stmt = $this->conn->prepare("SELECT id FROM client WHERE email = ?");
                $stmt->execute([$username]);
                
                if ($stmt->rowCount() > 0) {
                    // Hash password baru
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // Update password
                    $updateStmt = $this->conn->prepare("UPDATE client SET password = ? WHERE email = ?");
                    $updateStmt->execute([$hashedPassword, $username]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Password berhasil diubah'
                    ]);
                } else {
                    
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Username tidak ditemukan'
                    ]);
                }
            } catch(PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem'
                ]);
            }
            exit();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $auth = new Auth($conn);
    $auth->login();
}
// Inisialisasi class Auth dan jalankan proses registrasi
if (isset($_POST['username'])) {
    $auth = new Auth($conn);
    $auth->register();
}

if(isset($_POST['action']) && $_POST['action'] === 'forgot') {
    $auth = new Auth($conn);
    $auth->forgotPassword();
}
?>