<?php
require_once '../config/admin_auth_check.php';
require_once '../config/database.php';

$message = '';
$error = '';

// Function to get content from database
function getContent($pdo, $section, $field, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT content FROM portfolio_content WHERE section = ? AND field_name = ?");
        $stmt->execute([$section, $field]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['content'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_html'])) {
    try {
        // Read the original index.html file
        $indexPath = __DIR__ . '/../index.html';
        if (!file_exists($indexPath)) {
            throw new Exception("Original index.html file not found");
        }
        
        $html = file_get_contents($indexPath);
        
        // Get basic content from database
        $welcomeText = getContent($pdo, 'intro', 'welcome_text', 'Welcome to my portfolio');
        $mainTitle = getContent($pdo, 'intro', 'main_title', 'I am Rose Webb,<br>An IT Technician<br>& Aspiring<br>Developer based<br>in South Yorkshire.');
        $aboutDesc = getContent($pdo, 'about', 'about_description', '');
        $cvLink = getContent($pdo, 'about', 'cv_link', 'Files/RWEBB-CV.pdf');
        $worksTitle = getContent($pdo, 'works', 'works_title', 'Scripts, Code Projects & Other Nerdy Stuff');
        $contactTitle = getContent($pdo, 'contact', 'contact_title', 'Get In Touch');
        $contactSubtitle = getContent($pdo, 'contact', 'contact_subtitle', '');
        $email = getContent($pdo, 'contact', 'email', 'rosewebb2810@gmail.com');
        $phone = getContent($pdo, 'contact', 'phone', '+44 7578 777928');
        $linkedinUrl = getContent($pdo, 'social', 'linkedin_url', 'https://www.linkedin.com/in/rose-webb-798014215/');
        $twitterUrl = getContent($pdo, 'social', 'twitter_url', 'https://www.twitter.com/YuukiiRose');
        $githubUrl = getContent($pdo, 'social', 'github_url', 'https://github.com/YuukiRose');
        
        // Generate Skills/Expertise HTML
        $skillsHtml = '';
        $stmt = $pdo->query("SELECT category, skill_name FROM portfolio_skills ORDER BY category, sort_order");
        $skillsByCategory = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $skillsByCategory[$row['category']][] = $row['skill_name'];
        }
        
        $columnWidth = 'lg-4 md-6';
        $columnCount = 0;
        foreach ($skillsByCategory as $category => $skills) {
            if ($columnCount % 3 == 0 && $columnCount > 0) {
                // Add break for responsive layout
            }
            
            $skillsHtml .= "\n                            <div class=\"column {$columnWidth}\">\n";
            $skillsHtml .= "                                <h4>" . htmlspecialchars($category) . "</h4>\n";
            $skillsHtml .= "                                <ul class=\"skills-list\" data-animate-el>\n";
            
            foreach ($skills as $skill) {
                $skillsHtml .= "                                    <li>" . htmlspecialchars($skill) . "</li>\n";
            }
            
            $skillsHtml .= "                                </ul>\n";
            $skillsHtml .= "                            </div>\n";
            $columnCount++;
        }
        
        // Generate Experience Timeline HTML
        $experienceHtml = '';
        try {
            $stmt = $pdo->query("SELECT * FROM portfolio_experience ORDER BY is_present DESC, date_start DESC");
        } catch (PDOException $e) {
            // Fallback to old sorting if new columns don't exist
            $stmt = $pdo->query("SELECT * FROM portfolio_experience ORDER BY sort_order");
        }
        $experienceCount = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $experienceCount++;
            $experienceHtml .= "\n                            <div class=\"timeline__block\">\n";
            $experienceHtml .= "                                <div class=\"timeline__bullet\"></div>\n";
            $experienceHtml .= "                                <div class=\"timeline__header\">\n";
            $experienceHtml .= "                                    <h4 class=\"timeline__title\">" . htmlspecialchars($row['company']) . "</h4>\n";
            $experienceHtml .= "                                    <h5 class=\"timeline__meta\">" . htmlspecialchars($row['position']) . "</h5>\n";
            $experienceHtml .= "                                    <p class=\"timeline__timeframe\">" . htmlspecialchars($row['timeframe']) . "</p>\n";
            $experienceHtml .= "                                </div>\n";
            $experienceHtml .= "                                <div class=\"timeline__desc\">\n";
            $experienceHtml .= "                                    <p>" . htmlspecialchars($row['description']) . "</p>\n";
            $experienceHtml .= "                                </div>\n";
            $experienceHtml .= "                            </div>\n";
        }
        
        // Generate Education Timeline HTML
        $educationHtml = '';
        try {
            $stmt = $pdo->query("SELECT * FROM portfolio_education ORDER BY is_present DESC, date_start DESC");
        } catch (PDOException $e) {
            // Fallback to old sorting if new columns don't exist
            $stmt = $pdo->query("SELECT * FROM portfolio_education ORDER BY sort_order");
        }
        $educationCount = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $educationCount++;
            $educationHtml .= "\n                            <div class=\"timeline__block\">\n";
            $educationHtml .= "                                <div class=\"timeline__bullet\"></div>\n";
            $educationHtml .= "                                <div class=\"timeline__header\">\n";
            $educationHtml .= "                                    <h4 class=\"timeline__title\">" . htmlspecialchars($row['institution']) . "</h4>\n";
            $educationHtml .= "                                    <h5 class=\"timeline__meta\">" . htmlspecialchars($row['qualification']) . "</h5>\n";
            $educationHtml .= "                                    <p class=\"timeline__timeframe\">" . htmlspecialchars($row['timeframe']) . "</p>\n";
            $educationHtml .= "                                </div>\n";
            $educationHtml .= "                                <div class=\"timeline__desc\">\n";
            $educationHtml .= "                                    <p>" . htmlspecialchars($row['description']) . "</p>\n";
            $educationHtml .= "                                </div>\n";
            $educationHtml .= "                            </div>\n";
        }
        
        // Replace content in HTML
        
        // Update welcome text
        $html = preg_replace(
            '/(<div class="text-pretitle with-line">\s*)(.*?)(\s*<\/div>)/s',
            '$1' . htmlspecialchars($welcomeText) . '$3',
            $html
        );
        
        // Update main title
        $html = preg_replace(
            '/(<h2 class="text-huge-title">\s*)(.*?)(\s*<\/h2>)/s',
            '$1' . $mainTitle . '$3',
            $html
        );
        
        // Update about description
        $html = preg_replace(
            '/(<p class="attention-getter"[^>]*>\s*)(.*?)(\s*<\/p>)/s',
            '$1' . htmlspecialchars($aboutDesc) . '$3',
            $html
        );
        
        // Update CV link
        $html = preg_replace(
            '/(<a href=")[^"]*(" class="btn btn--medium u-fullwidth")/s',
            '$1' . htmlspecialchars($cvLink) . '$2',
            $html
        );
        
        // Replace Skills Section
        $skillsPattern = '/(<div class="row">\s*<div class="column lg-4 md-6">.*?<\/div>\s*<\/div>)/s';
        if (preg_match($skillsPattern, $html)) {
            $html = preg_replace(
                $skillsPattern,
                '<div class="row">' . $skillsHtml . '                        </div>',
                $html
            );
        }
        
        // Replace Experience Timeline
        $experiencePattern = '/(<div class="timeline" data-animate-el>\s*)(.*?)(\s*<\/div> <!-- end timeline -->)/s';
        if (preg_match($experiencePattern, $html)) {
            $html = preg_replace(
                $experiencePattern,
                '$1' . $experienceHtml . "\n                        " . '$3',
                $html,
                1  // Only replace first occurrence (experience section)
            );
        }
        
        // Replace Education Timeline
        $educationStart = strpos($html, '<h2 class="text-pretitle" data-animate-el>');
        if ($educationStart !== false) {
            $educationSectionStart = strpos($html, '<div class="timeline" data-animate-el>', $educationStart);
            if ($educationSectionStart !== false) {
                $educationSectionEnd = strpos($html, '</div> <!-- end timeline -->', $educationSectionStart);
                if ($educationSectionEnd !== false) {
                    $beforeEducation = substr($html, 0, $educationSectionStart + strlen('<div class="timeline" data-animate-el>'));
                    $afterEducation = substr($html, $educationSectionEnd);
                    $html = $beforeEducation . $educationHtml . "\n                        " . $afterEducation;
                }
            }
        }
        
        // Update works title
        $html = preg_replace(
            '/Scripts, Code Projects & Other Nerdy Stuff/',
            htmlspecialchars($worksTitle),
            $html
        );
        
        // Update contact title
        $html = preg_replace(
            '/(<h2 class="text-pretitle">\s*Get In Touch\s*<\/h2>)/',
            '<h2 class="text-pretitle">' . htmlspecialchars($contactTitle) . '</h2>',
            $html
        );
        
        // Update contact subtitle
        $html = preg_replace(
            '/(<p class="h1">\s*)(.*?)(shoot me a message\.\s*<\/p>)/s',
            '$1' . htmlspecialchars($contactSubtitle) . '$3',
            $html
        );
        
        // Update email addresses
        $html = preg_replace(
            '/rosewebb2810@gmail\.com/',
            htmlspecialchars($email),
            $html
        );
        
        // Update phone number
        $html = preg_replace(
            '/\+44 7578 777928/',
            htmlspecialchars($phone),
            $html
        );
        
        // Update social media links
        $html = preg_replace(
            '/https:\/\/www\.linkedin\.com\/in\/rose-webb-798014215\//',
            htmlspecialchars($linkedinUrl),
            $html
        );
        
        $html = preg_replace(
            '/https:\/\/www\.twitter\.com\/YuukiiRose/',
            htmlspecialchars($twitterUrl),
            $html
        );
        
        $html = preg_replace(
            '/https:\/\/github\.com\/YuukiRose/',
            htmlspecialchars($githubUrl),
            $html
        );
        
        // Create backup of original file
        $backupPath = __DIR__ . '/../index_backup_' . date('Y-m-d_H-i-s') . '.html';
        copy($indexPath, $backupPath);
        
        // Write updated HTML
        file_put_contents($indexPath, $html);
        
        $message = "HTML file generated successfully! Backup created: " . basename($backupPath);
        
    } catch (Exception $e) {
        $error = "Error generating HTML: " . $e->getMessage();
    }
}

