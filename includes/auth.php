<?php
class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($firstName, $lastName, $email, $password) {
        try {
            // Validate input
            if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                return ["success" => false, "message" => "All fields are required"];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ["success" => false, "message" => "Invalid email format"];
            }

            // Check if email exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return ["success" => false, "message" => "Email already exists"];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));

            // Insert user
            $stmt = $this->conn->prepare("
                INSERT INTO users (first_name, last_name, email, password, verification_token, email_verified)
                VALUES (?, ?, ?, ?, ?, TRUE)
            ");

            $stmt->execute([
                $firstName,
                $lastName,
                $email,
                $hashedPassword,
                $verificationToken
            ]);

            return ["success" => true, "message" => "Registration successful. Please login."];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Registration failed: " . $e->getMessage()];
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, password, email_verified, first_name 
                FROM users 
                WHERE email = ?
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ["success" => false, "message" => "Invalid email or password"];
            }

            if (!$user['email_verified']) {
                return ["success" => false, "message" => "Please verify your email before logging in"];
            }

            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                return ["success" => true, "message" => "Login successful"];
            }

            return ["success" => false, "message" => "Invalid email or password"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Login failed: " . $e->getMessage()];
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_start();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>