// LNHS Documents Request Portal - JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation
    initializeFormValidation();

    // File upload functionality
    initializeFileUpload();

    // Status progress bar
    initializeStatusProgress();

    // Search functionality
    initializeSearch();

    // Print functionality
    initializePrint();
});

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Real-time validation
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = '';

    // Remove existing validation classes
    field.classList.remove('is-valid', 'is-invalid');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }

    // Validation rules
    switch (fieldName) {
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value && !emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }
            break;
        
        case 'contact_number':
            const phoneRegex = /^(\+63|0)9\d{9}$/;
            if (value && !phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid Philippine phone number (e.g., 09123456789).';
            }
            break;
        
        case 'password':
            if (value && value.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters long.';
            }
            break;
        
        case 'confirm_password':
            const password = document.querySelector('input[name="password"]');
            if (value && password && value !== password.value) {
                isValid = false;
                errorMessage = 'Passwords do not match.';
            }
            break;
    }

    // Apply validation result
    if (value) { // Only validate if field has value
        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = errorMessage;
            field.parentNode.appendChild(errorDiv);
        }
    }
}

// File Upload Functionality
function initializeFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFileSelection(this);
        });

        // Drag and drop functionality
        const dropZone = input.closest('.file-upload-area') || input.parentNode;
        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                input.files = files;
                handleFileSelection(input);
            });
        }
    });
}

function handleFileSelection(input) {
    const files = Array.from(input.files);
    const maxFiles = 5;
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    // Validate number of files
    if (files.length > maxFiles) {
        alert(`Maximum ${maxFiles} files allowed.`);
        input.value = '';
        return;
    }

    // Validate each file
    const validFiles = files.filter(file => {
        if (file.size > maxSize) {
            alert(`File "${file.name}" is too large. Maximum size is 5MB.`);
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert(`File "${file.name}" is not a supported type.`);
            return false;
        }
        
        return true;
    });

    // Update file input with valid files
    if (validFiles.length !== files.length) {
        const dt = new DataTransfer();
        validFiles.forEach(file => dt.items.add(file));
        input.files = dt.files;
    }

    // Update file preview
    updateFilePreview(input, validFiles);
}

function updateFilePreview(input, files) {
    const previewContainer = input.parentNode.querySelector('.file-preview') || 
                           input.parentNode.querySelector('#filePreview');
    
    if (!previewContainer) return;

    previewContainer.innerHTML = '';
    
    files.forEach((file, index) => {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'alert alert-info d-flex justify-content-between align-items-center mb-2';
        
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileIcon = getFileIcon(file.type);
        
        fileDiv.innerHTML = `
            <div>
                <i class="${fileIcon} me-2"></i>
                <strong>${file.name}</strong> (${fileSize} MB)
            </div>
            <button type="button" class="btn-close" onclick="removeFile(${index}, this)"></button>
        `;
        
        previewContainer.appendChild(fileDiv);
    });
}

function getFileIcon(fileType) {
    switch (fileType) {
        case 'image/jpeg':
        case 'image/png':
            return 'fas fa-image';
        case 'application/pdf':
            return 'fas fa-file-pdf';
        case 'application/msword':
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            return 'fas fa-file-word';
        default:
            return 'fas fa-file';
    }
}

function removeFile(index, button) {
    const fileDiv = button.closest('.alert');
    const input = fileDiv.closest('.form-group').querySelector('input[type="file"]');
    
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    fileDiv.remove();
}

// Status Progress Bar
function initializeStatusProgress() {
    const progressBars = document.querySelectorAll('.status-progress');
    
    progressBars.forEach(progress => {
        const steps = progress.querySelectorAll('.status-step');
        const currentStatus = progress.dataset.status;
        
        const statusOrder = ['pending', 'processing', 'approved', 'ready_for_pickup', 'completed'];
        const currentIndex = statusOrder.indexOf(currentStatus);
        
        steps.forEach((step, index) => {
            if (index <= currentIndex) {
                step.classList.add('completed');
                if (index === currentIndex) {
                    step.classList.add('active');
                }
            }
        });
    });
}

// Search Functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });
}

// Print Functionality
function initializePrint() {
    const printButtons = document.querySelectorAll('.btn-print');
    
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    });
}

// AJAX Functions
function markNotificationAsRead(notificationId) {
    fetch('../ajax/mark-notification-read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update notification count
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent);
                if (currentCount > 1) {
                    badge.textContent = currentCount - 1;
                } else {
                    badge.style.display = 'none';
                }
            }
            
            // Mark notification as read visually
            const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notification) {
                notification.classList.remove('fw-bold');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateRequestStatus(requestId, status) {
    if (!confirm('Are you sure you want to update this request status?')) {
        return;
    }

    fetch('../ajax/update-request-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `request_id=${requestId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating request status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the request status.');
    });
}

// Utility Functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showLoading(element) {
    element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    element.disabled = true;
}

function hideLoading(element, originalText) {
    element.innerHTML = originalText;
    element.disabled = false;
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

// Export functions for global use
window.LNHSPortal = {
    markNotificationAsRead,
    updateRequestStatus,
    formatDate,
    formatDateTime,
    formatFileSize,
    showLoading,
    hideLoading,
    showAlert
};