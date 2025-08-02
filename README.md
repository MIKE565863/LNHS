# LNHS Documents Request Portal

A web-based document request system for students and alumni of LNHS (Laguna National High School) to request certificates and documents online without visiting the school.

## System Overview

**Title:** LNHS Documents Request Portal

**Purpose:** This system allows students and alumni to request documents such as certificates online, eliminating the need to physically visit the school. The system includes request tracking, admin management, and notification features.

## Features

### 🎓 For Students & Alumni
- **User Registration & Login** - Secure account creation and authentication
- **Document Request Form** - Online form to request various documents:
  - Certificate of Enrollment
  - Good Moral Certificate
  - Transcript of Records
  - Diploma Copy
  - Other documents
- **File Upload** - Upload valid ID and supporting documents
- **Request Tracking** - Real-time status tracking with progress indicators:
  - ✅ Pending → Processing → Approved/Denied → Ready for Pickup → Completed
- **Dashboard** - View request statistics and recent submissions
- **Notifications** - Get updates on request status

### 👨‍💼 For Administrators
- **Admin Dashboard** - Comprehensive overview of all requests and statistics
- **Request Management** - View, process, and update request statuses
- **User Management** - Manage student and alumni accounts
- **Reports & Analytics** - Generate and export request reports
- **Status Updates** - Update request statuses with admin notes
- **Print Logs** - Export request data to Excel or PDF

## Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Icons:** Font Awesome 6
- **File Uploads:** Multi-file support with validation

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependencies)

### Step 1: Clone/Download the Project
```bash
git clone <repository-url>
# or download and extract the ZIP file
```

### Step 2: Database Setup
1. Create a new MySQL database:
```sql
CREATE DATABASE lnhs_documents_portal;
```

2. Import the database schema:
```bash
mysql -u root -p lnhs_documents_portal < database/schema.sql
```

3. Configure database connection in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lnhs_documents_portal');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 3: File Permissions
Create upload directories and set proper permissions:
```bash
mkdir -p uploads/requests
chmod 755 uploads/requests
```

### Step 4: Web Server Configuration

#### Apache (.htaccess)
Create `.htaccess` in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### Step 5: Default Admin Account
The system comes with a default admin account:
- **Username:** admin
- **Password:** password

⚠️ **Important:** Change the default password after first login!

## File Structure

```
lnhs-documents-portal/
├── admin/                  # Admin panel files
│   ├── dashboard.php
│   ├── login.php
│   └── logout.php
├── assets/                 # Static assets
│   └── css/
│       └── style.css
├── config/                 # Configuration files
│   └── database.php
├── database/              # Database files
│   └── schema.sql
├── includes/              # Include files
│   └── auth.php
├── uploads/               # File uploads
│   └── requests/
├── user/                  # User panel files
│   ├── dashboard.php
│   ├── request-document.php
│   ├── track-request.php
│   └── logout.php
├── index.php              # Landing page
├── login.php              # User login
├── register.php           # User registration
└── README.md
```

## User Guide

### For Students/Alumni

1. **Registration:**
   - Visit the portal homepage
   - Click "Register Now"
   - Fill in your details (Student ID, name, email, etc.)
   - Choose "Current Student" or "Alumni"
   - Create a secure password

2. **Requesting Documents:**
   - Login with your credentials
   - Go to "New Request"
   - Select document type and purpose
   - Set preferred release date (minimum 3 days)
   - Upload required documents (ID, etc.)
   - Submit the request

3. **Tracking Requests:**
   - Use "Track Request" with your request number
   - Or view all requests in "My Requests"
   - Monitor status progress in real-time

### For Administrators

1. **Login:**
   - Access `/admin/login.php`
   - Use admin credentials

2. **Managing Requests:**
   - View all requests in the dashboard
   - Process pending requests
   - Update statuses and add notes
   - Generate reports

## Security Features

- **Password Hashing:** All passwords are hashed using PHP's `password_hash()`
- **Session Management:** Secure session handling
- **File Upload Validation:** File type and size restrictions
- **SQL Injection Prevention:** Prepared statements
- **XSS Protection:** Input sanitization and output escaping
- **Access Control:** Role-based authentication

## Support

For technical support or questions about the system:

1. Check the documentation
2. Review error logs in your web server
3. Ensure all file permissions are correct
4. Verify database connection settings

## License

This project is developed for educational purposes for LNHS.

## Changelog

### Version 1.0.0
- Initial release
- User registration and authentication
- Document request system
- Admin dashboard
- Request tracking
- File upload functionality
- Responsive design

---

**Developed for LNHS Documents Request Portal**
*Making document requests easier for students and alumni*