// Get current content for preview
$currentContent = [];
try {
    $stmt = $pdo->query("SELECT section, field_name, content FROM portfolio_content ORDER BY section, field_name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $currentContent[$row['section']][$row['field_name']] = $row['content'];
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
    <title>Generate Portfolio HTML - Admin Panel</title>
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
            max-width: 1000px;
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
        
        .preview-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }
        
        .preview-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .preview-content {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
            max-height: 150px;
            overflow-y: auto;
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
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60, #229954);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            margin-left: 10px;
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
        
        .generate-section {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 30px;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Generate Portfolio HTML</h1>
            <p>Apply your content changes to the live portfolio</p>
        </div>
        
        <div class="content">
            <div class="navigation">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="edit_portfolio_content.php" class="btn btn-warning">📝 Edit Content</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message success">✅ <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="warning-box">
                <strong>⚠️ Important:</strong> This will update your live portfolio HTML file. A backup will be created automatically before making changes.
            </div>
            
            <div class="message" style="background: #e8f5e8; border: 2px solid #28a745; color: #155724;">
                <strong>💡 New Dynamic Portfolio Available!</strong><br>
                Consider switching to the new <a href="../index.php" target="_blank" style="color: #28a745; font-weight: bold;">dynamic PHP portfolio</a> which updates automatically from the database without needing to regenerate HTML files. This provides real-time updates and better performance.
            </div>
            
            <!-- Content Preview -->
            <h2 style="margin-bottom: 20px;">📋 Current Content Preview</h2>
            
            <div class="preview-grid">
                <?php if (!empty($currentContent)): ?>
                    <?php foreach ($currentContent as $section => $fields): ?>
                        <div class="preview-section">
                            <div class="preview-title">📂 <?php echo ucfirst($section); ?> Section</div>
                            <?php foreach ($fields as $field => $content): ?>
                                <div style="margin-bottom: 15px;">
                                    <strong><?php echo ucwords(str_replace('_', ' ', $field)); ?>:</strong>
                                    <div class="preview-content"><?php echo htmlspecialchars(substr($content, 0, 200)) . (strlen($content) > 200 ? '...' : ''); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="preview-section">
                        <div class="preview-title">No Content Found</div>
                        <p>No content has been saved yet. Please use the Content Editor to add content first.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="generate-section">
                <form method="POST" action="">
                    <h3 style="margin-bottom: 15px;">🚀 Generate Updated HTML</h3>
                    <p style="margin-bottom: 20px;">This will apply all your content changes to the main portfolio page.</p>
                    <button type="submit" name="generate_html" class="btn btn-success" 
                            onclick="return confirm('Are you sure you want to update the HTML file? A backup will be created automatically.')">
                        🔧 Generate HTML File
                    </button>
                </form>
                
                <div style="margin-top: 15px;">
                    <a href="../index.php" class="btn" target="_blank">👁️ View Portfolio</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
