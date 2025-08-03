<?php
/**
 * Portfolio - Fresh Installation Tool
 * Complete setup wizard for new installations
 * Generates encryption keys, configures database, creates admin account
 */

require_once 'config/SecureKeyManager.php';

// Installation status tracking
session_start();

class InstallationManager {
    private $installationComplete = false;
    private $errors = [];
    private $status = [];
    
    public function __construct() {
        // Check if already installed
        if ($this->isAlreadyInstalled()) {
            $this->errors[] = "Installation already completed. Remove secure files to reinstall.";
        }
    }
    
    private function isAlreadyInstalled() {
        $keyFiles = [
            __DIR__ . '/secure/K.env',
            __DIR__ . '/secure/D.env',
            __DIR__ . '/secure/E.env',
            __DIR__ . '/secure/keys.json'
        ];
        
        foreach ($keyFiles as $file) {
            if (file_exists($file)) {
                return true;
            }
        }
        return false;
    }
    
    public function processInstallation($data) {
        try {
            // Step 1: Generate Encryption Keys
            $this->status[] = "🔐 Step 1: Generating encryption keys...";
            $keyManager = new SecureKeyManager();
            $newKeys = $keyManager->generateNewKeys();
            $this->status[] = "✅ Encryption keys generated successfully";
            
            // Step 2: Create secure directory and key files
            $this->status[] = "📁 Step 2: Creating secure directories...";
            $this->createSecureDirectories();
            $this->createKeyFiles($newKeys);
            $this->status[] = "✅ Key files created and secured";
            
            // Step 3: Encrypt and save database configuration
            $this->status[] = "🗄️ Step 3: Configuring database connection...";
            $this->createDatabaseConfig($data, $newKeys, $keyManager);
            $this->status[] = "✅ Database configuration encrypted and saved";
            
            // Step 4: Test database connection
            $this->status[] = "🔌 Step 4: Testing database connection...";
            $this->testDatabaseConnection($data);
            $this->status[] = "✅ Database connection verified";
            
            // Step 5: Create database tables
            $this->status[] = "🏗️ Step 5: Creating database tables...";
            $this->createDatabaseTables($data);
            $this->status[] = "✅ Database tables created";
            
            // Step 6: Create admin account
            $this->status[] = "👤 Step 6: Creating admin account...";
            $this->createAdminAccount($data, $newKeys, $keyManager);
            $this->status[] = "✅ Admin account created successfully";
            
            // Step 7: Set up email configuration if provided
            if (!empty($data['smtp_host'])) {
                $this->status[] = "📧 Step 7: Configuring email settings...";
                $this->setupEmailConfiguration($data, $newKeys, $keyManager);
                $this->status[] = "✅ Email configuration encrypted and saved";
            } else {
                $this->status[] = "📧 Step 7: Email configuration skipped (can be set up later)";
            }
            
            // Step 8: Final security setup
            $this->status[] = "🛡️ Step 8: Applying security configurations...";
            $this->applySecuritySettings();
            $this->status[] = "✅ Security settings applied";
            
            $this->installationComplete = true;
            $this->status[] = "🎉 Installation completed successfully!";
            
        } catch (Exception $e) {
            $this->errors[] = "Installation failed: " . $e->getMessage();
            throw $e;
        }
    }
    
