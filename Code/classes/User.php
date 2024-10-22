<?php

class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $first_name;
    public $last_name;
    public $email;
    public $password_hash;
    public $phone_number;
    public $role_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public static function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public function register($first_name, $last_name, $email, $password, $phone) {
        try {
            // Check if email exists
            $query = "SELECT email FROM " . $this->table_name . " WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                return array("success" => false, "message" => "Dit e-mailadres is al geregistreerd");
            }

            // Create new user
            $query = "INSERT INTO " . $this->table_name . " 
                    (first_name, last_name, email, password_hash, phone_number, role_id) 
                    VALUES (?, ?, ?, ?, ?, 2)";
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$first_name, $last_name, $email, $password_hash, $phone]);

            return array("success" => true, "message" => "Registratie succesvol! U kunt nu inloggen.");
        } catch(PDOException $e) {
            return array("success" => false, "message" => "Er is een fout opgetreden: " . $e->getMessage());
        }
    }

    public function login($email, $password) {
        try {
            $query = "SELECT user_id, password_hash, role_id FROM " . $this->table_name . " WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role_id'] = $user['role_id'];
                return array(
                    "success" => true,
                    "role_id" => $user['role_id']
                );
            }
            return array("success" => false, "message" => "Ongeldige inloggegevens");
        } catch(PDOException $e) {
            return array("success" => false, "message" => "Er is een fout opgetreden: " . $e->getMessage());
        }
    }

    public function logout() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        session_destroy();
    }
}