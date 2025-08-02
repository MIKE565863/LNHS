<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Side - Welcome Section -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-primary text-white">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-graduation-cap fa-5x mb-3"></i>
                        <h1 class="display-4 fw-bold">LNHS</h1>
                        <h2 class="h3">Documents Request Portal</h2>
                    </div>
                    <p class="lead mb-4">
                        Streamlined document requests for students and alumni.<br>
                        No need to visit the school - request your documents online!
                    </p>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <h5>Easy Requests</h5>
                            <p>Submit document requests online</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-tracking fa-2x mb-2"></i>
                            <h5>Track Progress</h5>
                            <p>Monitor your request status</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-bell fa-2x mb-2"></i>
                            <h5>Notifications</h5>
                            <p>Get updates via email/SMS</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100 max-w-md">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-primary">Welcome Back</h3>
                                <p class="text-muted">Sign in to your account</p>
                            </div>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form action="auth/login.php" method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>

                            <div class="text-center">
                                <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary">Register here</a></p>
                                <p class="mt-2"><a href="forgot-password.php" class="text-muted">Forgot your password?</a></p>
                            </div>

                            <hr class="my-4">

                            <div class="text-center">
                                <h6 class="text-muted mb-3">Available Documents</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <span class="badge bg-light text-dark me-1">Certificate of Enrollment</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="badge bg-light text-dark">Good Moral Certificate</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="badge bg-light text-dark me-1">Transcript of Records</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="badge bg-light text-dark">Form 137</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>