    private function createSecureDirectories() {
        $directories = [
            __DIR__ . '/secure/',
            __DIR__ . '/config/',
            __DIR__ . '/logs/',
            __DIR__ . '/uploads/'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Create .htaccess files for security
        $htaccessContent = "Order Deny,Allow\nDeny from all";
        file_put_contents(__DIR__ . '/secure/.htaccess', $htaccessContent);
        file_put_contents(__DIR__ . '/config/.htaccess', $htaccessContent);
    }
    
    private function createKeyFiles($keys) {
        // Create K.env file
        $kenvPath = __DIR__ . '/secure/K.env';
        file_put_contents($kenvPath, json_encode($keys, JSON_PRETTY_PRINT));
        
        // Create keys.json file (for compatibility)
        $keysJsonPath = __DIR__ . '/secure/keys.json';
        file_put_contents($keysJsonPath, json_encode($keys, JSON_PRETTY_PRINT));
        
        // Create config keys.json (for backward compatibility)
        $configKeysPath = __DIR__ . '/config/keys.json';
        file_put_contents($configKeysPath, json_encode($keys, JSON_PRETTY_PRINT));
    }
    
    private function createDatabaseConfig($data, $keys, $keyManager) {
        // Encrypt database credentials
        $encryptedDbUser = $keyManager->encryptData($data['db_user'], $keys['encryption_key']);
        $encryptedDbPassword = $keyManager->encryptData($data['db_password'], $keys['encryption_key']);
        
        // Create D.env file content
        $envContent = "# Database Configuration - Fresh Installation\n";
        $envContent .= "# Generated on " . date('Y-m-d H:i:s') . "\n";
        $envContent .= "# D.env - Database Environment File with Encrypted Credentials\n\n";
        $envContent .= "DB_HOST=" . $data['db_host'] . "\n";
        $envContent .= "DB_NAME=" . $data['db_name'] . "\n";
        $envContent .= "DB_USER_ENCRYPTED={$encryptedDbUser}\n";
        $envContent .= "DB_PASSWORD_ENCRYPTED={$encryptedDbPassword}\n";
        $envContent .= "\n# Application Settings\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "\n# Security Settings\n";
        $envContent .= "ENCRYPTION_ENABLED=true\n";
        $envContent .= "SESSION_SECURE=true\n";
        $envContent .= "\n# Installation Settings\n";
        $envContent .= "INSTALLATION_DATE=" . date('Y-m-d H:i:s') . "\n";
        $envContent .= "INSTALLER_VERSION=1.0\n";
        
        // Save D.env file
        $envPath = __DIR__ . '/secure/D.env';
        file_put_contents($envPath, $envContent);
    }
    
    private function testDatabaseConnection($data) {
        try {
            $dsn = "mysql:host={$data['db_host']};dbname={$data['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $data['db_user'], $data['db_password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function createDatabaseTables($data) {
        $pdo = $this->testDatabaseConnection($data);
        
        // Portfolio content table
        $sql = "CREATE TABLE IF NOT EXISTS portfolio_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_type VARCHAR(50) NOT NULL,
            title VARCHAR(255),
            content TEXT,
            image_url VARCHAR(500),
            order_position INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Portfolio works table
        $sql = "CREATE TABLE IF NOT EXISTS portfolio_works (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            image_url VARCHAR(500),
            link_url VARCHAR(500),
            technologies JSON,
            featured BOOLEAN DEFAULT FALSE,
            order_position INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(500) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Admins table
        $sql = "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(500) NOT NULL,
            full_name VARCHAR(200),
            role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
            status ENUM('active', 'inactive') DEFAULT 'active',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Testimonials table
        $sql = "CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            name VARCHAR(200) NOT NULL,
            email VARCHAR(255),
            company VARCHAR(200),
            position VARCHAR(200),
            testimonial TEXT NOT NULL,
            rating INT DEFAULT 5,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        $pdo->exec($sql);
        
        // Contact submissions table
        $sql = "CREATE TABLE IF NOT EXISTS contact_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(300),
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
    }
    
    private function createAdminAccount($data, $keys, $keyManager) {
        $pdo = $this->testDatabaseConnection($data);
        
        // Encrypt admin password
        $encryptedPassword = 'ENC:' . $keyManager->encryptData($data['admin_password'], $keys['encryption_key']);
        
        // Insert admin account
        $sql = "INSERT INTO admins (username, email, password, full_name, role, status) 
                VALUES (?, ?, ?, ?, 'super_admin', 'active')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['admin_username'],
            $data['admin_email'],
            $encryptedPassword,
            $data['admin_full_name'] ?? $data['admin_username']
        ]);
    }
    
