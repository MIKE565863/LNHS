<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $user_type = sanitize_input($_POST['user_type']);
    $student_id = isset($_POST['student_id']) ? sanitize_input($_POST['student_id']) : null;
    $contact_number = isset($_POST['contact_number']) ? sanitize_input($_POST['contact_number']) : null;
    $address = isset($_POST['address']) ? sanitize_input($_POST['address']) : null;
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']);

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($user_type) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: ../register.php');
        exit();
    }

    // Validate email
    if (!validateEmail($email)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: ../register.php');
        exit();
    }

    // Validate user type
    if (!in_array($user_type, ['student', 'alumni'])) {
        $_SESSION['error'] = 'Please select a valid user type.';
        header('Location: ../register.php');
        exit();
    }

    // Validate student ID for students
    if ($user_type == 'student' && empty($student_id)) {
        $_SESSION['error'] = 'Student ID is required for students.';
        header('Location: ../register.php');
        exit();
    }

    // Validate password
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long.';
        header('Location: ../register.php');
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: ../register.php');
        exit();
    }

    // Validate terms
    if (!$terms) {
        $_SESSION['error'] = 'You must agree to the terms and conditions.';
        header('Location: ../register.php');
        exit();
    }

    // Validate contact number if provided
    if (!empty($contact_number) && !validatePhone($contact_number)) {
        $_SESSION['error'] = 'Please enter a valid Philippine phone number (e.g., 09123456789).';
        header('Location: ../register.php');
        exit();
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Email address is already registered.';
            header('Location: ../register.php');
            exit();
        }

        // Check if student ID already exists (for students)
        if ($user_type == 'student' && !empty($student_id)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                $_SESSION['error'] = 'Student ID is already registered.';
                header('Location: ../register.php');
                exit();
            }
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password, first_name, last_name, user_type, student_id, contact_number, address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $email,
            $hashed_password,
            $first_name,
            $last_name,
            $user_type,
            $student_id,
            $contact_number,
            $address
        ]);

        $user_id = $pdo->lastInsertId();

        // Create welcome notification
        createNotification(
            $user_id,
            'Welcome to LNHS Documents Request Portal',
            'Thank you for registering! You can now request documents online.',
            'portal'
        );

        // Send welcome email (mock)
        sendEmailNotification(
            $email,
            'Welcome to LNHS Documents Request Portal',
            "Dear $first_name $last_name,\n\nWelcome to the LNHS Documents Request Portal! You can now request documents online without visiting the school.\n\nBest regards,\nLNHS Administration"
        );

        $_SESSION['success'] = 'Registration successful! You can now log in to your account.';
        header('Location: ../index.php');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = 'An error occurred during registration. Please try again.';
        header('Location: ../register.php');
        exit();
    }
} else {
    // If not POST request, redirect to registration page
    header('Location: ../register.php');
    exit();
}
?>