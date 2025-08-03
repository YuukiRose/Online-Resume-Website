<?php
require_once '../config/admin_auth_check.php';
require_once '../config/database.php';

$message = '';
$error = '';

// Create portfolio_content table if it doesn't exist
try {
    $createTable = "
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
    $pdo->exec($createTable);
    
    // Insert default content if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_content");
    if ($stmt->fetchColumn() == 0) {
        $defaultContent = [
            // Intro Section
            ['intro', 'welcome_text', 'Welcome to my portfolio', 'text'],
            ['intro', 'main_title', 'I am Rose Webb,<br>An IT Technician<br>& Aspiring<br>Developer based<br>in South Yorkshire.', 'html'],
            
            // About Section
            ['about', 'about_description', 'Driven IT professional with a passion for solving technical challenges, enhancing system efficiency, and continuously learning new technologies. Aspiring to grow in roles that integrate hands-on technical support with cloud services, networking, and programming, while delivering exceptional service and innovative solutions to users and organizations.', 'textarea'],
            ['about', 'cv_link', 'Files/RWEBB-CV.pdf', 'text'],
            
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
    
} catch (PDOException $e) {
    $error = "Database setup error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_content'])) {
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'update_content' && strpos($key, '_') !== false) {
                list($section, $field) = explode('_', $key, 2);
                
                $stmt = $pdo->prepare("
                    INSERT INTO portfolio_content (section, field_name, content, content_type) 
                    VALUES (?, ?, ?, 'text') 
                    ON DUPLICATE KEY UPDATE content = VALUES(content)
                ");
                $stmt->execute([$section, $field, trim($value)]);
            }
        }
        $message = "Portfolio content updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating content: " . $e->getMessage();
    }
}

// Get current content
$content = [];
try {
    $stmt = $pdo->query("SELECT section, field_name, content, content_type FROM portfolio_content ORDER BY section, field_name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $content[$row['section']][$row['field_name']] = $row;
    }
} catch (PDOException $e) {
    $error = "Error loading content: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Content Editor - Admin Panel</title>
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
            max-width: 1200px;
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
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            text-transform: capitalize;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .form-group.large textarea {
            min-height: 120px;
        }
        
        .btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-right: 10px;
        }
        
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
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
        
        .navigation {
            margin-bottom: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .preview-btn {
            background: #28a745;
            margin-left: 10px;
        }
        
        .field-description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        
        .save-section {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Portfolio Content Editor</h1>
            <p>Edit your portfolio content directly from the admin panel</p>
        </div>
        
        <div class="content">
            <div class="navigation">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="../index.php" class="btn preview-btn" target="_blank">👁️ Preview Portfolio</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message success">✅ <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Intro Section -->
                <div class="section">
                    <h2 class="section-title">🏠 Introduction Section</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="intro_welcome_text">Welcome Text</label>
                            <input type="text" id="intro_welcome_text" name="intro_welcome_text" 
                                   value="<?php echo htmlspecialchars($content['intro']['welcome_text']['content'] ?? ''); ?>">
                            <div class="field-description">Text shown above the main title</div>
                        </div>
                        
                        <div class="form-group large">
                            <label for="intro_main_title">Main Title</label>
                            <textarea id="intro_main_title" name="intro_main_title"><?php echo htmlspecialchars($content['intro']['main_title']['content'] ?? ''); ?></textarea>
                            <div class="field-description">Main hero title (HTML allowed for line breaks)</div>
                        </div>
                    </div>
                </div>
                
                <!-- About Section -->
                <div class="section">
                    <h2 class="section-title">👤 About Section</h2>
                    <div class="form-group large">
                        <label for="about_about_description">About Description</label>
                        <textarea id="about_about_description" name="about_about_description"><?php echo htmlspecialchars($content['about']['about_description']['content'] ?? ''); ?></textarea>
                        <div class="field-description">Main about section description</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="about_cv_link">CV Download Link</label>
                        <input type="text" id="about_cv_link" name="about_cv_link" 
                               value="<?php echo htmlspecialchars($content['about']['cv_link']['content'] ?? ''); ?>">
                        <div class="field-description">Path to your CV file (e.g., Files/RWEBB-CV.pdf)</div>
                    </div>
                </div>
                
                <!-- Works Section -->
                <div class="section">
                    <h2 class="section-title">💼 Works Section</h2>
                    <div class="form-group">
                        <label for="works_works_title">Works Section Title</label>
                        <input type="text" id="works_works_title" name="works_works_title" 
                               value="<?php echo htmlspecialchars($content['works']['works_title']['content'] ?? ''); ?>">
                        <div class="field-description">Title for the portfolio/works section</div>
                    </div>
                </div>
                
                <!-- Contact Section -->
                <div class="section">
                    <h2 class="section-title">📞 Contact Section</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="contact_contact_title">Contact Title</label>
                            <input type="text" id="contact_contact_title" name="contact_contact_title" 
                                   value="<?php echo htmlspecialchars($content['contact']['contact_title']['content'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Email Address</label>
                            <input type="email" id="contact_email" name="contact_email" 
                                   value="<?php echo htmlspecialchars($content['contact']['email']['content'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_phone">Phone Number</label>
                            <input type="text" id="contact_phone" name="contact_phone" 
                                   value="<?php echo htmlspecialchars($content['contact']['phone']['content'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group large">
                        <label for="contact_contact_subtitle">Contact Subtitle</label>
                        <textarea id="contact_contact_subtitle" name="contact_contact_subtitle"><?php echo htmlspecialchars($content['contact']['contact_subtitle']['content'] ?? ''); ?></textarea>
                        <div class="field-description">Subtitle text in the contact section</div>
                    </div>
                </div>
                
                <!-- Social Links -->
                <div class="section">
                    <h2 class="section-title">🔗 Social Media Links</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="social_linkedin_url">LinkedIn URL</label>
                            <input type="url" id="social_linkedin_url" name="social_linkedin_url" 
                                   value="<?php echo htmlspecialchars($content['social']['linkedin_url']['content'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="social_twitter_url">Twitter URL</label>
                            <input type="url" id="social_twitter_url" name="social_twitter_url" 
                                   value="<?php echo htmlspecialchars($content['social']['twitter_url']['content'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="social_github_url">GitHub URL</label>
                            <input type="url" id="social_github_url" name="social_github_url" 
                                   value="<?php echo htmlspecialchars($content['social']['github_url']['content'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="save-section">
                    <button type="submit" name="update_content" class="btn">💾 Save All Changes</button>
                    <div style="margin-top: 10px; color: #155724;">
                        <small>Changes will be saved to the database. You'll need to regenerate the HTML file to see changes on the live site.</small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
