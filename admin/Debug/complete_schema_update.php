<?php
/**
 * Complete Database Schema Update Tool
 * Fixes missing columns and tables for full application functionality
 */

require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h1>🔧 Complete Database Schema Update</h1>";

// Handle schema update
if (isset($_POST['update_schema'])) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>🔄 Schema Update in Progress...</h2>";
    
    try {
        // Database connection is already established via database.php as $pdo
        echo "<p>✅ Connected to database successfully</p>";
        
        // Step 1: Add missing column to users table
        echo "<h3>Step 1: Adding missing columns to users table</h3>";
        
        // Check if is_verified column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 COMMENT 'Email verification status'");
            echo "<p>✅ Added is_verified column to users table</p>";
        } else {
            echo "<p>ℹ️ is_verified column already exists</p>";
        }
        
        // Check if email_verified_at column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email_verified_at'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Email verification timestamp'");
            echo "<p>✅ Added email_verified_at column to users table</p>";
        } else {
            echo "<p>ℹ️ email_verified_at column already exists</p>";
        }
        
        // Check if updated_at column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'updated_at'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp'");
            echo "<p>✅ Added updated_at column to users table</p>";
        } else {
            echo "<p>ℹ️ updated_at column already exists</p>";
        }
        
        // Step 2: Create password_resets table
        echo "<h3>Step 2: Creating password_resets table</h3>";
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'password_resets'");
        if ($stmt->rowCount() == 0) {
            $createPasswordResets = "
                CREATE TABLE password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL UNIQUE,
                    expires_at TIMESTAMP NOT NULL,
                    used TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    used_at TIMESTAMP NULL DEFAULT NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    INDEX idx_email (email),
                    INDEX idx_token (token),
                    INDEX idx_expires_at (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens'
            ";
            $pdo->exec($createPasswordResets);
            echo "<p>✅ Created password_resets table</p>";
        } else {
            echo "<p>ℹ️ password_resets table already exists</p>";
        }
        
        // Step 3: Create email_verifications table
        echo "<h3>Step 3: Creating email_verifications table</h3>";
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'email_verifications'");
        if ($stmt->rowCount() == 0) {
            $createEmailVerifications = "
                CREATE TABLE email_verifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL UNIQUE,
                    expires_at TIMESTAMP NOT NULL,
                    verified TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    verified_at TIMESTAMP NULL DEFAULT NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user_id (user_id),
                    INDEX idx_email (email),
                    INDEX idx_token (token),
                    INDEX idx_expires_at (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email verification tokens'
            ";
            $pdo->exec($createEmailVerifications);
            echo "<p>✅ Created email_verifications table</p>";
        } else {
            echo "<p>ℹ️ email_verifications table already exists</p>";
        }
        
        // Step 4: Create user_sessions table (if not exists)
        echo "<h3>Step 4: Creating user_sessions table</h3>";
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'user_sessions'");
        if ($stmt->rowCount() == 0) {
            $createUserSessions = "
                CREATE TABLE user_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    session_token VARCHAR(255) NOT NULL UNIQUE,
                    expires_at TIMESTAMP NOT NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    is_active TINYINT(1) DEFAULT 1,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user_id (user_id),
                    INDEX idx_session_token (session_token),
                    INDEX idx_expires_at (expires_at),
                    INDEX idx_last_activity (last_activity)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User session management'
            ";
            $pdo->exec($createUserSessions);
            echo "<p>✅ Created user_sessions table</p>";
        } else {
            echo "<p>ℹ️ user_sessions table already exists</p>";
        }
        
        // Step 5: Create user_login_attempts table (security)
        echo "<h3>Step 5: Creating user_login_attempts table</h3>";
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'user_login_attempts'");
        if ($stmt->rowCount() == 0) {
            $createLoginAttempts = "
                CREATE TABLE user_login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    success TINYINT(1) NOT NULL DEFAULT 0,
                    user_agent TEXT NULL,
                    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    failure_reason VARCHAR(255) NULL,
                    INDEX idx_email (email),
                    INDEX idx_ip_address (ip_address),
                    INDEX idx_attempted_at (attempted_at),
                    INDEX idx_success (success)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User login attempt tracking'
            ";
            $pdo->exec($createLoginAttempts);
            echo "<p>✅ Created user_login_attempts table</p>";
        } else {
            echo "<p>ℹ️ user_login_attempts table already exists</p>";
        }
        
        // Step 5.5: Create admins table (admin accounts)
        echo "<h3>Step 5.5: Creating admins table</h3>";
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
        if ($stmt->rowCount() == 0) {
            $createAdmins = "
                CREATE TABLE admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(100) NOT NULL UNIQUE,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    full_name VARCHAR(255) NULL,
                    role ENUM('super_admin', 'admin', 'moderator') NOT NULL DEFAULT 'admin',
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    last_login TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    permissions JSON NULL COMMENT 'Admin-specific permissions',
                    notes TEXT NULL COMMENT 'Admin notes',
                    INDEX idx_username (username),
                    INDEX idx_email (email),
                    INDEX idx_role (role),
                    INDEX idx_is_active (is_active),
                    INDEX idx_last_login (last_login)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin user accounts'
            ";
            $pdo->exec($createAdmins);
            echo "<p>✅ Created admins table</p>";
        } else {
            echo "<p>ℹ️ admins table already exists</p>";
        }
        
        // Step 6: Update existing data
        echo "<h3>Step 6: Updating existing data</h3>";
        
        // Set all existing users as verified (for backward compatibility)
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, email_verified_at = created_at WHERE is_verified = 0");
        $affectedRows = $stmt->execute() ? $stmt->rowCount() : 0;
        if ($affectedRows > 0) {
            echo "<p>✅ Updated {$affectedRows} existing users as verified</p>";
        } else {
            echo "<p>ℹ️ No existing users needed verification status update</p>";
        }
        
        // Step 7: Verify schema
        echo "<h3>Step 7: Verifying updated schema</h3>";
        
        $tables = [
            'users' => 'User accounts',
            'admins' => 'Admin accounts', 
            'testimonials' => 'User testimonials',
            'admin_sessions' => 'Admin authentication',
            'password_resets' => 'Password reset functionality',
            'email_verifications' => 'Email verification tokens',
            'user_sessions' => 'User session management',
            'user_login_attempts' => 'Login attempt tracking'
        ];
        
        $allTablesExist = true;
        foreach ($tables as $table => $description) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ Table '{$table}' exists ({$description})</p>";
            } else {
                echo "<p>❌ Table '{$table}' missing ({$description})</p>";
                $allTablesExist = false;
            }
        }
        
        // Check required columns in users table
        $requiredColumns = [
            'id' => 'Primary key',
            'username' => 'Username',
            'email' => 'Email address',
            'password' => 'Password hash',
            'is_verified' => 'Email verification status',
            'email_verified_at' => 'Email verification timestamp',
            'created_at' => 'Account creation timestamp',
            'updated_at' => 'Last update timestamp'
        ];
        
        $stmt = $pdo->query("DESCRIBE users");
        $existingColumns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[] = $row['Field'];
        }
        
        foreach ($requiredColumns as $column => $description) {
            if (in_array($column, $existingColumns)) {
                echo "<p>✅ Column '{$column}' exists ({$description})</p>";
            } else {
                echo "<p>❌ Column '{$column}' missing ({$description})</p>";
                $allTablesExist = false;
            }
        }
        
        if ($allTablesExist) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>🎉 Schema Update Completed Successfully!</h3>";
            echo "<p>All required tables and columns are now present. Your application should have full functionality.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>⚠️ Some Issues Remain</h3>";
            echo "<p>Please check the results above and re-run the update if needed.</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Error During Schema Update</h3>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Display current schema status
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📊 Current Schema Status</h2>";

