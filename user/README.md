# User Management System

This folder contains all user-facing functionality for the testimonial system.

## Files:

### Authentication
- `login.php` - User login page
- `logout.php` - User logout handler
- `register.php` - User registration page

### Dashboard
- `dashboard.php` - Main user dashboard
- `edit_testimonial.php` - Edit testimonial page

### Security
- All files require proper session authentication
- Database connections use encrypted credentials
- Input validation on all forms

## Usage:

1. **New Users**: Start at `register.php` to create an account
2. **Existing Users**: Access `login.php` to sign in
3. **Dashboard**: Manage testimonials from `dashboard.php`
4. **Editing**: Edit pending/rejected testimonials via `edit_testimonial.php`

## Navigation:

- Users can access the system via the main website navigation
- All user pages have proper breadcrumb navigation
- Logout functionality is available from all authenticated pages

## Database Integration:

- Users are stored in the `users` table
- Testimonials are linked to users via `user_id` foreign key
- Session management through `user_sessions` table
