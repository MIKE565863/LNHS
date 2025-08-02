<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: ../index.php');
        exit();
    }

    if (!validateEmail($email)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: ../index.php');
        exit();
    }

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];

            // Set remember me cookie if requested
            if ($remember) {
                $token = generateRandomString(32);
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                
                // Store token in database (you might want to add a remember_tokens table)
                // For now, we'll just use the session
            }

            // Create login notification
            createNotification(
                $user['id'],
                'Login Successful',
                'You have successfully logged into your account.',
                'portal'
            );

            // Redirect based on user type
            if ($user['user_type'] == 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Invalid email or password.';
            header('Location: ../index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'An error occurred. Please try again.';
        header('Location: ../index.php');
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header('Location: ../index.php');
    exit();
}
?>