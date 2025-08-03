# 🌟 Rose Webb Portfolio

A modern, dynamic portfolio website with comprehensive admin system and user testimonials.

## 🚀 Quick Start Installation

### Prerequisites
- **Web Server**: Apache/Nginx with PHP 7.4+ 
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **PHP Extensions**: PDO, OpenSSL, MySQLi
- **SMTP Account**: For email functionality

### Step 1: Download & Setup
1. Clone or download this repository to your web server
2. Ensure the web server points to the project root directory
3. Verify PHP and database server are running

### Step 2: Run the Installer
1. **Access Installer**: Navigate to `yoursite.com/installer.php`
2. **Complete Installation Wizard**: The installer will:
   - Generate all encryption keys automatically
   - Set up database connection with encrypted credentials
   - Create all required database tables
   - Set up your admin account with encrypted password
   - Configure email settings (optional)
   - Apply security configurations

### Step 3: Remove Installer (Security)
1. **Delete installer.php** after successful installation
2. Your installation is now complete and secure

### Alternative Manual Setup (Advanced Users)
If you prefer manual setup or need recovery:
1. **Access Admin Panel**: Navigate to `yoursite.com/admin/`
2. **Generate Security Keys**: 
   - Go to Admin → Debug Dashboard
   - Click "Generate Security Keys" 
   - This creates encrypted key storage for secure operations
3. **Setup Database**:
   - In Debug Dashboard, click "Database Setup"
   - Follow the setup wizard to create all required tables
   - Default tables: portfolio_content, portfolio_works, portfolio_skills, etc.

### Step 4: Email Configuration (Optional via Installer)
If you didn't configure email during installation:

1. **Prepare SMTP Details**: Gather your email provider's SMTP settings:
   - SMTP Host (e.g., `smtp.gmail.com`, `smtp.outlook.com`)
   - SMTP Port (usually 465 for SSL, 587 for TLS)
   - Username & Password
   - Encryption type (SSL/TLS)

2. **Encrypt Email Settings**:
   - In Debug Dashboard → Email & Communication Tools
   - Click "Setup Email Encryption" 
   - Fill in your SMTP details
   - Click "Encrypt & Save Configuration"
   - Your credentials are now encrypted with AES-256-GCM

3. **Test Email System**:
   - Click "Test Email" in Debug Dashboard
   - Send a test email to verify configuration

### Step 5: Content Setup
1. **Add Portfolio Content**:
   - Go to Admin Dashboard → Content Management
   - Click "Portfolio Content Editor"
   - Add your skills, experience, education, and works
2. **Upload Images**: Use the image upload tools in the admin panel
3. **Customize**: Update contact information and social links

### Step 6: User System (Optional)
1. **Admin Account**: Already created during installation
2. **Enable Testimonials**: Users can register and submit testimonials via the main site

### Common SMTP Providers

#### Gmail Setup
- **Host**: `smtp.gmail.com`
- **Port**: `465` (SSL) or `587` (TLS)
- **Username**: Your Gmail address
- **Password**: App-specific password (not your regular password)
- **Encryption**: SSL

#### Outlook/Hotmail Setup
- **Host**: `smtp-mail.outlook.com`
- **Port**: `587`
- **Username**: Your Outlook email
- **Password**: Your account password
- **Encryption**: TLS

#### cPanel/Shared Hosting
- **Host**: `mail.yourdomain.com` or your hosting provider's SMTP
- **Port**: `465` (SSL) or `587` (TLS)
- **Username**: Your email address
- **Password**: Email account password
- **Encryption**: SSL/TLS

### Troubleshooting

#### Database Issues
- **Connection Failed**: Check database credentials in admin setup
- **Tables Missing**: Run "Database Setup" again or use "Comprehensive DB Test"
- **Permission Errors**: Ensure database user has CREATE, INSERT, UPDATE, DELETE privileges

#### Email Issues
- **Test Email Fails**: Verify SMTP settings using "Test Configuration" 
- **Authentication Error**: Check username/password, may need app-specific password
- **Connection Timeout**: Try different port (465 vs 587) or encryption type

#### File Permission Issues
- **Can't Write Files**: Ensure web server can write to `secure/`, `logs/`, `uploads/` directories
- **Encryption Fails**: Check that `secure/keys.json` exists and is readable

### Security Notes
- 🔐 All sensitive data is encrypted using AES-256-GCM
- 🛡️ Database credentials stored in encrypted D.env file
- 📧 Email credentials stored in encrypted E.env file  
- 🔑 Encryption keys stored securely in protected directory
- 🚫 Sensitive files protected from web access via .htaccess

## 🚀 Quick Start

1. **Main Website**: Access via `index.php`
2. **Admin Panel**: Navigate to `admin/`
3. **Documentation**: Find all guides in `docs/`

## 📁 Clean Directory Structure

This project maintains a clean root directory with only essential files:

- `index.php` - Main website entry point
- Web configuration files (`.htaccess`, `site.webmanifest`)
- Favicon and icon files

All other files are organized in logical directories. See `docs/DIRECTORY_STRUCTURE.md` for complete details.

## 🔗 Key Features

- **Dynamic Content**: Database-driven portfolio content
- **Admin Dashboard**: Comprehensive management tools
- **User System**: Registration and testimonial submission
- **Security**: Encrypted configuration and secure file handling
- **Modern Design**: Responsive and professional layout

## 📚 Documentation

Complete documentation available in the `docs/` directory:

- Directory structure guide
- Security configuration
- Admin system documentation
- User system setup
- Integration guides

---

**Live Website**: [Your Domain]  
**Admin Access**: [Your Domain]/admin/  
**Last Updated**: August 3, 2025
