-- Portfolio System Migration Script
-- Run this script to add portfolio management tables to existing Luthor installations
-- Created: August 2, 2025

USE luthor_portfolio;

-- Add portfolio content table for basic site content
CREATE TABLE IF NOT EXISTS portfolio_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(100) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    content_type ENUM('text', 'textarea', 'html') DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section_field (section, field_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add portfolio skills table
CREATE TABLE IF NOT EXISTS portfolio_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    skill_name VARCHAR(200) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add portfolio experience table
CREATE TABLE IF NOT EXISTS portfolio_experience (
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

-- Add portfolio education table
CREATE TABLE IF NOT EXISTS portfolio_education (
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

-- Add portfolio works table for dynamic portfolio management
CREATE TABLE IF NOT EXISTS portfolio_works (
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

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_portfolio_content_section ON portfolio_content(section);
CREATE INDEX IF NOT EXISTS idx_portfolio_content_section_field ON portfolio_content(section, field_name);
CREATE INDEX IF NOT EXISTS idx_portfolio_skills_category ON portfolio_skills(category);
CREATE INDEX IF NOT EXISTS idx_portfolio_skills_sort_order ON portfolio_skills(sort_order);
CREATE INDEX IF NOT EXISTS idx_portfolio_experience_company ON portfolio_experience(company);
CREATE INDEX IF NOT EXISTS idx_portfolio_experience_date_start ON portfolio_experience(date_start);
CREATE INDEX IF NOT EXISTS idx_portfolio_experience_is_present ON portfolio_experience(is_present);
CREATE INDEX IF NOT EXISTS idx_portfolio_education_institution ON portfolio_education(institution);
CREATE INDEX IF NOT EXISTS idx_portfolio_education_date_start ON portfolio_education(date_start);
CREATE INDEX IF NOT EXISTS idx_portfolio_education_is_present ON portfolio_education(is_present);
CREATE INDEX IF NOT EXISTS idx_portfolio_works_category ON portfolio_works(category);
CREATE INDEX IF NOT EXISTS idx_portfolio_works_sort_order ON portfolio_works(sort_order);
CREATE INDEX IF NOT EXISTS idx_portfolio_works_is_featured ON portfolio_works(is_featured);

-- Insert sample portfolio works data (only if table is empty)
INSERT IGNORE INTO portfolio_works (title, category, description, project_url, image_path, gallery_image_path, sort_order) VALUES
('Sample Web Project', 'Website', 'A modern responsive website built with the latest technologies.', 'https://example.com', 'images/portfolio/sample-web.jpg', 'images/portfolio/gallery/g-sample-web.jpg', 1),
('Mobile App Design', 'App Design', 'User-friendly mobile application with intuitive interface design.', 'https://example.com/app', 'images/portfolio/sample-app.jpg', 'images/portfolio/gallery/g-sample-app.jpg', 2),
('Brand Identity', 'Branding', 'Complete brand identity package including logo and visual guidelines.', 'https://example.com/brand', 'images/portfolio/sample-brand.jpg', 'images/portfolio/gallery/g-sample-brand.jpg', 3);

-- Ensure proper charset for all portfolio tables
ALTER TABLE portfolio_content CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_skills CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_experience CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_education CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE portfolio_works CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Migration completed successfully
SELECT 'Portfolio system migration completed successfully!' AS Status;