    private function setupEmailConfiguration($data, $keys, $keyManager) {
        if (empty($data['smtp_host'])) return;
        
        $emailConfig = [
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => $data['smtp_port'] ?? '465',
            'smtp_username' => $data['smtp_username'],
            'smtp_password' => $data['smtp_password'],
            'from_email' => $data['from_email'],
            'from_name' => $data['from_name'] ?? 'Portfolio',
            'encryption' => $data['smtp_encryption'] ?? 'ssl'
        ];
        
        $encryptedConfig = [];
        foreach ($emailConfig as $key => $value) {
            if (!empty($value)) {
                $encryptedConfig[$key] = $keyManager->encryptData($value, $keys['encryption_key']);
            }
        }
        
        $envContent = "# Encrypted Email Configuration - Fresh Installation\n";
        $envContent .= "# Generated on " . date('Y-m-d H:i:s') . "\n";
        $envContent .= "# DO NOT EDIT THIS FILE MANUALLY\n\n";
        
        foreach ($encryptedConfig as $key => $encryptedValue) {
            $envContent .= strtoupper($key) . "=" . $encryptedValue . "\n";
        }
        
        $envPath = __DIR__ . '/secure/E.env';
        file_put_contents($envPath, $envContent);
    }
    
    private function applySecuritySettings() {
        // Create .htaccess for root if it doesn't exist
        $htaccessPath = __DIR__ . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Security Headers\n";
            $htaccessContent .= "Header always set X-Content-Type-Options nosniff\n";
            $htaccessContent .= "Header always set X-Frame-Options DENY\n";
            $htaccessContent .= "Header always set X-XSS-Protection \"1; mode=block\"\n\n";
            $htaccessContent .= "# Hide sensitive files\n";
            $htaccessContent .= "<FilesMatch \"\\.(env|json|log)$\">\n";
            $htaccessContent .= "    Require all denied\n";
            $htaccessContent .= "</FilesMatch>\n";
            
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function isComplete() {
        return $this->installationComplete;
    }
}

// Process installation
$installer = new InstallationManager();
$showForm = true;
$installationStatus = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    try {
        $installer->processInstallation($_POST);
        $installationStatus = $installer->getStatus();
        $showForm = false;
    } catch (Exception $e) {
        $installationStatus = array_merge($installer->getStatus(), $installer->getErrors());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Portfolio - Installation Wizard</title>
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
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2C3E50 0%, #3498DB 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            font-weight: 300;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content {
            padding: 40px;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border-left: 5px solid #3498DB;
        }
        
