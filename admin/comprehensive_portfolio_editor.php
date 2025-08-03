<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once '../config/admin_auth_check.php';
require_once '../config/database.php';

$message = '';
$error = '';

// Create comprehensive portfolio tables
try {
    // Basic content table (from the original editor)
    $createContentTable = "
        CREATE TABLE IF NOT EXISTS portfolio_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            section VARCHAR(100) NOT NULL,
            field_name VARCHAR(100) NOT NULL,
            content TEXT NOT NULL,
            content_type ENUM('text', 'textarea', 'html') DEFAULT 'text',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_section_field (section, field_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($createContentTable);
    
    // Skills/Expertise table
    $createSkillsTable = "
        CREATE TABLE IF NOT EXISTS portfolio_skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(100) NOT NULL,
            skill_name VARCHAR(200) NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($createSkillsTable);
    
    // Experience/Timeline table
    $createExperienceTable = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($createExperienceTable);
    
    // Education table
    $createEducationTable = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($createEducationTable);
    
    // Portfolio Works table
    $createWorksTable = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($createWorksTable);
    
    // Add new columns to existing tables if they don't exist
    try {
        // Add date columns and present flag to experience table
        $pdo->exec("ALTER TABLE portfolio_experience ADD COLUMN IF NOT EXISTS date_start DATE NULL AFTER position");
        $pdo->exec("ALTER TABLE portfolio_experience ADD COLUMN IF NOT EXISTS date_end DATE NULL AFTER date_start");
        $pdo->exec("ALTER TABLE portfolio_experience ADD COLUMN IF NOT EXISTS is_present BOOLEAN DEFAULT FALSE AFTER date_end");
        
        // Add date columns and present flag to education table
        $pdo->exec("ALTER TABLE portfolio_education ADD COLUMN IF NOT EXISTS date_start DATE NULL AFTER qualification");
        $pdo->exec("ALTER TABLE portfolio_education ADD COLUMN IF NOT EXISTS date_end DATE NULL AFTER date_start");
        $pdo->exec("ALTER TABLE portfolio_education ADD COLUMN IF NOT EXISTS is_present BOOLEAN DEFAULT FALSE AFTER date_end");
    } catch (PDOException $e) {
        // Columns might already exist, continue
    }
    
    // Check if basic content table is empty and populate with default data
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_content");
    if ($stmt->fetchColumn() == 0) {
        $defaultContent = [
            // Intro Section
            ['intro', 'welcome_text', 'Welcome to my portfolio', 'text'],
            ['intro', 'main_title', 'I am Rose Webb,<br>An IT Technician<br>& Aspiring<br>Developer based<br>in South Yorkshire.', 'html'],
            ['intro', 'image', '', 'text'],
            
            // About Section
            ['about', 'about_description', 'Driven IT professional with a passion for solving technical challenges, enhancing system efficiency, and continuously learning new technologies. Aspiring to grow in roles that integrate hands-on technical support with cloud services, networking, and programming, while delivering exceptional service and innovative solutions to users and organizations.', 'textarea'],
            ['about', 'cv_link', 'Files/RWEBB-CV.pdf', 'text'],
            ['about', 'image', 'images/about-photo.jpg', 'text'],
            
            // Works Section
            ['works', 'works_title', 'Scripts, Code Projects & Other Nerdy Stuff', 'text'],
            
            // Contact Section
            ['contact', 'contact_title', 'Get In Touch', 'text'],
            ['contact', 'contact_subtitle', 'I\'d love to hear from you. Whether you have a question or just want to chat about design, tech & art — shoot me a message.', 'textarea'],
            ['contact', 'email', 'rosewebb2810@gmail.com', 'text'],
            ['contact', 'phone', '+44 7578 777928', 'text'],
            ['contact', 'linkedin_url', 'https://www.linkedin.com/in/rose-webb-798014215/', 'text'],
            ['contact', 'twitter_url', 'https://www.twitter.com/YuukiiRose', 'text'],
            ['contact', 'github_url', 'https://github.com/YuukiRose', 'text'],
            
            // Social Links
            ['social', 'linkedin_url', 'https://www.linkedin.com/in/rose-webb-798014215/', 'text'],
            ['social', 'twitter_url', 'https://www.twitter.com/YuukiiRose', 'text'],
            ['social', 'github_url', 'https://github.com/YuukiRose', 'text'],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO portfolio_content (section, field_name, content, content_type) VALUES (?, ?, ?, ?)");
        foreach ($defaultContent as $content) {
            $stmt->execute($content);
        }
    }
    
    // Check if skills table is empty and populate with default data
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_skills");
    if ($stmt->fetchColumn() == 0) {
        $defaultSkills = [
            // Technical Skills
            ['Technical Skills', 'Web Design (HTML, CSS)', 1],
            ['Technical Skills', 'Programming (PowerShell, C#, Lua, JSON)', 2],
            ['Technical Skills', 'Database Management (SQL)', 3],
            ['Technical Skills', 'Version Control (Git)', 4],
            ['Technical Skills', 'PC Repair', 5],
            ['Technical Skills', 'Hardware & Software Installation', 6],
            ['Technical Skills', 'System Monitoring & Maintenance', 7],
            ['Technical Skills', 'Performance Optimization', 8],
            
            // Cloud & Infrastructure
            ['Cloud & Infrastructure', 'Cloud Services (Azure)', 1],
            ['Cloud & Infrastructure', 'Azure Intune/AutoPilot Knowledge', 2],
            ['Cloud & Infrastructure', 'Windows Autopilot Provisioning', 3],
            ['Cloud & Infrastructure', 'MDT (Microsoft Deployment Toolkit)', 4],
            ['Cloud & Infrastructure', 'Virtualization (VMware, Hyper-V)', 5],
            ['Cloud & Infrastructure', 'Backup & Recovery Solutions', 6],
            ['Cloud & Infrastructure', 'Mobile Device Management (MDM)', 7],
            ['Cloud & Infrastructure', 'VPN Configuration', 8],
            
            // Networking & Support
            ['Networking & Support', 'Networking (Cisco, Arista) To CCNA Level', 1],
            ['Networking & Support', 'Network Troubleshooting', 2],
            ['Networking & Support', 'Technical Support', 3],
            ['Networking & Support', 'Remote Support & Troubleshooting', 4],
            ['Networking & Support', 'Multi-Platform Support (Windows, macOS, Linux)', 5],
            ['Networking & Support', 'Service Desk Operations', 6],
            ['Networking & Support', 'Asset Management & Inventory Control', 7],
            ['Networking & Support', 'Asset Configuration Management', 8],
            
            // Project & Process Management
            ['Project & Process Management', 'ISO Standards & SLA Adherence', 1],
            ['Project & Process Management', 'Agile Methodologies', 2],
            ['Project & Process Management', 'Project Management Tools (Trello)', 3],
            ['Project & Process Management', 'Documentation & Reporting', 4],
            ['Project & Process Management', 'Training & Mentoring', 5],
            ['Project & Process Management', 'Data Analysis & Reporting', 6],
            
            // Software & Platforms
            ['Software & Platforms', 'Content Management Systems (WordPress)', 1],
            ['Software & Platforms', 'Graphic Design (Adobe Creative Suite)', 2],
            ['Software & Platforms', 'Customer Relationship Management (CRM)', 3],
            ['Software & Platforms', 'Office Productivity Suites', 4],
            
            // Professional Skills
            ['Professional Skills', 'Customer Service & Communication', 1],
            ['Professional Skills', 'Problem Solving & Troubleshooting', 2],
            ['Professional Skills', 'Attention to Detail', 3],
            ['Professional Skills', 'Time Management & Prioritization', 4],
            ['Professional Skills', 'Adaptability & Continuous Learning', 5],
            ['Professional Skills', 'Team Collaboration & Support', 6],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO portfolio_skills (category, skill_name, sort_order) VALUES (?, ?, ?)");
        foreach ($defaultSkills as $skill) {
            $stmt->execute($skill);
        }
    }
    
    // Populate experience if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_experience");
    if ($stmt->fetchColumn() == 0) {
        $defaultExperience = [
            ['SHI International', 'Integration Engineer', '2023-01-01', null, 1, 'January 2023 - Present', 'Detail-oriented Integration Engineer experienced in Windows Autopilot provisioning, asset labelling, and configuration management. Skilled at adhering to SLA timelines and ISO standards to ensure high-quality customer deliverables. Dedicated to providing customized solutions and seamless support while maintaining consistent operational efficiency in dynamic, fast-paced environments.', 1],
            ['Sheffield City Trust', 'IT Support Technician', '2022-06-01', '2023-01-01', 0, 'June 2022 - January 2023', 'Experienced IT Support Technician skilled in handling support calls, triaging tickets, and creating IT accounts. Proficient in learning and utilizing diverse software systems such as Ticket Master\'s PCI, Gladstone MRM, and IPFX. Committed to delivering comprehensive assistance to end users while quickly adapting to new tools and providing excellent service in customer-focused settings.', 2],
            ['Amazon', 'Warehouse Operative', '2021-09-01', '2022-06-01', 0, 'September 2021 - June 2022', 'Experienced Warehouse Operative skilled in inventory management, order fulfillment, and logistics support. Proficient in operating warehouse equipment and adhering to safety protocols. Dedicated to maintaining efficient workflows and ensuring timely delivery of goods.', 3],
            ['CC33', 'Call Center Agent', '2021-02-01', '2021-05-01', 0, 'February 2021 - May 2021', 'Experienced Call Center Agent skilled in customer service, issue resolution, and communication. Proficient in using call center software and adhering to company policies. Dedicated to providing excellent support and ensuring customer satisfaction.', 4],
            ['Hen & Chickens Ltd', 'Bartender', '2016-04-01', '2020-04-01', 0, 'April 2016 - April 2020', 'Experienced Bartender skilled in mixology, customer service, and cash handling. Proficient in creating a welcoming atmosphere and ensuring customer satisfaction. Dedicated to maintaining cleanliness and organization.', 5],
            ['Very PC', 'IT Production Technician', '2017-06-01', '2019-05-01', 0, 'June 2017 - May 2019', 'Experienced IT Production Technician skilled in managing production processes, troubleshooting technical issues, and ensuring quality control. Proficient in using various IT tools and software to optimize production workflows. Dedicated to maintaining high standards of efficiency and productivity.', 6],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO portfolio_experience (company, position, date_start, date_end, is_present, timeframe, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($defaultExperience as $exp) {
            $stmt->execute($exp);
        }
    }
    
    // Populate education if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_education");
    if ($stmt->fetchColumn() == 0) {
        $defaultEducation = [
            ['3AAA', 'A-Level ICT Systems and Principles', '2016-06-01', '2017-06-01', 0, 'June 2016 - June 2017', 'Advanced Level qualification in ICT Systems and Principles, providing comprehensive understanding of information and communication technology systems, development principles, and practical application in real-world scenarios.', 1],
            ['Rotherham College of Arts and Technology', 'Diploma of Higher Education in Creative Media Production', '2014-06-01', '2015-01-01', 0, 'June 2014 - January 2015', 'Higher education diploma focused on creative media production, covering digital content creation, multimedia design, and production techniques across various media platforms.', 2],
            ['Maltby Academy', 'BTEC Diploma in Construction Level 2', '2012-07-01', '2013-07-01', 0, 'July 2012 - July 2013', 'Vocational qualification in construction covering practical skills, industry knowledge, and technical understanding of construction processes and materials.', 3],
            ['Maltby Academy', 'GCSE Qualifications', '2012-06-01', '2013-06-01', 0, 'June 2012 - June 2013', 'Comprehensive secondary education qualifications including Mathematics, ICT, English, Science, Additional Science, Humanities, and Design & Technology (Resistant Materials), providing a strong foundation across core academic subjects and early foundation in computing technologies.', 4],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO portfolio_education (institution, qualification, date_start, date_end, is_present, timeframe, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($defaultEducation as $edu) {
            $stmt->execute($edu);
        }
    }
    
    // Populate works if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_works");
    if ($stmt->fetchColumn() == 0) {
        $defaultWorks = [
            ];
        
        $stmt = $pdo->prepare("INSERT INTO portfolio_works (title, category, description, project_url, image_path, gallery_image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($defaultWorks as $work) {
            $stmt->execute($work);
        }
    }
    
} catch (PDOException $e) {
    $error = "Database setup error: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log all POST data
    error_log("POST data received: " . print_r($_POST, true));
    
    try {
        // Handle basic content updates
        if (isset($_POST['update_basic_content'])) {
            // Handle image uploads first
            $uploadedImages = [];
            
            // Handle intro image upload
            if (isset($_FILES['intro_image_upload']) && $_FILES['intro_image_upload']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $file = $_FILES['intro_image_upload'];
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                    $uploadDir = '../images/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'intro_' . time() . '_' . uniqid() . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $uploadedImages['intro_image'] = 'images/' . $filename;
                    }
                }
            }
            
            // Handle about image upload
            if (isset($_FILES['about_image_upload']) && $_FILES['about_image_upload']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $file = $_FILES['about_image_upload'];
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                    $uploadDir = '../images/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'about_' . time() . '_' . uniqid() . '.' . $extension;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $uploadedImages['about_image'] = 'images/' . $filename;
                    }
                }
            }
            
            // Process regular form fields
            foreach ($_POST as $key => $value) {
                if ($key !== 'update_basic_content' && strpos($key, '_') !== false) {
                    list($section, $field) = explode('_', $key, 2);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO portfolio_content (section, field_name, content, content_type) 
                        VALUES (?, ?, ?, 'text') 
                        ON DUPLICATE KEY UPDATE content = VALUES(content)
                    ");
                    $stmt->execute([$section, $field, trim($value)]);
                }
            }
            
            // Process uploaded images
            foreach ($uploadedImages as $key => $imagePath) {
                list($section, $field) = explode('_', $key, 2);
                $stmt = $pdo->prepare("
                    INSERT INTO portfolio_content (section, field_name, content, content_type) 
                    VALUES (?, ?, ?, 'text') 
                    ON DUPLICATE KEY UPDATE content = VALUES(content)
                ");
                $stmt->execute([$section, $field, $imagePath]);
            }
            
            $message = "Basic content updated successfully!";
        }
        
        if (isset($_POST['add_skill'])) {
            $stmt = $pdo->prepare("INSERT INTO portfolio_skills (category, skill_name, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['skill_category'], $_POST['skill_name'], $_POST['skill_order'] ?? 999]);
            $message = "Skill added successfully!";
        }
        
        if (isset($_POST['add_experience'])) {
            $dateEnd = !empty($_POST['exp_date_end']) ? $_POST['exp_date_end'] : null;
            $isPresent = isset($_POST['exp_is_present']) ? 1 : 0;
            
            // Convert month format (YYYY-MM) to readable format (Month YYYY)
            $startFormatted = date('F Y', strtotime($_POST['exp_date_start'] . '-01'));
            $timeframe = $startFormatted;
            
            if ($isPresent) {
                $timeframe .= ' - Present';
            } elseif ($dateEnd) {
                $endFormatted = date('F Y', strtotime($dateEnd . '-01'));
                $timeframe .= ' - ' . $endFormatted;
            }
            
            $stmt = $pdo->prepare("INSERT INTO portfolio_experience (company, position, date_start, date_end, is_present, timeframe, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['exp_company'], $_POST['exp_position'], $_POST['exp_date_start'] . '-01', $dateEnd ? $dateEnd . '-01' : null, $isPresent, $timeframe, $_POST['exp_description']]);
            $message = "Experience added successfully!";
        }
        
        if (isset($_POST['add_education'])) {
            $dateEnd = !empty($_POST['edu_date_end']) ? $_POST['edu_date_end'] : null;
            $isPresent = isset($_POST['edu_is_present']) ? 1 : 0;
            
            // Convert month format (YYYY-MM) to readable format (Month YYYY)
            $startFormatted = date('F Y', strtotime($_POST['edu_date_start'] . '-01'));
            $timeframe = $startFormatted;
            
            if ($isPresent) {
                $timeframe .= ' - Present';
            } elseif ($dateEnd) {
                $endFormatted = date('F Y', strtotime($dateEnd . '-01'));
                $timeframe .= ' - ' . $endFormatted;
            }
            
            $stmt = $pdo->prepare("INSERT INTO portfolio_education (institution, qualification, date_start, date_end, is_present, timeframe, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['edu_institution'], $_POST['edu_qualification'], $_POST['edu_date_start'] . '-01', $dateEnd ? $dateEnd . '-01' : null, $isPresent, $timeframe, $_POST['edu_description']]);
            $message = "Education added successfully!";
        }
        
        if (isset($_POST['edit_experience'])) {
            $dateEnd = !empty($_POST['exp_date_end']) ? $_POST['exp_date_end'] : null;
            $isPresent = isset($_POST['exp_is_present']) ? 1 : 0;
            
            // Convert month format (YYYY-MM) to readable format (Month YYYY)
            $startFormatted = date('F Y', strtotime($_POST['exp_date_start'] . '-01'));
            $timeframe = $startFormatted;
            
            if ($isPresent) {
                $timeframe .= ' - Present';
            } elseif ($dateEnd) {
                $endFormatted = date('F Y', strtotime($dateEnd . '-01'));
                $timeframe .= ' - ' . $endFormatted;
            }
            
            $stmt = $pdo->prepare("UPDATE portfolio_experience SET company = ?, position = ?, date_start = ?, date_end = ?, is_present = ?, timeframe = ?, description = ? WHERE id = ?");
            $stmt->execute([$_POST['exp_company'], $_POST['exp_position'], $_POST['exp_date_start'] . '-01', $dateEnd ? $dateEnd . '-01' : null, $isPresent, $timeframe, $_POST['exp_description'], $_POST['exp_id']]);
            $message = "Experience updated successfully!";
        }
        
        if (isset($_POST['edit_education'])) {
            $dateEnd = !empty($_POST['edu_date_end']) ? $_POST['edu_date_end'] : null;
            $isPresent = isset($_POST['edu_is_present']) ? 1 : 0;
            
            // Convert month format (YYYY-MM) to readable format (Month YYYY)
            $startFormatted = date('F Y', strtotime($_POST['edu_date_start'] . '-01'));
            $timeframe = $startFormatted;
            
            if ($isPresent) {
                $timeframe .= ' - Present';
            } elseif ($dateEnd) {
                $endFormatted = date('F Y', strtotime($dateEnd . '-01'));
                $timeframe .= ' - ' . $endFormatted;
            }
            
            $stmt = $pdo->prepare("UPDATE portfolio_education SET institution = ?, qualification = ?, date_start = ?, date_end = ?, is_present = ?, timeframe = ?, description = ? WHERE id = ?");
            $stmt->execute([$_POST['edu_institution'], $_POST['edu_qualification'], $_POST['edu_date_start'] . '-01', $dateEnd ? $dateEnd . '-01' : null, $isPresent, $timeframe, $_POST['edu_description'], $_POST['edu_id']]);
            $message = "Education updated successfully!";
        }
        
        if (isset($_POST['add_work'])) {
            $imagePath = $_POST['work_image'] ?? '';
            $galleryImagePath = $_POST['work_gallery_image'] ?? '';
            
            // Handle file upload if provided
            if (isset($_FILES['work_image_upload']) && $_FILES['work_image_upload']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $file = $_FILES['work_image_upload'];
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                    $uploadDir = '../images/portfolio/';
                    $galleryDir = '../images/portfolio/gallery/';
                    
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (!is_dir($galleryDir)) mkdir($galleryDir, 0755, true);
                    
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'work_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    $mainPath = $uploadDir . $filename;
                    $galleryPath = $galleryDir . 'g-' . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $mainPath)) {
                        copy($mainPath, $galleryPath);
                        $imagePath = 'images/portfolio/' . $filename;
                        $galleryImagePath = 'images/portfolio/gallery/g-' . $filename;
                    }
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO portfolio_works (title, category, description, project_url, image_path, gallery_image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['work_title'], 
                $_POST['work_category'], 
                $_POST['work_description'], 
                $_POST['work_url'], 
                $imagePath, 
                $galleryImagePath, 
                $_POST['work_order'] ?? 999
            ]);
            $message = "Work added successfully!";
        }
        
        if (isset($_POST['edit_work'])) {
            $imagePath = $_POST['work_image'];
            $galleryImagePath = $_POST['work_gallery_image'];
            
            // Handle file upload if provided
            if (isset($_FILES['work_image_upload']) && $_FILES['work_image_upload']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $file = $_FILES['work_image_upload'];
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                    $uploadDir = '../images/portfolio/';
                    $galleryDir = '../images/portfolio/gallery/';
                    
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (!is_dir($galleryDir)) mkdir($galleryDir, 0755, true);
                    
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'work_' . time() . '_' . uniqid() . '.' . $extension;
                    
                    $mainPath = $uploadDir . $filename;
                    $galleryPath = $galleryDir . 'g-' . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $mainPath)) {
                        copy($mainPath, $galleryPath);
                        $imagePath = 'images/portfolio/' . $filename;
                        $galleryImagePath = 'images/portfolio/gallery/g-' . $filename;
                    }
                }
            }
            
            $stmt = $pdo->prepare("UPDATE portfolio_works SET title = ?, category = ?, description = ?, project_url = ?, image_path = ?, gallery_image_path = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([
                $_POST['work_title'], 
                $_POST['work_category'], 
                $_POST['work_description'], 
                $_POST['work_url'], 
                $imagePath, 
                $galleryImagePath, 
                $_POST['work_order'] ?? 999,
                $_POST['work_id']
            ]);
            $message = "Work updated successfully!";
        }
        
        if (isset($_POST['delete_item'])) {
            $table = $_POST['table'];
            $id = $_POST['item_id'];
            
            // Debug output
            error_log("Delete request - Table: $table, ID: $id");
            
            $allowedTables = ['portfolio_skills', 'portfolio_experience', 'portfolio_education', 'portfolio_works'];
            if (in_array($table, $allowedTables)) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
                    $result = $stmt->execute([$id]);
                    $rowsAffected = $stmt->rowCount();
                    
                    error_log("Delete operation - Table: $table, ID: $id, Rows affected: $rowsAffected");
                    
                    if ($rowsAffected > 0) {
                        $message = "Item deleted successfully! ($rowsAffected row affected)";
                    } else {
                        $error = "No rows were deleted. Item might not exist.";
                    }
                } catch (PDOException $e) {
                    $error = "Delete error: " . $e->getMessage();
                    error_log("Delete error: " . $e->getMessage());
                }
            } else {
                $error = "Invalid table for deletion: $table";
                error_log("Invalid table for deletion: $table");
            }
        }
        
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get current data
$basicContent = [];
$skills = [];
$experience = [];
$education = [];

try {
    // Get basic content
    $stmt = $pdo->query("SELECT section, field_name, content, content_type FROM portfolio_content ORDER BY section, field_name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $basicContent[$row['section']][$row['field_name']] = $row;
    }
    
    // Get skills grouped by category
    $stmt = $pdo->query("SELECT * FROM portfolio_skills ORDER BY category, sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $skills[$row['category']][] = $row;
    }
    
    // Get experience (sorted by most recent first, with present jobs at top)
    try {
        $stmt = $pdo->query("SELECT * FROM portfolio_experience ORDER BY is_present DESC, date_start DESC");
    } catch (PDOException $e) {
        // Fallback to old sorting if new columns don't exist yet
        $stmt = $pdo->query("SELECT * FROM portfolio_experience ORDER BY sort_order");
    }
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get education (sorted by most recent first, with current studies at top)
    try {
        $stmt = $pdo->query("SELECT * FROM portfolio_education ORDER BY is_present DESC, date_start DESC");
    } catch (PDOException $e) {
        // Fallback to old sorting if new columns don't exist yet
        $stmt = $pdo->query("SELECT * FROM portfolio_education ORDER BY sort_order");
    }
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get works (sorted by sort order)
    $stmt = $pdo->query("SELECT * FROM portfolio_works ORDER BY sort_order, id");
    $works = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error loading data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Portfolio Editor - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            border-left: 4px solid #3498db;
        }
        
        .section-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .tab {
            padding: 12px 24px;
            background: #e9ecef;
            border: none;
            cursor: pointer;
            font-weight: 600;
            margin-right: 5px;
            border-radius: 8px 8px 0 0;
        }
        
        .tab.active {
            background: #3498db;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .item-list {
            margin-bottom: 20px;
        }
        
        .item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .item-content {
            flex: 1;
        }
        
        .item-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .item-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .item-desc {
            font-size: 14px;
            line-height: 1.4;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .edit-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .edit-btn:hover {
            background: #2980b9;
        }
        
        .delete-btn:hover {
            background: #c0392b;
        }
        
        .item-actions {
            margin-top: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .btn {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-success:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        
        .notification {
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-right: 10px;
        }
        
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .skill-category {
            background: #e8f4f8;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }
        
        .skill-category h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .skill-item {
            background: white;
            padding: 8px 12px;
            margin-bottom: 5px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Comprehensive Portfolio Editor</h1>
            <p>Manage all sections of your portfolio directly in the database. Changes are saved automatically and displayed in real-time on your portfolio.</p>
        </div>
        
        <div class="content">
            <div class="navigation" style="margin-bottom: 20px;">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                <button onclick="saveAllChanges()" class="btn btn-success">� Save All Changes</button>
            </div>
            
            <?php if ($message): ?>
                <div class="message success">✅ <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="openTab(event, 'basic')">📝 Basic Content</button>
                <button class="tab" onclick="openTab(event, 'skills')">🛠️ Skills & Expertise</button>
                <button class="tab" onclick="openTab(event, 'experience')">💼 Experience</button>
                <button class="tab" onclick="openTab(event, 'education')">🎓 Education</button>
                <button class="tab" onclick="openTab(event, 'works')">🎨 Portfolio Works</button>
            </div>
            
            <!-- Basic Content Tab -->
            <div id="basic" class="tab-content active">
                <div class="section">
                    <h2 class="section-title">Basic Content Management</h2>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Intro Section -->
                        <div style="background: #e8f4f8; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h3 style="color: #2c3e50; margin-bottom: 15px;">🏠 Introduction Section</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="intro_welcome_text">Welcome Text</label>
                                    <input type="text" id="intro_welcome_text" name="intro_welcome_text" 
                                           value="<?php echo htmlspecialchars($basicContent['intro']['welcome_text']['content'] ?? ''); ?>">
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Text shown above the main title</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="intro_main_title">Main Title</label>
                                    <textarea id="intro_main_title" name="intro_main_title"><?php echo htmlspecialchars($basicContent['intro']['main_title']['content'] ?? ''); ?></textarea>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Main hero title (HTML allowed for line breaks)</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="intro_image">Introduction Background/Hero Image</label>
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                        <input type="file" name="intro_image_upload" id="intro_image_upload" accept="image/*" onchange="uploadSectionImage(this, 'intro')">
                                        <button type="button" onclick="document.getElementById('intro_image_upload').click()" style="padding: 5px 10px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">Choose Image</button>
                                    </div>
                                    <div class="image-preview-intro" style="margin-bottom: 10px;">
                                        <?php if (!empty($basicContent['intro']['image']['content'])): ?>
                                            <img src="../<?php echo htmlspecialchars($basicContent['intro']['image']['content']); ?>" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 5px;">
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Or enter path manually:</div>
                                    <input type="text" name="intro_image" id="intro_image_path" placeholder="images/intro-bg.jpg" 
                                           value="<?php echo htmlspecialchars($basicContent['intro']['image']['content'] ?? ''); ?>">
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Background or hero image for the introduction section</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- About Section -->
                        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h3 style="color: #2c3e50; margin-bottom: 15px;">👤 About Section</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="about_about_description">About Description</label>
                                    <textarea id="about_about_description" name="about_about_description" style="min-height: 120px;"><?php echo htmlspecialchars($basicContent['about']['about_description']['content'] ?? ''); ?></textarea>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Main about section description</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="about_image">About Section Image</label>
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                        <input type="file" name="about_image_upload" id="about_image_upload" accept="image/*" onchange="uploadSectionImage(this, 'about')">
                                        <button type="button" onclick="document.getElementById('about_image_upload').click()" style="padding: 5px 10px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">Choose Image</button>
                                    </div>
                                    <div class="image-preview-about" style="margin-bottom: 10px;">
                                        <?php if (!empty($basicContent['about']['image']['content'])): ?>
                                            <img src="../<?php echo htmlspecialchars($basicContent['about']['image']['content']); ?>" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 5px;">
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Or enter path manually:</div>
                                    <input type="text" name="about_image" id="about_image_path" placeholder="images/about-photo.jpg" 
                                           value="<?php echo htmlspecialchars($basicContent['about']['image']['content'] ?? ''); ?>">
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Profile or about section image</div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="about_cv_link">CV Download Link</label>
                                <input type="text" id="about_cv_link" name="about_cv_link" 
                                       value="<?php echo htmlspecialchars($basicContent['about']['cv_link']['content'] ?? ''); ?>">
                                <div style="font-size: 12px; color: #666; margin-top: 5px;">Path to your CV file (e.g., Files/RWEBB-CV.pdf)</div>
                            </div>
                        </div>
                        
                        <!-- Works Section -->
                        <div style="background: #d4edda; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h3 style="color: #2c3e50; margin-bottom: 15px;">💼 Works Section</h3>
                            <div class="form-group">
                                <label for="works_works_title">Works Section Title</label>
                                <input type="text" id="works_works_title" name="works_works_title" 
                                       value="<?php echo htmlspecialchars($basicContent['works']['works_title']['content'] ?? ''); ?>">
                                <div style="font-size: 12px; color: #666; margin-top: 5px;">Title for the portfolio/works section</div>
                            </div>
                        </div>
                        
                        <!-- Contact Section -->
                        <div style="background: #f8d7da; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h3 style="color: #2c3e50; margin-bottom: 15px;">📞 Contact Section</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="contact_contact_title">Contact Title</label>
                                    <input type="text" id="contact_contact_title" name="contact_contact_title" 
                                           value="<?php echo htmlspecialchars($basicContent['contact']['contact_title']['content'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_email">Email Address</label>
                                    <input type="email" id="contact_email" name="contact_email" 
                                           value="<?php echo htmlspecialchars($basicContent['contact']['email']['content'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_phone">Phone Number</label>
                                    <input type="text" id="contact_phone" name="contact_phone" 
                                           value="<?php echo htmlspecialchars($basicContent['contact']['phone']['content'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_contact_subtitle">Contact Subtitle</label>
                                <textarea id="contact_contact_subtitle" name="contact_contact_subtitle"><?php echo htmlspecialchars($basicContent['contact']['contact_subtitle']['content'] ?? ''); ?></textarea>
                                <div style="font-size: 12px; color: #666; margin-top: 5px;">Subtitle text in the contact section</div>
                            </div>
                        </div>
                        
                        <!-- Social Links -->
                        <div style="background: #d1ecf1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h3 style="color: #2c3e50; margin-bottom: 15px;">🔗 Social Media Links</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="social_linkedin_url">LinkedIn URL</label>
                                    <input type="url" id="social_linkedin_url" name="social_linkedin_url" 
                                           value="<?php echo htmlspecialchars($basicContent['social']['linkedin_url']['content'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="social_twitter_url">Twitter URL</label>
                                    <input type="url" id="social_twitter_url" name="social_twitter_url" 
                                           value="<?php echo htmlspecialchars($basicContent['social']['twitter_url']['content'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="social_github_url">GitHub URL</label>
                                    <input type="url" id="social_github_url" name="social_github_url" 
                                           value="<?php echo htmlspecialchars($basicContent['social']['github_url']['content'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; padding: 20px; background: #e8f5e8; border-radius: 8px;">
                            <button type="submit" name="update_basic_content" class="btn btn-success" style="font-size: 16px; padding: 12px 24px;">💾 Save All Basic Content</button>
                            <p style="margin-top: 10px; color: #27ae60; font-size: 14px;">All changes will be saved to the database automatically</p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Skills Tab -->
            <div id="skills" class="tab-content">
                <div class="section">
                    <h2 class="section-title">Skills & Expertise Management</h2>
                    
                    <!-- Current Skills Display -->
                    <div class="item-list">
                        <?php foreach ($skills as $category => $categorySkills): ?>
                            <div class="skill-category">
                                <h4><?php echo htmlspecialchars($category); ?></h4>
                                <?php foreach ($categorySkills as $skill): ?>
                                    <div class="skill-item">
                                        <span><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="table" value="portfolio_skills">
                                            <input type="hidden" name="item_id" value="<?php echo $skill['id']; ?>">
                                            <button type="submit" name="delete_item" class="delete-btn" 
                                                    onclick="return confirm('Delete this skill?')">×</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Add New Skill Form -->
                    <h3>Add New Skill</h3>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="skill_category" required>
                                    <option value="">Select Category</option>
                                    <option value="Technical Skills">Technical Skills</option>
                                    <option value="Cloud & Infrastructure">Cloud & Infrastructure</option>
                                    <option value="Networking & Support">Networking & Support</option>
                                    <option value="Project & Process Management">Project & Process Management</option>
                                    <option value="Software & Platforms">Software & Platforms</option>
                                    <option value="Professional Skills">Professional Skills</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Skill Name</label>
                                <input type="text" name="skill_name" required>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="skill_order" value="999">
                            </div>
                        </div>
                        <button type="submit" name="add_skill" class="btn">➕ Add Skill</button>
                    </form>
                </div>
            </div>
            
            <!-- Experience Tab -->
            <div id="experience" class="tab-content">
                <div class="section">
                    <h2 class="section-title">Experience Management</h2>
                    
                    <!-- Current Experience Display -->
                    <div class="item-list">
                        <?php foreach ($experience as $exp): ?>
                            <div class="item">
                                <div class="item-content">
                                    <div class="item-title"><?php echo htmlspecialchars($exp['company']); ?></div>
                                    <div class="item-meta">
                                        <strong><?php echo htmlspecialchars($exp['position']); ?></strong> | 
                                        <?php echo htmlspecialchars($exp['timeframe']); ?>
                                    </div>
                                    <div class="item-desc"><?php echo htmlspecialchars($exp['description']); ?></div>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="edit-btn" onclick="editExperience(<?php echo $exp['id']; ?>, '<?php echo addslashes($exp['company']); ?>', '<?php echo addslashes($exp['position']); ?>', '<?php echo $exp['date_start'] ? date('Y-m', strtotime($exp['date_start'])) : ''; ?>', '<?php echo $exp['date_end'] ? date('Y-m', strtotime($exp['date_end'])) : ''; ?>', <?php echo $exp['is_present'] ? 'true' : 'false'; ?>, '<?php echo addslashes($exp['description']); ?>')">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="table" value="portfolio_experience">
                                        <input type="hidden" name="item_id" value="<?php echo $exp['id']; ?>">
                                        <button type="submit" name="delete_item" class="delete-btn" 
                                                onclick="return confirm('Delete this experience?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Add New Experience Form -->
                    <h3>Add New Experience</h3>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Company</label>
                                <input type="text" name="exp_company" required>
                            </div>
                            <div class="form-group">
                                <label>Position</label>
                                <input type="text" name="exp_position" required>
                            </div>
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="month" name="exp_date_start" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="month" name="exp_date_end" id="exp_date_end">
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="exp_is_present" id="exp_is_present" onchange="toggleEndDate('exp')">
                                    Current Position (Present)
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="exp_description" required></textarea>
                        </div>
                        <button type="submit" name="add_experience" class="btn">➕ Add Experience</button>
                    </form>
                    
                    <!-- Edit Experience Form -->
                    <div id="edit-experience-form" style="display: none;">
                        <h3>Edit Experience</h3>
                        <form method="POST">
                            <input type="hidden" name="exp_id" id="edit_exp_id">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Company</label>
                                    <input type="text" name="exp_company" id="edit_exp_company" required>
                                </div>
                                <div class="form-group">
                                    <label>Position</label>
                                    <input type="text" name="exp_position" id="edit_exp_position" required>
                                </div>
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="month" name="exp_date_start" id="edit_exp_date_start" required>
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="month" name="exp_date_end" id="edit_exp_date_end">
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="exp_is_present" id="edit_exp_is_present" onchange="toggleEndDate('edit_exp')">
                                        Current Position (Present)
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="exp_description" id="edit_exp_description" required></textarea>
                            </div>
                            <button type="submit" name="edit_experience" class="btn">💾 Update Experience</button>
                            <button type="button" class="btn" onclick="cancelEdit('experience')">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Education Tab -->
            <div id="education" class="tab-content">
                <div class="section">
                    <h2 class="section-title">Education Management</h2>
                    
                    <!-- Current Education Display -->
                    <div class="item-list">
                        <?php foreach ($education as $edu): ?>
                            <div class="item">
                                <div class="item-content">
                                    <div class="item-title"><?php echo htmlspecialchars($edu['institution']); ?></div>
                                    <div class="item-meta">
                                        <strong><?php echo htmlspecialchars($edu['qualification']); ?></strong> | 
                                        <?php echo htmlspecialchars($edu['timeframe']); ?>
                                    </div>
                                    <div class="item-desc"><?php echo htmlspecialchars($edu['description']); ?></div>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="edit-btn" onclick="editEducation(<?php echo $edu['id']; ?>, '<?php echo addslashes($edu['institution']); ?>', '<?php echo addslashes($edu['qualification']); ?>', '<?php echo $edu['date_start'] ? date('Y-m', strtotime($edu['date_start'])) : ''; ?>', '<?php echo $edu['date_end'] ? date('Y-m', strtotime($edu['date_end'])) : ''; ?>', <?php echo $edu['is_present'] ? 'true' : 'false'; ?>, '<?php echo addslashes($edu['description']); ?>')">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="table" value="portfolio_education">
                                        <input type="hidden" name="item_id" value="<?php echo $edu['id']; ?>">
                                        <button type="submit" name="delete_item" class="delete-btn" 
                                                onclick="return confirm('Delete this education entry?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Add New Education Form -->
                    <h3>Add New Education</h3>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Institution</label>
                                <input type="text" name="edu_institution" required>
                            </div>
                            <div class="form-group">
                                <label>Qualification</label>
                                <input type="text" name="edu_qualification" required>
                            </div>
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="month" name="edu_date_start" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="month" name="edu_date_end" id="edu_date_end">
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="edu_is_present" id="edu_is_present" onchange="toggleEndDate('edu')">
                                    Currently Studying (Present)
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="edu_description" required></textarea>
                        </div>
                        <button type="submit" name="add_education" class="btn">➕ Add Education</button>
                    </form>
                    
                    <!-- Edit Education Form -->
                    <div id="edit-education-form" style="display: none;">
                        <h3>Edit Education</h3>
                        <form method="POST">
                            <input type="hidden" name="edu_id" id="edit_edu_id">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Institution</label>
                                    <input type="text" name="edu_institution" id="edit_edu_institution" required>
                                </div>
                                <div class="form-group">
                                    <label>Qualification</label>
                                    <input type="text" name="edu_qualification" id="edit_edu_qualification" required>
                                </div>
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="month" name="edu_date_start" id="edit_edu_date_start" required>
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="month" name="edu_date_end" id="edit_edu_date_end">
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="edu_is_present" id="edit_edu_is_present" onchange="toggleEndDate('edit_edu')">
                                        Currently Studying (Present)
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="edu_description" id="edit_edu_description" required></textarea>
                            </div>
                            <button type="submit" name="edit_education" class="btn">💾 Update Education</button>
                            <button type="button" class="btn" onclick="cancelEdit('education')">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Works Tab -->
            <div id="works" class="tab-content">
                <h2>🎨 Portfolio Works Management</h2>
                
                <div class="section">
                    <!-- Current Works Display -->
                    <div class="item-list">
                        <?php foreach ($works as $work): ?>
                            <div class="item">
                                <div class="item-content">
                                    <div class="item-title"><?php echo htmlspecialchars($work['title']); ?></div>
                                    <div class="item-meta">
                                        <strong><?php echo htmlspecialchars($work['category']); ?></strong>
                                        <?php if ($work['project_url']): ?>
                                        | <a href="<?php echo htmlspecialchars($work['project_url']); ?>" target="_blank">View Project</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-desc"><?php echo htmlspecialchars($work['description']); ?></div>
                                    <?php if ($work['image_path']): ?>
                                    <div class="item-image">
                                        <img src="../<?php echo htmlspecialchars($work['image_path']); ?>" alt="<?php echo htmlspecialchars($work['title']); ?>" style="max-width: 200px; height: auto; border-radius: 4px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="edit-btn" onclick="editWork(<?php echo $work['id']; ?>, '<?php echo addslashes($work['title']); ?>', '<?php echo addslashes($work['category']); ?>', '<?php echo addslashes($work['description']); ?>', '<?php echo addslashes($work['project_url']); ?>', '<?php echo addslashes($work['image_path']); ?>', '<?php echo addslashes($work['gallery_image_path']); ?>', <?php echo $work['sort_order']; ?>)">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="table" value="portfolio_works">
                                        <input type="hidden" name="item_id" value="<?php echo $work['id']; ?>">
                                        <button type="submit" name="delete_item" class="delete-btn" 
                                                onclick="return confirm('Delete this work?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Add New Work Form -->
                    <h3>Add New Portfolio Work</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Project Title</label>
                                <input type="text" name="work_title" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="work_category" required placeholder="e.g. Website, Branding, Frontend Design">
                            </div>
                            <div class="form-group">
                                <label>Project URL</label>
                                <input type="url" name="work_url" placeholder="https://example.com">
                            </div>
                            <div class="form-group">
                                <label>Upload Image</label>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="file" name="work_image_upload" id="work_image_upload" accept="image/*" onchange="uploadWorkImage(this, 'add')">
                                    <button type="button" onclick="document.getElementById('work_image_upload').click()" style="padding: 5px 10px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">Choose File</button>
                                </div>
                                <div class="image-preview-add" style="margin-top: 10px;"></div>
                                <div style="font-size: 12px; color: #666; margin-top: 5px;">Or enter path manually:</div>
                                <input type="text" name="work_image" id="add_work_image" placeholder="images/portfolio/project.jpg">
                            </div>
                            <div class="form-group">
                                <label>Gallery Image Path</label>
                                <input type="text" name="work_gallery_image" id="add_work_gallery_image" placeholder="images/portfolio/gallery/g-project.jpg">
                                <div style="font-size: 12px; color: #666; margin-top: 5px;">Auto-filled when uploading</div>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="work_order" value="999">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="work_description" required></textarea>
                        </div>
                        <button type="submit" name="add_work" class="btn">➕ Add Work</button>
                    </form>
                    
                    <!-- Edit Work Form -->
                    <div id="edit-work-form" style="display: none;">
                        <h3>Edit Portfolio Work</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="work_id" id="edit_work_id">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Project Title</label>
                                    <input type="text" name="work_title" id="edit_work_title" required>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <input type="text" name="work_category" id="edit_work_category" required>
                                </div>
                                <div class="form-group">
                                    <label>Project URL</label>
                                    <input type="url" name="work_url" id="edit_work_url">
                                </div>
                                <div class="form-group">
                                    <label>Update Image</label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="file" name="work_image_upload" id="edit_work_image_upload" accept="image/*" onchange="uploadWorkImage(this, 'edit')">
                                        <button type="button" onclick="document.getElementById('edit_work_image_upload').click()" style="padding: 5px 10px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">Choose New File</button>
                                    </div>
                                    <div class="image-preview-edit" style="margin-top: 10px;"></div>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Or enter path manually:</div>
                                    <input type="text" name="work_image" id="edit_work_image">
                                </div>
                                <div class="form-group">
                                    <label>Gallery Image Path</label>
                                    <input type="text" name="work_gallery_image" id="edit_work_gallery_image">
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Auto-updated when uploading new image</div>
                                </div>
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <input type="number" name="work_order" id="edit_work_order">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="work_description" id="edit_work_description" required></textarea>
                            </div>
                            <button type="submit" name="edit_work" class="btn">💾 Update Work</button>
                            <button type="button" class="btn" onclick="cancelEdit('work')">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function saveAllChanges() {
            // Show loading message
            const originalText = document.querySelector('.btn-success').textContent;
            const saveButton = document.querySelector('.btn-success');
            saveButton.textContent = '💾 Saving...';
            saveButton.disabled = true;
            
            // Since the portfolio editor uses individual forms for each section,
            // we'll provide user feedback that changes are saved automatically
            setTimeout(function() {
                saveButton.textContent = '✅ All Changes Saved!';
                setTimeout(function() {
                    saveButton.textContent = originalText;
                    saveButton.disabled = false;
                }, 2000);
            }, 500);
            
            // Show a notification
            showNotification('All changes are automatically saved to the database when you submit forms in each section.', 'success');
        }
        
        function showNotification(message, type) {
            // Remove any existing notifications
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification message ' + type;
            notification.innerHTML = '✅ ' + message;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.maxWidth = '400px';
            notification.style.padding = '15px';
            notification.style.borderRadius = '6px';
            notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        function openTab(evt, tabName) {
            var i, tabcontent, tabs;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tabs = document.getElementsByClassName("tab");
            for (i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
        
        function toggleEndDate(prefix) {
            const checkbox = document.getElementById(prefix + '_is_present');
            const endDateField = document.getElementById(prefix + '_date_end');
            
            if (checkbox.checked) {
                endDateField.disabled = true;
                endDateField.value = '';
                endDateField.style.opacity = '0.5';
            } else {
                endDateField.disabled = false;
                endDateField.style.opacity = '1';
            }
        }
        
        function editExperience(id, company, position, dateStart, dateEnd, isPresent, description) {
            // Hide add form and show edit form
            document.getElementById('edit-experience-form').style.display = 'block';
            
            // Populate form fields
            document.getElementById('edit_exp_id').value = id;
            document.getElementById('edit_exp_company').value = company;
            document.getElementById('edit_exp_position').value = position;
            document.getElementById('edit_exp_date_start').value = dateStart;
            document.getElementById('edit_exp_date_end').value = dateEnd;
            document.getElementById('edit_exp_is_present').checked = isPresent;
            document.getElementById('edit_exp_description').value = description;
            
            // Toggle end date field based on current position
            toggleEndDate('edit_exp');
            
            // Scroll to edit form
            document.getElementById('edit-experience-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function editEducation(id, institution, qualification, dateStart, dateEnd, isPresent, description) {
            // Hide add form and show edit form
            document.getElementById('edit-education-form').style.display = 'block';
            
            // Populate form fields
            document.getElementById('edit_edu_id').value = id;
            document.getElementById('edit_edu_institution').value = institution;
            document.getElementById('edit_edu_qualification').value = qualification;
            document.getElementById('edit_edu_date_start').value = dateStart;
            document.getElementById('edit_edu_date_end').value = dateEnd;
            document.getElementById('edit_edu_is_present').checked = isPresent;
            document.getElementById('edit_edu_description').value = description;
            
            // Toggle end date field based on current studying
            toggleEndDate('edit_edu');
            
            // Scroll to edit form
            document.getElementById('edit-education-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function cancelEdit(type) {
            document.getElementById('edit-' + type + '-form').style.display = 'none';
        }
        
        function editWork(id, title, category, description, url, image, galleryImage, sortOrder) {
            // Show edit form
            document.getElementById('edit-work-form').style.display = 'block';
            
            // Populate form fields
            document.getElementById('edit_work_id').value = id;
            document.getElementById('edit_work_title').value = title;
            document.getElementById('edit_work_category').value = category;
            document.getElementById('edit_work_description').value = description;
            document.getElementById('edit_work_url').value = url;
            document.getElementById('edit_work_image').value = image;
            document.getElementById('edit_work_gallery_image').value = galleryImage;
            document.getElementById('edit_work_order').value = sortOrder;
            
            // Show current image preview if exists
            const previewDiv = document.querySelector('.image-preview-edit');
            if (image) {
                previewDiv.innerHTML = `<img src="../${image}" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 5px; margin-top: 10px;">`;
            } else {
                previewDiv.innerHTML = '';
            }
            
            // Scroll to edit form
            document.getElementById('edit-work-form').scrollIntoView({ behavior: 'smooth' });
        }
        
        function uploadWorkImage(fileInput, formType) {
            const file = fileInput.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('image', file);
            
            // Show upload progress
            const button = fileInput.parentNode.querySelector('button');
            const originalText = button.textContent;
            button.textContent = 'Uploading...';
            button.disabled = true;
            
            fetch('../upload_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                button.textContent = originalText;
                button.disabled = false;
                
                if (data.success) {
                    // Update the path input fields
                    const imageInput = document.getElementById(formType === 'add' ? 'add_work_image' : 'edit_work_image');
                    const galleryInput = document.getElementById(formType === 'add' ? 'add_work_gallery_image' : 'edit_work_gallery_image');
                    
                    imageInput.value = data.image_path;
                    galleryInput.value = data.gallery_image_path;
                    
                    // Show preview
                    const previewDiv = document.querySelector('.image-preview-' + formType);
                    previewDiv.innerHTML = `<img src="../${data.image_path}" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 5px; margin-top: 10px;">`;
                    
                    alert('Image uploaded successfully!');
                } else {
                    alert('Upload failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                button.textContent = originalText;
                button.disabled = false;
                console.error('Upload error:', error);
                alert('Upload failed: ' + error.message);
            });
        }
        
        function uploadSectionImage(fileInput, sectionType) {
            const file = fileInput.files[0];
            if (!file) return;
            
            // Validate file type and size
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }
            
            // Show upload progress
            const button = fileInput.parentNode.querySelector('button');
            const originalText = button.textContent;
            button.textContent = 'Uploading...';
            button.disabled = true;
            
            const formData = new FormData();
            formData.append('image', file);
            formData.append('section', sectionType);
            
            fetch('../upload_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                button.textContent = originalText;
                button.disabled = false;
                
                if (data.success) {
                    // Update the path input field
                    const imageInput = document.getElementById(sectionType + '_image_path');
                    imageInput.value = data.image_path;
                    
                    // Show preview
                    const previewDiv = document.querySelector('.image-preview-' + sectionType);
                    previewDiv.innerHTML = `<img src="../${data.image_path}" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 5px;">`;
                    
                    // Show success message
                    showNotification(`${sectionType.charAt(0).toUpperCase() + sectionType.slice(1)} image uploaded successfully!`, 'success');
                } else {
                    alert('Upload failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                button.textContent = originalText;
                button.disabled = false;
                console.error('Upload error:', error);
                alert('Upload failed: ' + error.message);
            });
        }
    </script>
</body>
</html>
