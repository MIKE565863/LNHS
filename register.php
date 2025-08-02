<?php
require_once 'includes/auth.php';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    if ($auth->isUser()) {
        header('Location: user/dashboard.php');
    } else {
        header('Location: admin/dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

// Handle registration form submission
if ($_POST) {
    // Validate required fields
    $required_fields = ['student_id', 'first_name', 'last_name', 'email', 'password', 'confirm_password', 'user_type'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $error = 'Please fill in all required fields.';
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $error = 'Passwords do not match.';
    } elseif (strlen($_POST['password']) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Prepare data for registration
        $data = [
            'student_id' => trim($_POST['student_id']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'password' => $_POST['password'],
            'user_type' => $_POST['user_type'],
            'graduation_year' => $_POST['graduation_year'] ?? null,
            'course' => trim($_POST['course'])
        ];
        
        $result = $auth->registerUser($data);
        
        if ($result['success']) {
            $success = $result['message'] . ' You can now login with your credentials.';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="auth-card">
                        <div class="auth-header">
                            <h3><i class="fas fa-user-plus me-2"></i>Create Account</h3>
                            <p class="mb-0">Register to access document request portal</p>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="student_id" class="form-label">
                                            <i class="fas fa-id-card me-2"></i>Student ID *
                                        </label>
                                        <input type="text" class="form-control" id="student_id" name="student_id" 
                                               placeholder="Enter your student ID" required 
                                               value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="user_type" class="form-label">
                                            <i class="fas fa-users me-2"></i>User Type *
                                        </label>
                                        <select class="form-select" id="user_type" name="user_type" required>
                                            <option value="">Select type</option>
                                            <option value="student" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'student') ? 'selected' : ''; ?>>Current Student</option>
                                            <option value="alumni" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">
                                            <i class="fas fa-user me-2"></i>First Name *
                                        </label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               placeholder="Enter your first name" required 
                                               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">
                                            <i class="fas fa-user me-2"></i>Last Name *
                                        </label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               placeholder="Enter your last name" required 
                                               value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email Address *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Enter your email" required 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Phone Number
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="Enter your phone number" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="course" class="form-label">
                                            <i class="fas fa-book me-2"></i>Course/Program
                                        </label>
                                        <input type="text" class="form-control" id="course" name="course" 
                                               placeholder="e.g., Computer Science" 
                                               value="<?php echo isset($_POST['course']) ? htmlspecialchars($_POST['course']) : ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3" id="graduation_year_field" style="display: none;">
                                        <label for="graduation_year" class="form-label">
                                            <i class="fas fa-calendar me-2"></i>Graduation Year
                                        </label>
                                        <input type="number" class="form-control" id="graduation_year" name="graduation_year" 
                                               placeholder="e.g., 2023" min="1990" max="2030"
                                               value="<?php echo isset($_POST['graduation_year']) ? htmlspecialchars($_POST['graduation_year']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password *
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Create a password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Minimum 6 characters</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Confirm Password *
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   placeholder="Confirm your password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <p class="mb-2">Already have an account?</p>
                                <a href="login.php" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login Here
                                </a>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="index.php" class="text-muted">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePasswordVisibility(inputId, buttonId) {
            document.getElementById(buttonId).addEventListener('click', function() {
                const password = document.getElementById(inputId);
                const icon = this.querySelector('i');
                
                if (password.type === 'password') {
                    password.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    password.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
        
        togglePasswordVisibility('password', 'togglePassword');
        togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');
        
        // Show/hide graduation year field based on user type
        document.getElementById('user_type').addEventListener('change', function() {
            const graduationField = document.getElementById('graduation_year_field');
            const graduationInput = document.getElementById('graduation_year');
            
            if (this.value === 'alumni') {
                graduationField.style.display = 'block';
                graduationInput.required = true;
            } else {
                graduationField.style.display = 'none';
                graduationInput.required = false;
                graduationInput.value = '';
            }
        });
        
        // Check if alumni is selected on page load
        if (document.getElementById('user_type').value === 'alumni') {
            document.getElementById('graduation_year_field').style.display = 'block';
            document.getElementById('graduation_year').required = true;
        }
    </script>
</body>
</html>