        .form-section h3 {
            color: #2C3E50;
            margin-bottom: 20px;
            font-size: 1.4em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2C3E50;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498DB;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-group small {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .btn {
            background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }
        
        .status-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }
        
        .status-item {
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
            font-family: monospace;
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .next-steps {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
        }
        
        .next-steps h3 {
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .next-steps a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin: 5px 10px 5px 0;
            transition: all 0.3s ease;
        }
        
        .next-steps a:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }
        
        .optional {
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Portfolio</h1>
            <p>Professional Portfolio Installation Wizard</p>
        </div>
        
        <div class="content">
            <?php if (!empty($installer->getErrors())): ?>
                <div class="error">
                    <h4>❌ Installation Errors</h4>
                    <?php foreach ($installer->getErrors() as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($installationStatus) && $installer->isComplete()): ?>
                <div class="success">
                    <h3>🎉 Installation Completed Successfully!</h3>
                    <p>Your Portfolio has been installed and configured.</p>
                </div>
                
                <div class="status-container">
                    <h3>📋 Installation Process</h3>
                    <?php foreach ($installationStatus as $status): ?>
                        <div class="status-item"><?php echo htmlspecialchars($status); ?></div>
                    <?php endforeach; ?>
                </div>
                
                <div class="next-steps">
                    <h3>🎯 Next Steps</h3>
                    <p>Your portfolio is now ready! Here's what you can do next:</p>
                    <br>
                    <a href="index.php">🏠 View Your Portfolio</a>
                    <a href="admin/">⚙️ Admin Dashboard</a>
                    <a href="admin/debug_dashboard.php">🔧 Debug Tools</a>
                </div>
                
                <div class="warning">
                    <h4>🔐 Important Security Notes</h4>
                    <ul>
                        <li>Delete or rename this installer.php file for security</li>
                        <li>Your admin credentials are encrypted and stored securely</li>
                        <li>All sensitive configuration files are protected from web access</li>
                        <li>Change your admin password after first login if needed</li>
                    </ul>
                </div>
                
            <?php elseif (!empty($installationStatus)): ?>
                <div class="error">
                    <h3>❌ Installation Failed</h3>
                    <div class="status-container">
                        <?php foreach ($installationStatus as $status): ?>
                            <div class="status-item"><?php echo htmlspecialchars($status); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($showForm && empty($installer->getErrors())): ?>
                <div class="info">
                    <h4>🛠️ Installation Requirements</h4>
                    <p>This installer will set up your portfolio with encrypted security, database configuration, and admin account. 
                    Make sure you have your database details and SMTP settings (optional) ready.</p>
                </div>
                
                <form method="POST">
                    <!-- Database Configuration -->
                    <div class="form-section">
                        <h3>🗄️ Database Configuration</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="db_host">Database Host *</label>
                                <input type="text" id="db_host" name="db_host" value="localhost" required>
                                <small>Usually 'localhost' or your database server IP</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="db_name">Database Name *</label>
                                <input type="text" id="db_name" name="db_name" required>
                                <small>The name of your MySQL database</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="db_user">Database Username *</label>
                                <input type="text" id="db_user" name="db_user" required>
                                <small>MySQL username with full privileges</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="db_password">Database Password *</label>
                                <input type="password" id="db_password" name="db_password" required>
                                <small>Password for the database user</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Admin Account -->
                    <div class="form-section">
                        <h3>👤 Admin Account Setup</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="admin_username">Admin Username *</label>
                                <input type="text" id="admin_username" name="admin_username" required>
                                <small>Your admin login username</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_email">Admin Email *</label>
                                <input type="email" id="admin_email" name="admin_email" required>
                                <small>Your admin email address</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_password">Admin Password *</label>
                                <input type="password" id="admin_password" name="admin_password" required minlength="8">
                                <small>Strong password (minimum 8 characters)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_full_name">Full Name</label>
                                <input type="text" id="admin_full_name" name="admin_full_name">
                                <small>Your full name for the admin profile</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Configuration (Optional) -->
                    <div class="form-section optional">
                        <h3>📧 Email Configuration (Optional)</h3>
                        <p style="margin-bottom: 20px; color: #6c757d;">You can skip this and configure email settings later through the admin panel.</p>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="smtp_host">SMTP Host</label>
                                <input type="text" id="smtp_host" name="smtp_host" placeholder="smtp.gmail.com">
                                <small>Your email provider's SMTP server</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_port">SMTP Port</label>
                                <input type="number" id="smtp_port" name="smtp_port" value="465">
                                <small>Usually 465 (SSL) or 587 (TLS)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_username">SMTP Username</label>
                                <input type="text" id="smtp_username" name="smtp_username">
                                <small>Your email address or SMTP username</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_password">SMTP Password</label>
                                <input type="password" id="smtp_password" name="smtp_password">
                                <small>Your email password or app-specific password</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="from_email">From Email</label>
                                <input type="email" id="from_email" name="from_email">
                                <small>Email address for outgoing messages</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="from_name">From Name</label>
                                <input type="text" id="from_name" name="from_name" value="Portfolio">
                                <small>Name that appears in sent emails</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_encryption">Encryption</label>
                                <select id="smtp_encryption" name="smtp_encryption">
                                    <option value="ssl">SSL</option>
                                    <option value="tls">TLS</option>
                                    <option value="none">None</option>
                                </select>
                                <small>SSL for port 465, TLS for port 587</small>
                            </div>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 40px;">
                        <button type="submit" name="install" class="btn">
                            🚀 Install Portfolio
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
