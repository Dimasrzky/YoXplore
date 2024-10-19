<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $number_phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (First_name, Last_name, Email, phone, Password) VALUES (:first_name, :last_name, :email, :phone, :password)");
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':number_phone', $number_phone);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Register telah berhasil, silahkan kembali ke login page');
                    window.location.href = '../page/Login.html';
                  </script>";
        } else {
            echo "<script>
                    alert('Terjadi kesalahan saat registrasi.');
                    window.location.href = '../page/Register.html';
                  </script>";
        }
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<script>
                    alert('Email sudah terdaftar. Silakan gunakan email lain.');
                    window.location.href = '../page/Register.html';
                  </script>";
        } else {
            echo "<script>
                    alert('Error: " . $e->getMessage() . "');
                    window.location.href = '../page/Register.html';
                  </script>";
        }
    }
}
?>