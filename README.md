# LNHS Documents Request Portal

A comprehensive web-based document request system for students and alumni of LNHS (Local National High School) to request official documents online without visiting the school.

## 🎯 System Overview

The LNHS Documents Request Portal is a PHP-based web application that streamlines the process of requesting official documents from the school. It eliminates the need for students and alumni to physically visit the school for document requests.

### Key Features

- **User Authentication System**
  - Student and Alumni registration/login
  - Admin panel for system management
  - Secure password hashing and session management

- **Document Request Management**
  - Online document request submission
  - Multiple document types support (Certificate of Enrollment, Good Moral Certificate, etc.)
  - File upload for supporting documents
  - Request tracking with status updates

- **Request Tracking System**
  - Real-time status updates: Pending → Processing → Approved/Denied → Ready for Pickup
  - Visual progress indicators
  - Detailed request history

- **Admin Dashboard**
  - Comprehensive request management
  - User management
  - Document type configuration
  - Status updates and notifications
  - Reports and analytics

- **Notification System**
  - Email notifications (mock implementation)
  - SMS notifications (mock implementation)
  - Portal notifications
  - Status change alerts

## 🚀 Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependency management)

### Step 1: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE lnhs_portal;
```

2. Update database configuration in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lnhs_portal');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 2: File Uploads

1. Create upload directories:
```bash
mkdir uploads
mkdir logs
chmod 755 uploads logs
```

2. Ensure web server has write permissions to these directories.

### Step 3: Web Server Configuration

#### Apache Configuration
Add to your `.htaccess` file or Apache configuration:
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

#### Nginx Configuration
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

### Step 4: Initial Setup

1. Access the application in your browser
2. The system will automatically create the database tables
3. Default admin credentials:
   - Email: `admin@lnhs.edu.ph`
   - Password: `admin123`

**⚠️ Important:** Change the default admin password immediately after first login!

## 📁 Project Structure

```
lnhs-portal/
├── admin/                 # Admin panel files
│   ├── dashboard.php     # Admin dashboard
│   ├── requests.php      # Request management
│   ├── users.php         # User management
│   └── reports.php       # Reports and analytics
├── user/                  # User panel files
│   ├── dashboard.php     # User dashboard
│   ├── request-document.php # Document request form
│   ├── my-requests.php   # User's request history
│   └── profile.php       # User profile management
├── auth/                  # Authentication files
│   ├── login.php         # Login handler
│   ├── register.php      # Registration handler
│   └── logout.php        # Logout handler
├── config/               # Configuration files
│   └── database.php      # Database configuration
├── includes/             # Shared functions
│   └── functions.php     # Utility functions
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images
├── uploads/              # File uploads directory
├── logs/                 # System logs
├── index.php            # Main entry point
├── register.php         # Registration page
└── README.md           # This file
```

## 🔧 Configuration

### Database Tables

The system automatically creates the following tables:

- **users** - User accounts (students, alumni, admins)
- **document_types** - Available document types and fees
- **document_requests** - Document request records
- **request_attachments** - Uploaded files for requests
- **notifications** - System notifications

### Document Types

Default document types are automatically created:
- Certificate of Enrollment (₱50.00, 2 days)
- Good Moral Certificate (₱75.00, 3 days)
- Transcript of Records (₱150.00, 5 days)
- Form 137 (₱200.00, 5 days)
- Certificate of Graduation (₱100.00, 3 days)

## 👥 User Roles

### Students & Alumni
- Register and create accounts
- Submit document requests
- Upload supporting documents
- Track request status
- View request history
- Receive notifications

### Administrators
- Manage all document requests
- Update request statuses
- Manage user accounts
- Configure document types
- Generate reports
- Send notifications

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection (recommended to implement)
- File upload validation
- Session management
- Input validation and sanitization

## 📧 Notification System

The system includes a notification framework that supports:

- **Email Notifications** (currently mocked - logs to `logs/email_log.txt`)
- **SMS Notifications** (currently mocked - logs to `logs/sms_log.txt`)
- **Portal Notifications** (real-time in the application)

To implement real email notifications, replace the `sendEmailNotification()` function in `includes/functions.php` with a proper email service like PHPMailer or SendGrid.

## 📊 Reports & Analytics

The admin panel includes reporting features:
- Request statistics
- User activity reports
- Document type usage analytics
- Export functionality (Excel/PDF)

## 🛠️ Customization

### Adding New Document Types

1. Access the admin panel
2. Go to Document Types management
3. Add new document type with:
   - Name and description
   - Processing time
   - Fee amount
   - Required documents

### Modifying Fees

Update document fees in the admin panel or directly in the database `document_types` table.

### Custom Styling

Modify `assets/css/style.css` to customize the appearance:
- Color scheme
- Layout adjustments
- Responsive design
- Custom animations

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Check database permissions

2. **File Upload Issues**
   - Verify upload directory permissions
   - Check PHP upload limits in `php.ini`
   - Ensure proper file type validation

3. **Session Issues**
   - Check PHP session configuration
   - Verify session storage permissions
   - Clear browser cookies

4. **Email Notifications Not Working**
   - Check email log files in `logs/email_log.txt`
   - Implement proper email service
   - Verify SMTP configuration

### Log Files

- Email logs: `logs/email_log.txt`
- SMS logs: `logs/sms_log.txt`
- PHP error logs: Check your web server error logs

## 🔄 Updates & Maintenance

### Regular Maintenance Tasks

1. **Database Backup**
   ```bash
   mysqldump -u username -p lnhs_portal > backup_$(date +%Y%m%d).sql
   ```

2. **Log Rotation**
   - Monitor log file sizes
   - Implement log rotation for production

3. **Security Updates**
   - Keep PHP and MySQL updated
   - Regularly review and update dependencies
   - Monitor for security vulnerabilities

### Performance Optimization

1. **Database Optimization**
   - Add indexes for frequently queried columns
   - Optimize slow queries
   - Regular database maintenance

2. **Caching**
   - Implement Redis/Memcached for session storage
   - Add query caching for frequently accessed data

3. **File Optimization**
   - Compress CSS/JS files
   - Optimize images
   - Enable gzip compression

## 📝 License

This project is developed for educational purposes. Please ensure compliance with your institution's policies and local regulations.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For technical support or questions:
- Check the troubleshooting section
- Review log files for errors
- Contact your system administrator

## 🔮 Future Enhancements

Potential improvements for future versions:
- Mobile app development
- Integration with payment gateways
- Advanced reporting and analytics
- Multi-language support
- API development for third-party integrations
- Advanced notification system
- Document digital signatures
- Automated document generation

---

**Note:** This system is designed for educational institutions and should be customized according to specific requirements and local regulations.