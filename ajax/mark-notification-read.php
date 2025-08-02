<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
    
    if ($notification_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        exit();
    }
    
    try {
        // Check if notification belongs to current user
        $stmt = $pdo->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            // Mark notification as read
            $result = markNotificationAsRead($notification_id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Notification not found or access denied']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>