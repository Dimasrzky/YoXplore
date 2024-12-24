<?php
class DatabaseConnection {
    private $host;
    private $username;
    private $password;
    private $database;
    private $conn;

    public function __construct() {
        // Check if running in Docker
        if (getenv('DOCKER_ENV')) {
            $this->host = 'db';        // nama service di docker-compose
            $this->username = 'root';
            $this->password = '';
            $this->database = 'yoxplore';
        } else {
            // XAMPP configuration
            $this->host = 'localhost';
            $this->username = 'root';
            $this->password = '';
            $this->database = 'yoxplore';
        }
    }

    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->database",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            return false;
        }
    }
}
?>