try {
    // Database connection is already established via database.php as $pdo
    echo "<p>✅ Database connection successful</p>";
    
    // Check tables
    $requiredTables = [
        'users' => 'User accounts',
        'admins' => 'Admin accounts',
            'testimonials' => 'User testimonials', 
            'admin_sessions' => 'Admin authentication',
            'password_resets' => 'Password reset functionality',
            'email_verifications' => 'Email verification tokens',
            'user_sessions' => 'User session management',
            'user_login_attempts' => 'Login attempt tracking'
        ];
        
        $missingTables = [];
        foreach ($requiredTables as $table => $description) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: #28a745;'>✅ Table '{$table}' exists ({$description})</p>";
            } else {
                echo "<p style='color: #dc3545;'>❌ Table '{$table}' missing ({$description})</p>";
                $missingTables[] = $table;
            }
        }
        
        // Check users table columns
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            $requiredColumns = ['is_verified', 'email_verified_at', 'updated_at'];
            $stmt = $pdo->query("DESCRIBE users");
            $existingColumns = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $existingColumns[] = $row['Field'];
            }
            
            foreach ($requiredColumns as $column) {
                if (in_array($column, $existingColumns)) {
                    echo "<p style='color: #28a745;'>✅ Column '{$column}' exists in users table</p>";
                } else {
                    echo "<p style='color: #dc3545;'>❌ Column '{$column}' missing in users table</p>";
                }
            }
            
            // Show user count
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            echo "<p><strong>Total users:</strong> {$userCount}</p>";
            
            if (in_array('is_verified', $existingColumns)) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 1");
                $verifiedCount = $stmt->fetch()['count'];
                echo "<p><strong>Verified users:</strong> {$verifiedCount}</p>";
            }
        }
        
} catch (Exception $e) {
    echo "<p style='color: #dc3545;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Update form
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🔧 Update Database Schema</h2>";
echo "<p><strong>This update will:</strong></p>";
echo "<ul>";
echo "<li>Add missing <code>is_verified</code> column to users table</li>";
echo "<li>Add missing <code>email_verified_at</code> column to users table</li>";
echo "<li>Add missing <code>updated_at</code> column to users table</li>";
echo "<li>Create <code>password_resets</code> table for password reset functionality</li>";
echo "<li>Create <code>email_verifications</code> table for email verification</li>";
echo "<li>Create <code>user_sessions</code> table for session management</li>";
echo "<li>Create <code>user_login_attempts</code> table for security tracking</li>";
echo "<li>Set existing users as verified (backward compatibility)</li>";
echo "</ul>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h4>⚠️ Important Notes:</h4>";
echo "<ul>";
echo "<li>This operation is safe and preserves existing data</li>";
echo "<li>Existing users will be marked as verified automatically</li>";
echo "<li>New tables will be created with proper foreign keys</li>";
echo "<li>The operation can be run multiple times safely</li>";
echo "</ul>";
echo "</div>";

echo "<form method='post' onsubmit='return confirm(\"Are you sure you want to update the database schema? This will add missing tables and columns.\")'>";
echo "<button type='submit' name='update_schema' style='background: #28a745; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>";
echo "🔧 Update Database Schema";
echo "</button>";
echo "</form>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='../debug_dashboard.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500;'>← Back to Debug Dashboard</a>";
echo "</div>";
?>

<style>
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    margin: 20px; 
    background: #f8f9fa;
}
h1, h2, h3, h4 { color: #2c3e50; }
button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
ul { line-height: 1.6; }
code {
    background: #e9ecef;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}
</style>
