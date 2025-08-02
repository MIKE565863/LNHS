<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $status = isset($_POST['status']) ? sanitize_input($_POST['status']) : '';
    $admin_notes = isset($_POST['admin_notes']) ? sanitize_input($_POST['admin_notes']) : '';
    
    // Validate request ID
    if ($request_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
        exit();
    }
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'approved', 'denied', 'ready_for_pickup', 'completed'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    try {
        // Get request details
        $request = getRequestById($request_id);
        if (!$request) {
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            exit();
        }
        
        // Get user details
        $user = getUserById($request['user_id']);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }
        
        // Update request status
        $stmt = $pdo->prepare("
            UPDATE document_requests 
            SET status = ?, admin_notes = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$status, $admin_notes, $request_id]);
        
        if ($result) {
            // Create notification for user
            $status_text = getStatusText($status);
            $notification_title = "Request Status Updated";
            $notification_message = "Your request for {$request['document_name']} (ID: #{$request_id}) has been updated to: {$status_text}";
            
            createNotification(
                $request['user_id'],
                $notification_title,
                $notification_message,
                'portal'
            );
            
            // Send email notification
            $email_subject = "Document Request Status Update - LNHS Portal";
            $email_message = "Dear {$user['first_name']} {$user['last_name']},\n\n" .
                           "Your document request has been updated.\n\n" .
                           "Request Details:\n" .
                           "- Request ID: #{$request_id}\n" .
                           "- Document: {$request['document_name']}\n" .
                           "- New Status: {$status_text}\n" .
                           "- Updated: " . date('Y-m-d H:i:s') . "\n\n";
            
            if (!empty($admin_notes)) {
                $email_message .= "Admin Notes: {$admin_notes}\n\n";
            }
            
            $email_message .= "You can track your request status in your dashboard.\n\n" .
                            "Best regards,\nLNHS Administration";
            
            sendEmailNotification($user['email'], $email_subject, $email_message);
            
            // Send SMS notification if phone number is available
            if (!empty($user['contact_number'])) {
                $sms_message = "LNHS Portal: Your request #{$request_id} status updated to {$status_text}. Check your email for details.";
                sendSMSNotification($user['contact_number'], $sms_message);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Request status updated successfully',
                'status' => $status,
                'status_text' => $status_text
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update request status']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>