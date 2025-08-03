-- Create database
CREATE DATABASE IF NOT EXISTS luthor_portfolio;
USE luthor_portfolio;

-- Create users table first (referenced by testimonials)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    linkedin_profile VARCHAR(255) NULL,
    profile_picture_url VARCHAR(500) NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create testimonials table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    position VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    linkedin_profile VARCHAR(255) NULL,
    message TEXT NOT NULL,
    rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create user sessions table
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, email) VALUES 
('admin', '$2y$10$tgbY4n8H.5TFmwt8qVVK2.Wk5tOXWJ5PkO4K8VHV.8hIJ8v5.yoJG', 'admin@webbr.com');

-- Create sessions table for admin login
CREATE TABLE admin_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Create password reset tokens table
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    verification_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Portfolio Management Tables
-- Create portfolio content table for basic site content
CREATE TABLE portfolio_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(100) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    content_type ENUM('text', 'textarea', 'html') DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section_field (section, field_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create portfolio skills table
CREATE TABLE portfolio_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    skill_name VARCHAR(200) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create portfolio experience table
CREATE TABLE portfolio_experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(200) NOT NULL,
    position VARCHAR(200) NOT NULL,
    date_start DATE NOT NULL,
    date_end DATE NULL,
    is_present BOOLEAN DEFAULT FALSE,
    timeframe VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create portfolio education table
CREATE TABLE portfolio_education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    institution VARCHAR(200) NOT NULL,
    qualification VARCHAR(200) NOT NULL,
    date_start DATE NOT NULL,
    date_end DATE NULL,
    is_present BOOLEAN DEFAULT FALSE,
    timeframe VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create portfolio works table for dynamic portfolio management
CREATE TABLE portfolio_works (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    project_url VARCHAR(500),
    image_path VARCHAR(500),
    gallery_image_path VARCHAR(500),
    sort_order INT DEFAULT 999,
    is_featured BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample portfolio works data
INSERT INTO portfolio_works (title, category, description, project_url, image_path, gallery_image_path, sort_order) VALUES
('Sample Web Project', 'Website', 'A modern responsive website built with the latest technologies.', 'https://example.com', 'images/portfolio/sample-web.jpg', 'images/portfolio/gallery/g-sample-web.jpg', 1),
('Mobile App Design', 'App Design', 'User-friendly mobile application with intuitive interface design.', 'https://example.com/app', 'images/portfolio/sample-app.jpg', 'images/portfolio/gallery/g-sample-app.jpg', 2),
('Brand Identity', 'Branding', 'Complete brand identity package including logo and visual guidelines.', 'https://example.com/brand', 'images/portfolio/sample-brand.jpg', 'images/portfolio/gallery/g-sample-brand.jpg', 3);

-- Create uploads directory structure (run these commands manually or via file system)
-- uploads/
-- uploads/avatars/
-- uploads/testimonials/

-- Create indexes for better performance
CREATE INDEX idx_testimonials_status ON testimonials(status);
CREATE INDEX idx_testimonials_user_id ON testimonials(user_id);
CREATE INDEX idx_testimonials_created_at ON testimonials(created_at);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_admin_sessions_token ON admin_sessions(session_token);
CREATE INDEX idx_password_reset_tokens_token ON password_reset_tokens(token);

-- Portfolio table indexes
CREATE INDEX idx_portfolio_content_section ON portfolio_content(section);
CREATE INDEX idx_portfolio_content_section_field ON portfolio_content(section, field_name);
CREATE INDEX idx_portfolio_skills_category ON portfolio_skills(category);
CREATE INDEX idx_portfolio_skills_sort_order ON portfolio_skills(sort_order);
CREATE INDEX idx_portfolio_experience_company ON portfolio_experience(company);
CREATE INDEX idx_portfolio_experience_date_start ON portfolio_experience(date_start);
CREATE INDEX idx_portfolio_experience_is_present ON portfolio_experience(is_present);
CREATE INDEX idx_portfolio_education_institution ON portfolio_education(institution);
CREATE INDEX idx_portfolio_education_date_start ON portfolio_education(date_start);
CREATE INDEX idx_portfolio_education_is_present ON portfolio_education(is_present);
CREATE INDEX idx_portfolio_works_category ON portfolio_works(category);
CREATE INDEX idx_portfolio_works_sort_order ON portfolio_works(sort_order);
CREATE INDEX idx_portfolio_works_is_featured ON portfolio_works(is_featured);

-- Set proper charset and collation
ALTER DATABASE luthor_portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE testimonials CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE user_sessions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE admin_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE admin_sessions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE password_reset_tokens CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_content CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_skills CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_experience CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_education CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_works CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
