<?php
// Utility functions for LNHS Documents Request Portal

// Sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    return date($format, strtotime($datetime));
}

// Get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning';
        case 'processing':
            return 'bg-info';
        case 'approved':
            return 'bg-success';
        case 'denied':
            return 'bg-danger';
        case 'ready_for_pickup':
            return 'bg-primary';
        case 'completed':
            return 'bg-secondary';
        default:
            return 'bg-secondary';
    }
}

// Get status text
function getStatusText($status) {
    switch ($status) {
        case 'pending':
            return 'Pending';
        case 'processing':
            return 'Processing';
        case 'approved':
            return 'Approved';
        case 'denied':
            return 'Denied';
        case 'ready_for_pickup':
            return 'Ready for Pickup';
        case 'completed':
            return 'Completed';
        default:
            return 'Unknown';
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Check if user is student or alumni
function isUser() {
    return isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['student', 'alumni']);
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION[$type] = $message;
    }
    header('Location: ' . $url);
    exit();
}

// Upload file
function uploadFile($file, $uploadDir = 'uploads/') {
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . generateRandomString(8) . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    // Check file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }

    // Check file size (5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}

// Send email notification
function sendEmailNotification($to, $subject, $message) {
    // For demo purposes, we'll just log the email
    // In production, you would use a proper email service like PHPMailer
    $logFile = 'logs/email_log.txt';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . " | To: $to | Subject: $subject | Message: $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    return true;
}

// Send SMS notification (mock function)
function sendSMSNotification($phone, $message) {
    // For demo purposes, we'll just log the SMS
    $logFile = 'logs/sms_log.txt';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . " | To: $phone | Message: $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    return true;
}

// Create notification
function createNotification($userId, $title, $message, $type = 'portal') {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$userId, $title, $message, $type]);
}

// Get user notifications
function getUserNotifications($userId, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_at DESC LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

// Get unread notification count
function getUnreadNotificationCount($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['count'];
}

// Mark notification as read
function markNotificationAsRead($notificationId) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    return $stmt->execute([$notificationId]);
}

// Get user by ID
function getUserById($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

// Get document type by ID
function getDocumentTypeById($typeId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM document_types WHERE id = ?");
    $stmt->execute([$typeId]);
    return $stmt->fetch();
}

// Get all document types
function getAllDocumentTypes() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get request by ID
function getRequestById($requestId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT dr.*, u.first_name, u.last_name, u.email, dt.name as document_name, dt.fee
        FROM document_requests dr
        JOIN users u ON dr.user_id = u.id
        JOIN document_types dt ON dr.document_type_id = dt.id
        WHERE dr.id = ?
    ");
    $stmt->execute([$requestId]);
    return $stmt->fetch();
}

// Get user requests
function getUserRequests($userId, $limit = null) {
    global $pdo;
    
    $sql = "
        SELECT dr.*, dt.name as document_name, dt.fee
        FROM document_requests dr
        JOIN document_types dt ON dr.document_type_id = dt.id
        WHERE dr.user_id = ?
        ORDER BY dr.request_date DESC
    ";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Get all requests (for admin)
function getAllRequests($status = null, $limit = null) {
    global $pdo;
    
    $sql = "
        SELECT dr.*, u.first_name, u.last_name, u.email, dt.name as document_name, dt.fee
        FROM document_requests dr
        JOIN users u ON dr.user_id = u.id
        JOIN document_types dt ON dr.document_type_id = dt.id
    ";
    
    $params = [];
    if ($status) {
        $sql .= " WHERE dr.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY dr.request_date DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get request attachments
function getRequestAttachments($requestId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM request_attachments WHERE request_id = ?");
    $stmt->execute([$requestId]);
    return $stmt->fetchAll();
}

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number (Philippine format)
function validatePhone($phone) {
    return preg_match('/^(\+63|0)9\d{9}$/', $phone);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return getUserById($_SESSION['user_id']);
}

// Check if request belongs to user
function isRequestOwner($requestId, $userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM document_requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$requestId, $userId]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}
?>