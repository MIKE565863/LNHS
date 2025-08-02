<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lnhs_portal');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTables($pdo) {
    // Users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        user_type ENUM('student', 'alumni', 'admin') NOT NULL,
        student_id VARCHAR(50),
        contact_number VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Document types table
    $sql = "CREATE TABLE IF NOT EXISTS document_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        requirements TEXT,
        processing_days INT DEFAULT 3,
        fee DECIMAL(10,2) DEFAULT 0.00,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Document requests table
    $sql = "CREATE TABLE IF NOT EXISTS document_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        document_type_id INT NOT NULL,
        purpose TEXT NOT NULL,
        preferred_release_date DATE,
        status ENUM('pending', 'processing', 'approved', 'denied', 'ready_for_pickup', 'completed') DEFAULT 'pending',
        admin_notes TEXT,
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Request attachments table
    $sql = "CREATE TABLE IF NOT EXISTS request_attachments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(100),
        file_size INT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (request_id) REFERENCES document_requests(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('email', 'sms', 'portal') NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Insert default document types
    $documentTypes = [
        ['Certificate of Enrollment', 'Official certificate showing current enrollment status', 'Valid ID, Proof of payment', 2, 50.00],
        ['Good Moral Certificate', 'Certificate attesting to good moral character', 'Valid ID, Clearance form', 3, 75.00],
        ['Transcript of Records', 'Complete academic record', 'Valid ID, Clearance form, Payment receipt', 5, 150.00],
        ['Form 137', 'Permanent record of student', 'Valid ID, Clearance form, Payment receipt', 5, 200.00],
        ['Certificate of Graduation', 'Certificate confirming graduation', 'Valid ID, Clearance form', 3, 100.00]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO document_types (name, description, requirements, processing_days, fee) VALUES (?, ?, ?, ?, ?)");
    foreach ($documentTypes as $type) {
        $stmt->execute($type);
    }

    // Create default admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password, first_name, last_name, user_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin@lnhs.edu.ph', $adminPassword, 'System', 'Administrator', 'admin']);
}

// Initialize database
createTables($pdo);
?>