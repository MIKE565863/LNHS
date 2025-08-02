<?php
require_once '../includes/auth.php';
$auth->requireUser();

$user = $auth->getCurrentUser();
$conn = getDB();

$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    // Validate required fields
    if (empty($_POST['document_type']) || empty($_POST['purpose']) || empty($_POST['preferred_release_date'])) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Generate unique request number
            $request_number = 'REQ-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Check if request number already exists
            $stmt = $conn->prepare("SELECT id FROM document_requests WHERE request_number = ?");
            $stmt->execute([$request_number]);
            while ($stmt->fetch()) {
                $request_number = 'REQ-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $stmt = $conn->prepare("SELECT id FROM document_requests WHERE request_number = ?");
                $stmt->execute([$request_number]);
            }
            
            // Insert document request
            $stmt = $conn->prepare("
                INSERT INTO document_requests (user_id, request_number, document_type, purpose, preferred_release_date, additional_notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $result = $stmt->execute([
                $user['id'],
                $request_number,
                $_POST['document_type'],
                $_POST['purpose'],
                $_POST['preferred_release_date'],
                $_POST['additional_notes'] ?? ''
            ]);
            
            if ($result) {
                $request_id = $conn->lastInsertId();
                
                // Handle file uploads
                if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
                    $upload_dir = '../uploads/requests/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
                    $max_file_size = 5 * 1024 * 1024; // 5MB
                    
                    foreach ($_FILES['documents']['name'] as $key => $filename) {
                        if (!empty($filename)) {
                            $file_tmp = $_FILES['documents']['tmp_name'][$key];
                            $file_size = $_FILES['documents']['size'][$key];
                            $file_error = $_FILES['documents']['error'][$key];
                            
                            if ($file_error === UPLOAD_ERR_OK) {
                                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                
                                if (in_array($file_ext, $allowed_types) && $file_size <= $max_file_size) {
                                    $new_filename = $request_number . '_' . time() . '_' . $key . '.' . $file_ext;
                                    $file_path = $upload_dir . $new_filename;
                                    
                                    if (move_uploaded_file($file_tmp, $file_path)) {
                                        // Save file info to database
                                        $stmt = $conn->prepare("
                                            INSERT INTO document_uploads (request_id, file_name, file_path, file_type, file_size, upload_type) 
                                            VALUES (?, ?, ?, ?, ?, 'requirement')
                                        ");
                                        $stmt->execute([
                                            $request_id,
                                            $filename,
                                            $file_path,
                                            $file_ext,
                                            $file_size
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                
                $success = "Document request submitted successfully! Your request number is: <strong>$request_number</strong>";
                
                // Clear form data
                $_POST = [];
            } else {
                $error = 'Failed to submit request. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Document - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                LNHS Documents Portal
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="request-document.php">
                                <i class="fas fa-plus-circle"></i>
                                New Request
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my-requests.php">
                                <i class="fas fa-file-alt"></i>
                                My Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="track-request.php">
                                <i class="fas fa-search"></i>
                                Track Request
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notifications.php">
                                <i class="fas fa-bell"></i>
                                Notifications
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Request Document</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <hr>
                        <div class="d-flex gap-2">
                            <a href="my-requests.php" class="btn btn-primary">
                                <i class="fas fa-file-alt me-2"></i>View My Requests
                            </a>
                            <a href="request-document.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Submit Another Request
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Request Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Document Request Form</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data" id="requestForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="document_type" class="form-label">
                                                <i class="fas fa-certificate me-2"></i>Document Type *
                                            </label>
                                            <select class="form-select" id="document_type" name="document_type" required>
                                                <option value="">Select document type</option>
                                                <option value="certificate_of_enrollment" <?php echo (isset($_POST['document_type']) && $_POST['document_type'] === 'certificate_of_enrollment') ? 'selected' : ''; ?>>Certificate of Enrollment</option>
                                                <option value="good_moral_certificate" <?php echo (isset($_POST['document_type']) && $_POST['document_type'] === 'good_moral_certificate') ? 'selected' : ''; ?>>Good Moral Certificate</option>
                                                <option value="transcript_of_records" <?php echo (isset($_POST['document_type']) && $_POST['document_type'] === 'transcript_of_records') ? 'selected' : ''; ?>>Transcript of Records</option>
                                                <option value="diploma_copy" <?php echo (isset($_POST['document_type']) && $_POST['document_type'] === 'diploma_copy') ? 'selected' : ''; ?>>Diploma Copy</option>
                                                <option value="other" <?php echo (isset($_POST['document_type']) && $_POST['document_type'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="preferred_release_date" class="form-label">
                                                <i class="fas fa-calendar me-2"></i>Preferred Release Date *
                                            </label>
                                            <input type="date" class="form-control" id="preferred_release_date" name="preferred_release_date" 
                                                   min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required
                                                   value="<?php echo isset($_POST['preferred_release_date']) ? $_POST['preferred_release_date'] : ''; ?>">
                                            <small class="form-text text-muted">Minimum processing time is 3 business days</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="purpose" class="form-label">
                                            <i class="fas fa-info-circle me-2"></i>Purpose of Request *
                                        </label>
                                        <textarea class="form-control" id="purpose" name="purpose" rows="3" 
                                                  placeholder="Please specify the purpose of your document request" required><?php echo isset($_POST['purpose']) ? htmlspecialchars($_POST['purpose']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="additional_notes" class="form-label">
                                            <i class="fas fa-sticky-note me-2"></i>Additional Notes
                                        </label>
                                        <textarea class="form-control" id="additional_notes" name="additional_notes" rows="2" 
                                                  placeholder="Any additional information or special instructions"><?php echo isset($_POST['additional_notes']) ? htmlspecialchars($_POST['additional_notes']) : ''; ?></textarea>
                                    </div>
                                    
                                    <!-- File Upload Section -->
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-upload me-2"></i>Upload Required Documents
                                        </label>
                                        <div class="file-upload-area" id="fileUploadArea">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5>Drag and drop files here or click to browse</h5>
                                            <p class="text-muted mb-0">
                                                Upload valid ID, requirements, or supporting documents<br>
                                                <small>Supported formats: JPG, PNG, PDF, DOC, DOCX (Max 5MB per file)</small>
                                            </p>
                                            <input type="file" class="form-control d-none" id="documents" name="documents[]" 
                                                   multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                                        </div>
                                        
                                        <!-- File Preview -->
                                        <div id="filePreview" class="mt-3"></div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Information Panel -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Important Information</h6>
                            </div>
                            <div class="card-body">
                                <h6>Processing Time:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-clock me-2 text-primary"></i>Certificate of Enrollment: 3-5 days</li>
                                    <li><i class="fas fa-clock me-2 text-primary"></i>Good Moral Certificate: 5-7 days</li>
                                    <li><i class="fas fa-clock me-2 text-primary"></i>Transcript of Records: 7-10 days</li>
                                    <li><i class="fas fa-clock me-2 text-primary"></i>Diploma Copy: 10-14 days</li>
                                </ul>
                                
                                <hr>
                                
                                <h6>Required Documents:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check me-2 text-success"></i>Valid Government ID</li>
                                    <li><i class="fas fa-check me-2 text-success"></i>School ID (if available)</li>
                                    <li><i class="fas fa-check me-2 text-success"></i>Previous certificates (if applicable)</li>
                                </ul>
                                
                                <hr>
                                
                                <h6>Status Tracking:</h6>
                                <div class="progress-steps">
                                    <div class="progress-step">
                                        <div class="progress-step-circle"><i class="fas fa-file"></i></div>
                                        <small>Pending</small>
                                    </div>
                                    <div class="progress-step">
                                        <div class="progress-step-circle"><i class="fas fa-cog"></i></div>
                                        <small>Processing</small>
                                    </div>
                                    <div class="progress-step">
                                        <div class="progress-step-circle"><i class="fas fa-check"></i></div>
                                        <small>Ready</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload handling
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('documents');
        const filePreview = document.getElementById('filePreview');
        
        // Click to browse files
        fileUploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // Drag and drop functionality
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });
        
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            fileInput.files = files;
            displayFiles(files);
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            displayFiles(e.target.files);
        });
        
        // Display selected files
        function displayFiles(files) {
            filePreview.innerHTML = '';
            
            if (files.length > 0) {
                const fileList = document.createElement('div');
                fileList.className = 'list-group';
                
                Array.from(files).forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                    
                    const fileInfo = document.createElement('div');
                    fileInfo.innerHTML = `
                        <i class="fas fa-file me-2"></i>
                        <strong>${file.name}</strong>
                        <small class="text-muted ms-2">(${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                    `;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'btn btn-sm btn-outline-danger';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.onclick = () => removeFile(index);
                    
                    fileItem.appendChild(fileInfo);
                    fileItem.appendChild(removeBtn);
                    fileList.appendChild(fileItem);
                });
                
                filePreview.appendChild(fileList);
            }
        }
        
        // Remove file from selection
        function removeFile(index) {
            const dt = new DataTransfer();
            const files = fileInput.files;
            
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }
            
            fileInput.files = dt.files;
            displayFiles(fileInput.files);
        }
        
        // Form validation
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            const documentType = document.getElementById('document_type').value;
            const purpose = document.getElementById('purpose').value;
            const releaseDate = document.getElementById('preferred_release_date').value;
            
            if (!documentType || !purpose || !releaseDate) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Check if release date is at least 3 days from now
            const selectedDate = new Date(releaseDate);
            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 3);
            
            if (selectedDate < minDate) {
                e.preventDefault();
                alert('Please select a release date at least 3 days from today.');
                return false;
            }
        });
    </script>
</body>
</html>