<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="landing-page">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-graduation-cap me-2"></i>
                    LNHS Documents Request Portal
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="login.php">Student/Alumni Login</a>
                    <a class="nav-link" href="admin/login.php">Admin Login</a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="hero-section">
            <div class="container">
                <div class="row align-items-center min-vh-100">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <h1 class="display-4 fw-bold text-primary mb-4">
                                Request Your Documents Online
                            </h1>
                            <p class="lead mb-4">
                                No need to visit the school! Request your certificates and documents online through our secure portal. Track your requests and get notified when they're ready.
                            </p>
                            <div class="d-grid gap-3 d-md-flex">
                                <a href="login.php" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Student/Alumni Login
                                </a>
                                <a href="register.php" class="btn btn-outline-primary btn-lg px-4">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Register Now
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image text-center">
                            <i class="fas fa-file-alt hero-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section py-5 bg-light">
            <div class="container">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <h2 class="fw-bold">Available Documents</h2>
                        <p class="text-muted">Request these documents online with just a few clicks</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-certificate feature-icon text-primary mb-3"></i>
                                <h5 class="card-title">Certificate of Enrollment</h5>
                                <p class="card-text text-muted">Official enrollment certificate for current students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-award feature-icon text-success mb-3"></i>
                                <h5 class="card-title">Good Moral Certificate</h5>
                                <p class="card-text text-muted">Character certificate for students and alumni</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-scroll feature-icon text-info mb-3"></i>
                                <h5 class="card-title">Transcript of Records</h5>
                                <p class="card-text text-muted">Official academic records and grades</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-diploma feature-icon text-warning mb-3"></i>
                                <h5 class="card-title">Diploma Copy</h5>
                                <p class="card-text text-muted">Certified true copy of diploma</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works Section -->
        <div class="process-section py-5">
            <div class="container">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <h2 class="fw-bold">How It Works</h2>
                        <p class="text-muted">Simple steps to request your documents</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="process-step text-center">
                            <div class="step-number">1</div>
                            <h5>Register/Login</h5>
                            <p class="text-muted">Create an account or login with your student ID</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="process-step text-center">
                            <div class="step-number">2</div>
                            <h5>Submit Request</h5>
                            <p class="text-muted">Fill out the form and upload required documents</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="process-step text-center">
                            <div class="step-number">3</div>
                            <h5>Track Status</h5>
                            <p class="text-muted">Monitor your request progress in real-time</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="process-step text-center">
                            <div class="step-number">4</div>
                            <h5>Pickup/Receive</h5>
                            <p class="text-muted">Get notified when your documents are ready</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-dark text-white py-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-graduation-cap me-2"></i>LNHS Documents Request Portal</h5>
                        <p class="text-muted">Making document requests easier for students and alumni.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="text-muted mb-0">© 2024 LNHS. All rights reserved.</p>
                        <p class="text-muted mb-0">
                            <a href="admin/login.php" class="text-light">Admin Access</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>