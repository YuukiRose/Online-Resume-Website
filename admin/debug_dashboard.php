<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug & Configuration Dashboard - Portfolio Admin</title>
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
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-description {
            color: #7f8c8d;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .tool-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .tool-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #3498db;
        }
        
        .tool-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tool-description {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .tool-link {
            display: inline-block;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .tool-link:hover {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-1px);
        }
        
        .tool-category {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .tool-category.database { background: #e74c3c; }
        .tool-category.security { background: #f39c12; }
        .tool-category.email { background: #27ae60; }
        .tool-category.admin { background: #8e44ad; }
        .tool-category.config { background: #34495e; }
        .tool-category.test { background: #16a085; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .quick-actions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .quick-actions h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .quick-actions .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .quick-btn {
            background: #27ae60;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .quick-btn:hover {
            background: #219a52;
            transform: translateY(-1px);
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .back-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .tools-grid {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛠️ Debug & Configuration Dashboard</h1>
            <p>Comprehensive admin tools for database management, security configuration, and system debugging</p>
        </div>
        
        <div class="content">
            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number">17</div>
                    <div class="stat-label">Debug Tools</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">17</div>
                    <div class="stat-label">Config Files</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Admin Tools</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">47</div>
                    <div class="stat-label">Total Scripts</div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>🚀 Quick Actions</h3>
                <div class="btn-group">
                    <a href="Debug/database_debug.php" class="quick-btn">Database Health Check</a>
                    <a href="../config/test_encrypted_db.php" class="quick-btn">Test Database Connection</a>
                    <a href="../config/generate_keys.php" class="quick-btn">Generate Security Keys</a>
                    <a href="../config/setup_database.php" class="quick-btn">Setup Database</a>
                    <a href="email_test.php" class="quick-btn">Test Email System</a>
                    <a href="../user/login.php" class="quick-btn">Test Login</a>
                </div>
            </div>
            
            <!-- Database Tools -->
            <div class="section">
                <h2 class="section-title">
                    🗄️ Database Management Tools
                </h2>
                <p class="section-description">
                    Tools for database health monitoring, schema management, and data integrity verification.
                </p>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-category database">Database</div>
                        <div class="tool-title">� Comprehensive DB Test</div>
                        <div class="tool-description">Complete database diagnostic with automated repair capabilities. Checks tables, columns, indexes, and portfolio structure. Can fix missing components automatically.</div>
                        <a href="Debug/comprehensive_db_test.php" class="tool-link">Run Full Diagnostic & Repair</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category database">Database</div>
                        <div class="tool-title">🏗️ Database Setup</div>
                        <div class="tool-description">Initial database setup with table creation, portfolio structure, and test data insertion for new installations or complete rebuilds.</div>
                        <a href="../config/setup_database.php" class="tool-link">Setup Database</a>
                    </div>
                </div>
            </div>
            
            <!-- Security & Authentication -->
            <div class="section">
                <h2 class="section-title">
                    🔐 Security & Authentication Tools
                </h2>
                <p class="section-description">
                    Security configuration, encryption management, and authentication system tools.
                </p>
                <div class="tools-grid">
                    
                    <div class="tool-card">
                        <div class="tool-category security">Security</div>
                        <div class="tool-title">� Generate Security Keys</div>
                        <div class="tool-description">Generate cryptographically secure encryption keys for database credentials and system security.</div>
                        <a href="../config/generate_keys.php" class="tool-link">Generate Keys</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category security">Security</div>
                        <div class="tool-title">🧪 Test Encrypted Database</div>
                        <div class="tool-description">Test encrypted database configuration and verify credential decryption is working properly.</div>
                        <a href="../config/test_encrypted_db.php" class="tool-link">Test Encryption</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category security">Security</div>
                        <div class="tool-title">🔒 Encrypt Password</div>
                        <div class="tool-description">Utility for encrypting passwords and sensitive data using the secure encryption system.</div>
                        <a href="Debug/encrypt_password.php" class="tool-link">Encrypt Data</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category security">Security</div>
                        <div class="tool-title">🔄 Update Encrypted DB</div>
                        <div class="tool-description">Update database configuration with new encrypted credentials for enhanced security.</div>
                        <a href="../config/update_encrypted_db.php" class="tool-link">Update Encryption</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category security">Security</div>
                        <div class="tool-title">🛡️ Test Security Config</div>
                        <div class="tool-description">Comprehensive security configuration testing and validation tool.</div>
                        <a href="../config/test_security.php" class="tool-link">Test Security</a>
                    </div>
                </div>
            </div>
            
            <!-- Password Reset & Session Management -->
            <div class="section">
                <h2 class="section-title">
                    🔄 Password Reset & Session Tools
                </h2>
                <p class="section-description">
                    Tools for managing password reset workflows, session debugging, and user authentication flows.
                </p>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-category admin">Admin</div>
                        <div class="tool-title">🔧 Fix Password Reset</div>
                        <div class="tool-description">Automated tool to fix common password reset issues and repair broken workflows.</div>
                        <a href="Debug/fix_password_reset.php" class="tool-link">Fix Reset</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category admin">Admin</div>
                        <div class="tool-title">🔄 Debug Reset Session</div>
                        <div class="tool-description">Debug session management and troubleshoot reset session-related issues.</div>
                        <a href="Debug/debug_reset_session.php" class="tool-link">Debug Session</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category admin">Admin</div>
                        <div class="tool-title">👤 Debug User Sessions</div>
                        <div class="tool-description">Monitor and debug user session management and authentication states.</div>
                        <a href="Debug/debug_session.php" class="tool-link">Debug Sessions</a>
                    </div>
                </div>
            </div>
            
            <!-- Email & Communication -->
            <div class="section">
                <h2 class="section-title">
                    📧 Email & Communication Tools
                </h2>
                <p class="section-description">
                    Email system testing, SMTP configuration, and communication workflow verification.
                </p>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-category email">Email</div>
                        <div class="tool-title">📨 Email System Test</div>
                        <div class="tool-description">Comprehensive email system testing including SMTP configuration and delivery verification.</div>
                        <a href="email_test.php" class="tool-link">Test Email</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category email">Email</div>
                        <div class="tool-title">� Email Encryption Setup</div>
                        <div class="tool-description">Encrypt SMTP email configuration using secure keys and save to E.env file. Replaces plaintext email credentials with military-grade AES-256-GCM encryption.</div>
                        <a href="Debug/email_encryption_setup.php" class="tool-link">Setup Email Encryption</a>
                    </div>
                </div>
            </div>
            
            <!-- User Management & Testing -->
            <div class="section">
                <h2 class="section-title">
                    👥 User Management & Testing Tools
                </h2>
                <p class="section-description">
                    User account management, dashboard testing, and user experience verification tools.
                </p>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-category admin">Admin</div>
                        <div class="tool-title">👤 Setup Admin User</div>
                        <div class="tool-description">Create and configure admin user accounts with proper permissions and access levels.</div>
                        <a href="Debug/setup_admin.php" class="tool-link">Setup Admin</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category test">Test</div>
                        <div class="tool-title">🐱 HTTP Cat Demo</div>
                        <div class="tool-description">Interactive demo of HTTP status codes with cute cat images from http.cat API.</div>
                        <a href="Debug/http_cat_demo.php" class="tool-link">View HTTP Cats</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category test">Test</div>
                        <div class="tool-title">🛠️ HTTP Cat Integration</div>
                        <div class="tool-description">Learn how to integrate HTTP Cats into your application's error handling with examples and best practices.</div>
                        <a href="Debug/http_cat_integration.php" class="tool-link">Integration Guide</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category test">Test</div>
                        <div class="tool-title">🔧 HTTP Cat Error Tester</div>
                        <div class="tool-description">Test and verify that custom error pages with HTTP Cats are working correctly for forbidden files and missing pages.</div>
                        <a href="Debug/http_cat_error_tester.php" class="tool-link">Test Error Pages</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category admin">Admin</div>
                        <div class="tool-title">👥 Manage Users</div>
                        <div class="tool-description">Administrative interface for user account management, permissions, and account status.</div>
                        <a href="manage_users.php" class="tool-link">Manage Users</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category admin">Admin</div>
                        <div class="tool-title">➕ Create User</div>
                        <div class="tool-description">Create new user accounts with custom permissions and initial configuration.</div>
                        <a href="create_user.php" class="tool-link">Create User</a>
                    </div>
                </div>
            </div>
            
            <!-- Configuration & Integration -->
            <div class="section">
                <h2 class="section-title">
                    ⚙️ Configuration & Integration Tools
                </h2>
                <p class="section-description">
                    System configuration tools, third-party integrations, and feature enhancements.
                </p>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-category config">Config</div>
                        <div class="tool-title">📐 Standardize Database</div>
                        <div class="tool-description">Standardize database structure and ensure consistency across all tables and relationships.</div>
                        <a href="Debug/standardize_database.php" class="tool-link">Standardize DB</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category test">Test</div>
                        <div class="tool-title">🔍 PHP Syntax Checker</div>
                        <div class="tool-description">Comprehensive syntax validation tool that checks all PHP files in the workspace for syntax errors and provides detailed error reports.</div>
                        <a href="Debug/syntax_checker.php" class="tool-link">Check PHP Syntax</a>
                    </div>
                </div>
            </div>
            
            <!-- Content Management -->
            <div class="section">
                <h2 class="section-title">
                    📝 Content Management Tools
                </h2>
                <p class="section-description">
                    Edit and manage your portfolio content directly from the admin panel without touching HTML files.
                </p>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-category config">Content</div>
                        <div class="tool-title">🎯 Portfolio Content Editor</div>
                        <div class="tool-description">Complete portfolio management including basic content, skills & expertise, experience timeline, and education. All-in-one content management system.</div>
                        <a href="comprehensive_portfolio_editor.php" class="tool-link">Edit All Content</a>
                    </div>
                </div>
            </div>
            
            <!-- Main Application Links -->
            <div class="section">
                <h2 class="section-title">
                    🌐 Application Access
                </h2>
                <p class="section-description">
                    Direct access to main application areas and user-facing interfaces.
                </p>
                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-category test">App</div>
                        <div class="tool-title">🏠 Main Website</div>
                        <div class="tool-description">Access the main website homepage and public-facing content.</div>
                        <a href="../index.html" class="tool-link">View Website</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category test">App</div>
                        <div class="tool-title">🔐 User Login</div>
                        <div class="tool-description">Test user authentication and login functionality.</div>
                        <a href="../user/login.php" class="tool-link">User Login</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category test">App</div>
                        <div class="tool-title">📝 User Registration</div>
                        <div class="tool-description">Test user registration process including email verification.</div>
                        <a href="../user/register.php" class="tool-link">Register User</a>
                    </div>
                    
                    <div class="tool-card">
                        <div class="tool-category admin">Admin</div>
                        <div class="tool-title">👑 Admin Dashboard</div>
                        <div class="tool-description">Access the main admin dashboard with administrative controls.</div>
                        <a href="dashboard.php" class="tool-link">Admin Dashboard</a>
                    </div>
                </div>
            </div>
            
            <div class="back-link">
                <a href="dashboard.php">← Back to Admin Dashboard</a> | 
                <a href="../index.php">← Back to Main Site</a>
            </div>
        </div>
    </div>
</body>
</html>
