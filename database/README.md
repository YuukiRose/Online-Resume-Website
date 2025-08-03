# Database Documentation

This folder contains the database schema and migration files for the Luthor Portfolio system.

## Files Overview

### `setup.sql`
Complete database setup script for fresh installations. Contains:
- User management tables (users, user_sessions)
- Admin system tables (admin_users, admin_sessions, password_reset_tokens)
- Testimonials system
- **NEW: Portfolio management tables (content, skills, experience, education, works)**
- All indexes and proper charset configurations

### `portfolio_migration.sql`
Migration script for existing installations to add the new portfolio management system. Run this if you have an existing Luthor installation and want to add the dynamic portfolio features.

## Recent Updates (August 2, 2025)

### Added Portfolio Management System Tables:

1. **`portfolio_content`** - Basic site content management
   - Stores text, textarea, and HTML content for different sections
   - Unique constraints on section/field combinations

2. **`portfolio_skills`** - Skills and expertise management
   - Categorized skills with sort ordering
   - Used for the skills section of the portfolio

3. **`portfolio_experience`** - Work experience timeline
   - Company, position, date ranges
   - Support for current positions (is_present flag)
   - Automatic timeline generation

4. **`portfolio_education`** - Education history
   - Institution, qualification, date ranges
   - Support for ongoing studies (is_present flag)
   - Timeline integration

5. **`portfolio_works`** - Dynamic portfolio projects
   - **NEW FEATURE**: Image upload support
   - Project URLs, categories, descriptions
   - Gallery image management
   - Sort ordering and featured flags

### Key Features Implemented:

- **Dynamic Content Management**: Real-time editing through admin interface
- **Timeline Sorting**: Automatic chronological ordering for experience/education
- **Image Upload System**: Direct file uploads with automatic path management
- **Database-Driven Portfolio**: All content loaded dynamically from database
- **CRUD Operations**: Full Create, Read, Update, Delete functionality

### File Upload Integration:
- Images stored in `images/portfolio/` directory
- Gallery versions in `images/portfolio/gallery/`
- Automatic filename generation to prevent conflicts
- Support for JPG, PNG, GIF, WebP formats
- 5MB file size limit

## Usage Instructions

### For Fresh Installations:
1. Import `setup.sql` into your MySQL database
2. Update database connection details in `config/database.php`
3. Access the admin panel to start managing content

### For Existing Installations:
1. Backup your current database
2. Run `portfolio_migration.sql` to add new tables
3. Access the comprehensive portfolio editor in the admin panel

### Admin Panel Access:
- **Content Management**: `/admin/comprehensive_portfolio_editor.php`
- **Testimonials**: `/admin/dashboard.php`
- **User Management**: `/admin/manage_users.php`

### Default Admin Credentials:
- Username: `admin`
- Password: `admin123`
- **Change these immediately after installation!**

## Security Notes

- All file uploads are validated for type and size
- SQL injection protection through prepared statements
- Session-based authentication for admin access
- Proper charset handling (utf8mb4) for international content

## Performance Optimizations

- Indexes on frequently queried columns
- Optimized table structures with appropriate data types
- Proper foreign key relationships
- Engine-specific optimizations (InnoDB)

## Backup Recommendations

Before making any changes:
1. Backup your database: `mysqldump -u username -p luthor_portfolio > backup.sql`
2. Backup uploaded files in `images/portfolio/` directory
3. Test changes on a staging environment first

## Support

For issues or questions about the database setup:
1. Check the MySQL error logs
2. Verify database connection settings
3. Ensure proper permissions for file uploads
4. Review the comprehensive portfolio editor for detailed error messages
