# Testimonial System Setup Instructions

## Prerequisites
- XAMPP or similar PHP/MySQL environment
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Setup Steps

### 1. Database Setup
1. Start XAMPP (Apache and MySQL)
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the database structure by running the SQL commands in `database/setup.sql`
   - OR manually run each SQL command from the file

### 2. File Structure
Ensure your files are organized as follows:
```
Luthor/
├── config/
│   └── database.php
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── create_user.php
│   ├── manage_users.php
│   └── logout.php
├── user/
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── edit_testimonial.php
│   └── logout.php
├── database/
│   └── setup.sql
├── get_testimonials.php
└── index.html (updated with user navigation)
```

### 3. Directory Permissions
Make sure the `uploads` directory is writable:
- On Windows: Right-click uploads folder → Properties → Security → Give full control
- The uploads/avatars directory will be created automatically when first testimonial with avatar is submitted

### 4. Database Configuration
Edit `config/database.php` if needed to match your MySQL settings:
```php
$host = 'localhost';
$dbname = 'luthor_portfolio';
$username = 'root';  // Change if different
$password = '';      // Change if you have a password
```

### 5. Default Admin Account
- Username: `admin`
- Password: `admin123`
- **IMPORTANT**: Change this password after first login!

## Usage

### For Visitors (Testimonial Submission)
1. Visit: `http://localhost/Luthor/testimonial_form.php`
2. Fill out the form with testimonial details
3. Optionally upload a profile picture
4. Submit - testimonial goes to pending status

### For Admin (Testimonial Management)
1. Visit: `http://localhost/Luthor/admin/login.php`
2. Login with admin credentials
3. View, approve, reject, or delete testimonials
4. Approved testimonials automatically appear on the main site

### Main Site Integration
- Approved testimonials automatically load on `index.html`
- "Submit a Testimonial" button appears above the testimonials section
- If no approved testimonials exist, shows a placeholder encouraging submissions

## Security Features
- Password hashing for admin accounts
- Session token validation
- File upload validation (image types, size limits)
- SQL injection protection with prepared statements
- XSS protection with input escaping

## Customization

### Adding More Admin Users
Run this SQL command in phpMyAdmin (replace values as needed):
```sql
INSERT INTO admin_users (username, password, email) VALUES 
('newadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');
```
Note: This example password hash is for 'password123' - generate your own hash!

### Changing Default Avatar Images
- Replace images in `images/avatars/` directory
- Update the avatar fallback logic in the JavaScript if needed

### Email Notifications (Optional Enhancement)
You could add email notifications when:
- New testimonials are submitted (notify admin)
- Testimonials are approved/rejected (notify submitter)

## Troubleshooting

### Common Issues:
1. **"Connection failed" error**: Check database credentials in `config/database.php`
2. **404 errors**: Ensure all files are in the correct directories
3. **File upload errors**: Check `uploads` directory permissions
4. **Testimonials not loading**: Check browser console for JavaScript errors

### Testing the System:

#### Admin Access
1. Visit: `http://localhost/Luthor/admin/login.php`
2. Default admin credentials:
   - Username: `admin`
   - Password: `admin123`
3. Access the admin dashboard to manage testimonials and users

#### User Access  
1. Visit: `http://localhost/Luthor/user/register.php` to create a user account
2. Or visit: `http://localhost/Luthor/user/login.php` to sign in
3. Access the user dashboard to submit and manage testimonials

#### Public Access
1. Visit: `http://localhost/Luthor/index.html` to see the main portfolio
2. Use "Share Testimonial" in navigation to register and submit testimonials
3. Use "Login" in navigation for existing users

## File Descriptions

### Core System
- `get_testimonials.php`: API endpoint that returns approved testimonials as JSON
- `config/database.php`: Database connection configuration (encrypted)
- `database/setup.sql`: Database schema and initial data

### Admin System
- `admin/login.php`: Admin login page with 2FA password reset
- `admin/dashboard.php`: Main admin panel for managing testimonials
- `admin/manage_users.php`: User management interface
- `admin/create_user.php`: Admin interface for creating new users

### User System
- `user/register.php`: User registration page
- `user/login.php`: User login page  
- `user/dashboard.php`: User dashboard for managing personal testimonials
- `user/edit_testimonial.php`: Edit testimonial interface
- `user/logout.php`: User logout handler

### Legacy Files (No Longer Used)
- `testimonial_form.php`: Old public form (replaced by user system)
- `submit_testimonial.php`: Old submission handler (replaced by user system)

The system is now ready to use! Visitors can submit testimonials, and you can manage them through the admin panel.
