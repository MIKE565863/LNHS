<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student or alumni
if (!isLoggedIn() || !isUser()) {
    header('Location: ../index.php');
    exit();
}

$user = getCurrentUser();
$documentTypes = getAllDocumentTypes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Document - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>LNHS Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="request-document.php">
                            <i class="fas fa-file-alt me-1"></i>Request Document
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-requests.php">
                            <i class="fas fa-list me-1"></i>My Requests
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="change-password.php"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-file-alt me-2"></i>Request Document
                        </h4>
                    </div>
                    <div class="card-body p-4">
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

                        <form action="process-request.php" method="POST" enctype="multipart/form-data" id="requestForm">
                            <!-- Document Type Selection -->
                            <div class="mb-4">
                                <label for="document_type" class="form-label fw-bold">Document Type *</label>
                                <select class="form-select" id="document_type" name="document_type_id" required>
                                    <option value="">Select a document type</option>
                                    <?php foreach ($documentTypes as $docType): ?>
                                        <option value="<?php echo $docType['id']; ?>" 
                                                data-fee="<?php echo $docType['fee']; ?>"
                                                data-days="<?php echo $docType['processing_days']; ?>"
                                                data-requirements="<?php echo htmlspecialchars($docType['requirements']); ?>">
                                            <?php echo htmlspecialchars($docType['name']); ?> - ₱<?php echo number_format($docType['fee'], 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Document Information -->
                            <div class="row mb-4" id="documentInfo" style="display: none;">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Document Information</h6>
                                            <p class="mb-1"><strong>Fee:</strong> <span id="docFee">-</span></p>
                                            <p class="mb-1"><strong>Processing Time:</strong> <span id="docDays">-</span></p>
                                            <p class="mb-0"><strong>Requirements:</strong></p>
                                            <small id="docRequirements" class="text-muted">-</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Purpose -->
                            <div class="mb-4">
                                <label for="purpose" class="form-label fw-bold">Purpose of Request *</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" 
                                          placeholder="Please specify the purpose of your document request (e.g., scholarship application, employment, etc.)" required></textarea>
                            </div>

                            <!-- Preferred Release Date -->
                            <div class="mb-4">
                                <label for="preferred_release_date" class="form-label fw-bold">Preferred Release Date</label>
                                <input type="date" class="form-control" id="preferred_release_date" name="preferred_release_date"
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                <small class="text-muted">Leave blank if no specific date is required</small>
                            </div>

                            <!-- Attachments -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Required Documents</label>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <p class="mb-2"><strong>Please upload the following:</strong></p>
                                        <ul class="mb-3">
                                            <li>Valid ID (Government ID, School ID, etc.)</li>
                                            <li>Proof of payment (if applicable)</li>
                                            <li>Other supporting documents</li>
                                        </ul>
                                        <div class="mb-3">
                                            <label for="attachments" class="form-label">Upload Files</label>
                                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple 
                                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                            <small class="text-muted">Maximum 5 files, 5MB each. Supported formats: JPG, PNG, PDF, DOC, DOCX</small>
                                        </div>
                                        <div id="filePreview" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> *
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Document Request Terms and Conditions</h6>
                    <ol>
                        <li>All document requests are subject to approval by the school administration.</li>
                        <li>Processing time may vary depending on the type of document and current workload.</li>
                        <li>Payment must be made before document processing begins.</li>
                        <li>Valid identification is required for all requests.</li>
                        <li>Documents will be held for pickup for 30 days after completion.</li>
                        <li>The school reserves the right to deny requests that do not meet requirements.</li>
                        <li>False information provided may result in request cancellation.</li>
                        <li>Requests are processed during school hours only.</li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show document information when type is selected
        document.getElementById('document_type').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const documentInfo = document.getElementById('documentInfo');
            
            if (this.value) {
                const fee = selectedOption.getAttribute('data-fee');
                const days = selectedOption.getAttribute('data-days');
                const requirements = selectedOption.getAttribute('data-requirements');
                
                document.getElementById('docFee').textContent = '₱' + parseFloat(fee).toFixed(2);
                document.getElementById('docDays').textContent = days + ' business days';
                document.getElementById('docRequirements').textContent = requirements;
                
                documentInfo.style.display = 'block';
            } else {
                documentInfo.style.display = 'none';
            }
        });

        // File preview
        document.getElementById('attachments').addEventListener('change', function() {
            const filePreview = document.getElementById('filePreview');
            filePreview.innerHTML = '';
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                const fileDiv = document.createElement('div');
                fileDiv.className = 'alert alert-info d-flex justify-content-between align-items-center';
                fileDiv.innerHTML = `
                    <div>
                        <i class="fas fa-file me-2"></i>
                        <strong>${file.name}</strong> (${fileSize} MB)
                    </div>
                    <button type="button" class="btn-close" onclick="removeFile(${i})"></button>
                `;
                filePreview.appendChild(fileDiv);
            }
        });

        function removeFile(index) {
            const input = document.getElementById('attachments');
            const dt = new DataTransfer();
            
            for (let i = 0; i < input.files.length; i++) {
                if (i !== index) {
                    dt.items.add(input.files[i]);
                }
            }
            
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }

        // Form validation
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            const purpose = document.getElementById('purpose').value.trim();
            const documentType = document.getElementById('document_type').value;
            
            if (!documentType) {
                e.preventDefault();
                alert('Please select a document type.');
                return false;
            }
            
            if (purpose.length < 10) {
                e.preventDefault();
                alert('Please provide a detailed purpose (at least 10 characters).');
                return false;
            }
        });
    </script>
</body>
</html>