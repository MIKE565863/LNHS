<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student or alumni
if (!isLoggedIn() || !isUser()) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = getCurrentUser();
    
    // Get form data
    $document_type_id = sanitize_input($_POST['document_type_id']);
    $purpose = sanitize_input($_POST['purpose']);
    $preferred_release_date = !empty($_POST['preferred_release_date']) ? $_POST['preferred_release_date'] : null;
    $terms = isset($_POST['terms']);

    // Validate required fields
    if (empty($document_type_id) || empty($purpose) || !$terms) {
        $_SESSION['error'] = 'Please fill in all required fields and agree to the terms.';
        header('Location: request-document.php');
        exit();
    }

    // Validate purpose length
    if (strlen($purpose) < 10) {
        $_SESSION['error'] = 'Please provide a detailed purpose (at least 10 characters).';
        header('Location: request-document.php');
        exit();
    }

    // Validate document type exists
    $documentType = getDocumentTypeById($document_type_id);
    if (!$documentType) {
        $_SESSION['error'] = 'Invalid document type selected.';
        header('Location: request-document.php');
        exit();
    }

    // Validate preferred release date
    if ($preferred_release_date) {
        $minDate = date('Y-m-d', strtotime('+1 day'));
        if ($preferred_release_date < $minDate) {
            $_SESSION['error'] = 'Preferred release date must be at least tomorrow.';
            header('Location: request-document.php');
            exit();
        }
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Insert document request
        $stmt = $pdo->prepare("
            INSERT INTO document_requests (user_id, document_type_id, purpose, preferred_release_date, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $user['id'],
            $document_type_id,
            $purpose,
            $preferred_release_date
        ]);

        $request_id = $pdo->lastInsertId();

        // Handle file uploads
        $uploadedFiles = [];
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            $fileCount = count($_FILES['attachments']['name']);
            
            // Limit to 5 files
            if ($fileCount > 5) {
                throw new Exception('Maximum 5 files allowed.');
            }

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['attachments']['name'][$i],
                        'type' => $_FILES['attachments']['type'][$i],
                        'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                        'error' => $_FILES['attachments']['error'][$i],
                        'size' => $_FILES['attachments']['size'][$i]
                    ];

                    $uploadedFileName = uploadFile($file, '../uploads/');
                    if ($uploadedFileName) {
                        // Save file information to database
                        $stmt = $pdo->prepare("
                            INSERT INTO request_attachments (request_id, file_name, file_path, file_type, file_size)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $request_id,
                            $file['name'],
                            $uploadedFileName,
                            $file['type'],
                            $file['size']
                        ]);
                        
                        $uploadedFiles[] = $file['name'];
                    } else {
                        throw new Exception('Failed to upload file: ' . $file['name']);
                    }
                }
            }
        }

        // Create notification for user
        createNotification(
            $user['id'],
            'Document Request Submitted',
            "Your request for {$documentType['name']} has been submitted successfully. Request ID: #$request_id",
            'portal'
        );

        // Send email notification to user
        sendEmailNotification(
            $user['email'],
            'Document Request Submitted - LNHS Portal',
            "Dear {$user['first_name']} {$user['last_name']},\n\n" .
            "Your document request has been submitted successfully.\n\n" .
            "Request Details:\n" .
            "- Document: {$documentType['name']}\n" .
            "- Request ID: #$request_id\n" .
            "- Purpose: $purpose\n" .
            "- Fee: ₱" . number_format($documentType['fee'], 2) . "\n" .
            "- Processing Time: {$documentType['processing_days']} business days\n\n" .
            "You will be notified when your request status changes.\n\n" .
            "Best regards,\nLNHS Administration"
        );

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Document request submitted successfully! Your request ID is #$request_id";
        header('Location: view-request.php?id=' . $request_id);
        exit();

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        
        // Delete uploaded files if any
        foreach ($uploadedFiles as $fileName) {
            $filePath = '../uploads/' . $fileName;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $_SESSION['error'] = 'An error occurred while submitting your request: ' . $e->getMessage();
        header('Location: request-document.php');
        exit();
    }
} else {
    // If not POST request, redirect to request form
    header('Location: request-document.php');
    exit();
}
?>