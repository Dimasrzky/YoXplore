<?php
session_start();
require_once '/Client/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['com_password'];
    $error = false;
    $error_message = '';

    // Validasi input kosong
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = true;
        $error_message = "Semua bidang harus diisi!";
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $error_message = "Format email tidak valid!";
    }

    // Validasi password match
    if ($password !== $confirm_password) {
        $error = true;
        $error_message = "Password dan konfirmasi password tidak sesuai!";
    }

    // Validasi panjang password
    if (strlen($password) < 6) {
        $error = true;
        $error_message = "Password harus memiliki panjang minimal 6 karakter!";
    }

    // Cek email sudah terdaftar
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = true;
            $error_message = "Email sudah terdaftar!";
        }
    } catch(PDOException $e) {
        $error = true;
        $error_message = "Terjadi kesalahan: " . $e->getMessage();
    }

    if (!$error) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $hashed_password]);
            
            $_SESSION['success_message'] = "Akun berhasil dibuat!";
            header("Location: /Client/Login.html");
            exit();
        } catch(PDOException $e) {
            $error_message = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Your Account</title>
    <link rel="icon" href="/Image/Logo Yoxplore.png" type="image/png">
    <link rel="stylesheet" href="/Style/Login.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="container">
        <div class="background-section">
            <img src="/Image/Yoxplore logo text.png">
        </div>
        
        <div class="login-section">
            <h2>Create your account</h2>
            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="input-group">
                    <input type="text" id="username" name="username" 
                           placeholder=" " value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                    <label for="username">Username</label>
                </div>
                
                <div class="input-group">
                    <input type="text" id="email" name="email" 
                           placeholder=" " value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    <label for="email">Email</label>
                </div>
                
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Password</label>
                </div>
                
                <div class="input-group">
                    <input type="password" id="com_password" name="com_password" placeholder=" " required>
                    <label for="com_password">Confirm Password</label>
                </div>
                
                <button type="submit">Create Account</button>
            </form>
            
            <a href="/Client/Login.html" class="create-account">Back to login page</a>
        </div>
    </div>
</body>
</html>