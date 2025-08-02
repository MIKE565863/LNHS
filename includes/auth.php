<?php
session_start();
require_once '../config/database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $this->conn = getDB();
    }
    
    // User login (students/alumni)
    public function loginUser($student_id, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'user';
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_category'] = $user['user_type'];
                return ['success' => true, 'user' => $user];
            }
            
            return ['success' => false, 'message' => 'Invalid student ID or password'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
        }
    }
    
    // Admin login
    public function loginAdmin($username, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                return ['success' => true, 'admin' => $admin];
            }
            
            return ['success' => false, 'message' => 'Invalid username or password'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
        }
    }
    
    // User registration
    public function registerUser($data) {
        try {
            // Check if student ID already exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE student_id = ? OR email = ?");
            $stmt->execute([$data['student_id'], $data['email']]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Student ID or email already registered'];
            }
            
            // Hash password
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->conn->prepare("
                INSERT INTO users (student_id, first_name, last_name, email, phone, password, user_type, graduation_year, course) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['student_id'],
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $hashed_password,
                $data['user_type'],
                $data['graduation_year'],
                $data['course']
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Registration successful'];
            }
            
            return ['success' => false, 'message' => 'Registration failed'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
        }
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
    }
    
    // Check if admin is logged in
    public function isAdmin() {
        return isset($_SESSION['admin_id']) && $_SESSION['user_type'] === 'admin';
    }
    
    // Check if regular user is logged in
    public function isUser() {
        return isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'user';
    }
    
    // Get current user info
    public function getCurrentUser() {
        if ($this->isUser()) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        }
        return null;
    }
    
    // Get current admin info
    public function getCurrentAdmin() {
        if ($this->isAdmin()) {
            $stmt = $this->conn->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            return $stmt->fetch();
        }
        return null;
    }
    
    // Logout
    public function logout() {
        session_destroy();
        return true;
    }
    
    // Require user login
    public function requireUser() {
        if (!$this->isUser()) {
            header('Location: ../login.php');
            exit();
        }
    }
    
    // Require admin login
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: ../admin/login.php');
            exit();
        }
    }
}

// Initialize auth object
$auth = new Auth();
?>