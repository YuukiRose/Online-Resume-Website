<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

// Enhanced Comprehensive Database Test with Auto-Repair Functionality
echo "<h1>🔍 Comprehensive Database Diagnostic & Repair Tool</h1>";
echo "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Auto-Repair Mode:</strong> Missing tables and columns will be automatically created</p>";

$total_tests = 0;
$passed_tests = 0;
$warnings = 0;
$failed_tests = 0;
$repairs_made = 0;
$issues = [];

// Function to create missing table
function createTable($pdo, $tableName, $structure) {
    global $repairs_made;
    try {
        $pdo->exec($structure);
        echo "<p style='color: orange;'>🔧 AUTO-REPAIR: Created missing table '$tableName'</p>";
        $repairs_made++;
        return true;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Failed to create table '$tableName': " . $e->getMessage() . "</p>";
        return false;
    }
}

// Function to add missing column
function addColumn($pdo, $tableName, $columnName, $columnDef) {
    global $repairs_made;
    try {
        $pdo->exec("ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnDef");
        echo "<p style='color: orange;'>🔧 AUTO-REPAIR: Added missing column '$columnName' to '$tableName'</p>";
        $repairs_made++;
        return true;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Failed to add column '$columnName' to '$tableName': " . $e->getMessage() . "</p>";
        return false;
    }
}

// Portfolio Table Structures
$portfolio_tables = [
    'portfolio_works' => [
        'structure' => "CREATE TABLE `portfolio_works` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `image_url` varchar(500),
            `project_url` varchar(500),
            `technologies` text,
            `category` varchar(100),
            `featured` tinyint(1) DEFAULT 0,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'columns' => [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'title' => 'varchar(255) NOT NULL',
            'description' => 'text',
            'image_url' => 'varchar(500)',
            'project_url' => 'varchar(500)',
            'technologies' => 'text',
            'category' => 'varchar(100)',
            'featured' => 'tinyint(1) DEFAULT 0',
            'sort_order' => 'int(11) DEFAULT 0',
            'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],
    'portfolio_experience' => [
        'structure' => "CREATE TABLE `portfolio_experience` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `company` varchar(255) NOT NULL,
            `position` varchar(255) NOT NULL,
            `description` text,
            `start_date` date,
            `end_date` date,
            `is_current` tinyint(1) DEFAULT 0,
            `technologies` text,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'columns' => [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'company' => 'varchar(255) NOT NULL',
            'position' => 'varchar(255) NOT NULL',
            'description' => 'text',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'tinyint(1) DEFAULT 0',
            'technologies' => 'text',
            'sort_order' => 'int(11) DEFAULT 0',
            'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],
    'portfolio_education' => [
        'structure' => "CREATE TABLE `portfolio_education` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `institution` varchar(255) NOT NULL,
            `degree` varchar(255) NOT NULL,
            `field_of_study` varchar(255),
            `description` text,
            `start_date` date,
            `end_date` date,
            `is_current` tinyint(1) DEFAULT 0,
            `gpa` decimal(3,2),
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'columns' => [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'institution' => 'varchar(255) NOT NULL',
            'degree' => 'varchar(255) NOT NULL',
            'field_of_study' => 'varchar(255)',
            'description' => 'text',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'tinyint(1) DEFAULT 0',
            'gpa' => 'decimal(3,2)',
            'sort_order' => 'int(11) DEFAULT 0',
            'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],
    'portfolio_skills' => [
        'structure' => "CREATE TABLE `portfolio_skills` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `category` varchar(100),
            `proficiency_level` int(11) DEFAULT 1,
            `years_experience` int(11) DEFAULT 0,
            `description` text,
            `is_featured` tinyint(1) DEFAULT 0,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'columns' => [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'name' => 'varchar(255) NOT NULL',
            'category' => 'varchar(100)',
            'proficiency_level' => 'int(11) DEFAULT 1',
            'years_experience' => 'int(11) DEFAULT 0',
            'description' => 'text',
            'is_featured' => 'tinyint(1) DEFAULT 0',
            'sort_order' => 'int(11) DEFAULT 0',
            'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],
    'portfolio_content' => [
        'structure' => "CREATE TABLE `portfolio_content` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `section` varchar(100) NOT NULL,
            `content_key` varchar(255) NOT NULL,
            `content_value` text,
            `content_type` varchar(50) DEFAULT 'text',
            `is_active` tinyint(1) DEFAULT 1,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `section_key` (`section`, `content_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'columns' => [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'section' => 'varchar(100) NOT NULL',
            'content_key' => 'varchar(255) NOT NULL',
            'content_value' => 'text',
            'content_type' => 'varchar(50) DEFAULT \'text\'',
            'is_active' => 'tinyint(1) DEFAULT 1',
            'sort_order' => 'int(11) DEFAULT 0',
            'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ]
];

// Test 1: Database Connection
$total_tests++;
try {
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    $passed_tests++;
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    $failed_tests++;
    $issues[] = "Database connection failed";
}

// Test 2: Check and Repair Portfolio Tables
echo "<h2>🏗️ Portfolio Database Structure Analysis & Repair</h2>";

foreach ($portfolio_tables as $table_name => $table_info) {
    $total_tests++;
    echo "<h3>📋 Table: $table_name</h3>";
    
    // Check if table exists
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");
        $table_exists = $stmt->rowCount() > 0;
        
        if (!$table_exists) {
            echo "<p style='color: orange;'>⚠️ Table '$table_name' does not exist</p>";
            if (createTable($pdo, $table_name, $table_info['structure'])) {
                echo "<p style='color: green;'>✅ Table '$table_name' created successfully</p>";
                $passed_tests++;
            } else {
                $failed_tests++;
                $issues[] = "Failed to create table '$table_name'";
                continue;
            }
        } else {
            echo "<p style='color: green;'>✅ Table '$table_name' exists</p>";
            $passed_tests++;
        }
        
        // Check columns
        $stmt = $pdo->query("DESCRIBE $table_name");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $existing_columns = [];
        foreach ($columns as $column) {
            $existing_columns[] = $column['Field'];
        }
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Status</th>";
        echo "</tr>";
        
        foreach ($table_info['columns'] as $column_name => $column_def) {
            $total_tests++;
            
            if (in_array($column_name, $existing_columns)) {
                // Find column details
                $column_details = null;
                foreach ($columns as $col) {
                    if ($col['Field'] === $column_name) {
                        $column_details = $col;
                        break;
                    }
                }
                
                echo "<tr>";
                echo "<td>$column_name</td>";
                echo "<td>" . $column_details['Type'] . "</td>";
                echo "<td>" . $column_details['Null'] . "</td>";
                echo "<td>" . $column_details['Key'] . "</td>";
                echo "<td>" . $column_details['Default'] . "</td>";
                echo "<td style='color: green;'>✅ Exists</td>";
                echo "</tr>";
                $passed_tests++;
            } else {
                echo "<tr>";
                echo "<td>$column_name</td>";
                echo "<td colspan='4' style='color: orange;'>⚠️ Missing - Auto-repairing...</td>";
                
                if (addColumn($pdo, $table_name, $column_name, $column_def)) {
                    echo "<td style='color: green;'>✅ Added</td>";
                    $passed_tests++;
                } else {
                    echo "<td style='color: red;'>❌ Failed</td>";
                    $failed_tests++;
                    $issues[] = "Failed to add column '$column_name' to '$table_name'";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking table '$table_name': " . $e->getMessage() . "</p>";
        $failed_tests++;
        $issues[] = "Error checking table '$table_name'";
    }
}

// Test 3: Check Users Table Structure (if exists)
$total_tests++;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $users_table_exists = $stmt->rowCount() > 0;
    
    if ($users_table_exists) {
        echo "<h3>📋 Users Table Structure Analysis:</h3>";
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $existing_columns = [];
        foreach ($columns as $column) {
            $existing_columns[] = $column['Field'];
        }
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Status</th>";
        echo "</tr>";
        
        // Required columns for proper functionality
        $required_columns = [
            'id' => 'Primary key',
            'username' => 'User identification',
            'email' => 'User email',
            'password' => 'User authentication',
            'first_name' => 'User first name',
            'last_name' => 'User last name',
            'account_status' => 'Account status (active, suspended, banned)',
            'last_login_at' => 'Last login timestamp',
            'created_at' => 'Record creation timestamp',
            'updated_at' => 'Record update timestamp',
            'email_verified_at' => 'Email verification timestamp',
            'is_verified' => 'Email verification status'
        ];
    
        $column_status = [];
        foreach ($columns as $column) {
            $col_name = $column['Field'];
            $status = 'Unknown';
            $color = 'black';
            
            if (array_key_exists($col_name, $required_columns)) {
                $status = '✅ Required - ' . $required_columns[$col_name];
                $color = 'green';
                $passed_tests++;
            } elseif (in_array($col_name, ['avatar', 'bio', 'location', 'website', 'phone', 'role'])) {
                $status = '⚠️ Optional - Profile enhancement';
                $color = 'orange';
                $warnings++;
            } else {
                $status = '❓ Custom column - May need review';
                $color = 'blue';
        }
        
        echo "<tr>";
        echo "<td><strong>{$col_name}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td style='color: $color;'>$status</td>";
        echo "</tr>";
        
        $total_tests++;
    }
    echo "</table>";
    
    // Check for missing required columns and auto-repair
    foreach ($required_columns as $req_col => $description) {
        if (!in_array($req_col, $existing_columns)) {
            $total_tests++;
            
            // Determine column definition based on column name
            $column_def = '';
            switch ($req_col) {
                case 'first_name':
                case 'last_name':
                    $column_def = 'varchar(100) DEFAULT NULL';
                    break;
                case 'account_status':
                    $column_def = 'enum(\'active\', \'suspended\', \'banned\') DEFAULT \'active\'';
                    break;
                case 'last_login_at':
                    $column_def = 'timestamp NULL DEFAULT NULL';
                    break;
                case 'email_verified_at':
                    $column_def = 'timestamp NULL DEFAULT NULL';
                    break;
                case 'is_verified':
                    $column_def = 'tinyint(1) DEFAULT 0';
                    break;
                default:
                    $column_def = 'varchar(255) DEFAULT NULL';
            }
            
            echo "<p style='color: orange;'>⚠️ Missing required column: <strong>$req_col</strong> ($description) - Auto-repairing...</p>";
            
            if (addColumn($pdo, 'users', $req_col, $column_def)) {
                echo "<p style='color: green;'>✅ Added column '$req_col' to users table</p>";
                $passed_tests++;
            } else {
                $failed_tests++;
                $issues[] = "Failed to add required column: users.$req_col";
                echo "<p style='color: red;'>❌ Failed to add required column: <strong>$req_col</strong> ($description)</p>";
            }
        }
    }
    
    } else {
        echo "<p style='color: orange;'>⚠️ Users table does not exist - this is optional for portfolio functionality</p>";
        $warnings++;
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Failed to analyze users table: " . $e->getMessage() . "</p>";
    $failed_tests++;
    $issues[] = "Users table analysis failed";
}

// Test 3: Check other important tables
$important_tables = [
    'testimonials' => 'User testimonials',
    'admin_sessions' => 'Admin authentication',
    'password_resets' => 'Password reset functionality',
    'email_verifications' => 'Email verification tokens'
];

foreach ($important_tables as $table_name => $description) {
    $total_tests++;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table_name' exists ($description)</p>";
            $passed_tests++;
        } else {
            echo "<p style='color: orange;'>⚠️ Table '$table_name' missing ($description)</p>";
            $warnings++;
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking table '$table_name': " . $e->getMessage() . "</p>";
        $failed_tests++;
        $issues[] = "Table check failed: $table_name";
    }
}

// Test 4: Data Integrity Checks
echo "<h2>📊 User Data Integrity & Status Checks</h2>";

$total_tests++;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $user_count = $stmt->fetch()['total_users'];
    echo "<p style='color: blue;'>ℹ️ Total users in database: $user_count</p>";
    $passed_tests++;
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Failed to count users: " . $e->getMessage() . "</p>";
    $failed_tests++;
    $issues[] = "User count query failed";
}

// Test account status distribution
$total_tests++;
try {
    $stmt = $pdo->query("SELECT account_status, COUNT(*) as count FROM users GROUP BY account_status");
    $status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color: green;'>✅ Account status distribution:</p>";
    echo "<ul>";
    foreach ($status_counts as $status) {
        $status_name = $status['account_status'] ?? 'Unknown';
        $count = $status['count'];
        $color = $status_name === 'active' ? 'green' : ($status_name === 'suspended' ? 'orange' : 'red');
        echo "<li style='color: $color;'>$status_name: $count users</li>";
    }
    echo "</ul>";
    $passed_tests++;
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Failed to check account status distribution: " . $e->getMessage() . "</p>";
    $failed_tests++;
    $issues[] = "Account status check failed";
}

// Test last login tracking
$total_tests++;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as recent_logins FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recent_logins = $stmt->fetch()['recent_logins'];
    echo "<p style='color: green;'>✅ Users with recent logins (last 30 days): $recent_logins</p>";
    $passed_tests++;
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Failed to check recent logins: " . $e->getMessage() . "</p>";
    $failed_tests++;
    $issues[] = "Last login tracking check failed";
}

// Test name completeness
$total_tests++;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as complete_names FROM users WHERE first_name IS NOT NULL AND last_name IS NOT NULL AND first_name != '' AND last_name != ''");
    $complete_names = $stmt->fetch()['complete_names'];
    echo "<p style='color: green;'>✅ Users with complete names (first + last): $complete_names</p>";
    $passed_tests++;
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Failed to check name completeness: " . $e->getMessage() . "</p>";
    $failed_tests++;
    $issues[] = "Name completeness check failed";
}

// Test 5: Email Verification System Check
if (in_array('email_verified_at', $existing_columns ?? [])) {
    $total_tests++;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as verified_users FROM users WHERE email_verified_at IS NOT NULL");
        $verified_count = $stmt->fetch()['verified_users'];
        echo "<p style='color: green;'>✅ Email verification system ready - $verified_count verified users</p>";
        $passed_tests++;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Email verification check failed: " . $e->getMessage() . "</p>";
        $failed_tests++;
        $issues[] = "Email verification check failed";
    }
}

// Calculate success rate
$success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100) : 0;

echo "<hr>";
echo "<h2>📊 Database Debug Summary</h2>";
echo "<div style='background-color: " . ($success_rate >= 95 ? '#e8f5e8' : ($success_rate >= 80 ? '#fff3cd' : '#ffebee')) . "; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Test Results:</h3>";
echo "<ul>";
echo "<li><strong>Total Tests:</strong> $total_tests</li>";
echo "<li><strong>Passed:</strong> <span style='color: green;'>$passed_tests</span></li>";
echo "<li><strong>Warnings:</strong> <span style='color: orange;'>$warnings</span></li>";
echo "<li><strong>Failed:</strong> <span style='color: red;'>$failed_tests</span></li>";
echo "<li><strong>Success Rate:</strong> <span style='font-size: 1.2em; font-weight: bold;'>$success_rate%</span></li>";
echo "</ul>";
echo "</div>";

if (count($issues) > 0) {
    echo "<h3>🚨 Issues Found:</h3>";
    echo "<ol>";
    foreach ($issues as $index => $issue) {
        echo "<li style='color: red; margin: 5px 0;'>$issue</li>";
    }
    echo "</ol>";
    
    echo "<div style='background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🔧 Recommended Actions:</h4>";
    echo "<ul>";
    echo "<li>Run the specific column fix script to add missing columns</li>";
    echo "<li>Check database configuration and permissions</li>";
    echo "<li>Verify all required tables are created</li>";
    echo "<li>Test email verification functionality</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: green;'>🎉 All Tests Passed!</h3>";
    echo "<p>Your database schema is properly configured and ready for use.</p>";
    echo "</div>";
}

// Generate machine-readable output for automated parsing
echo "<div style='display: none;' id='debug-summary'>";
echo "=== DATABASE DEBUG ISSUES ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Total Tests: $total_tests | Passed: $passed_tests | Warnings: $warnings | Failed: $failed_tests\n";
echo "Success Rate: $success_rate%\n\n";

if ($failed_tests > 0) {
    echo "FAILED TESTS ($failed_tests):\n";
    $counter = 1;
    foreach ($issues as $issue) {
        echo "$counter. $issue\n";
        $counter++;
    }
    echo "\n";
}

if ($warnings > 0) {
    echo "WARNINGS ($warnings):\n";
    echo "1. Some optional columns detected\n\n";
}

echo "=== END ISSUES ===\n";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f0f0f0; }
</style>

<div style="margin-top: 30px; text-align: center; background: white; padding: 20px; border-radius: 5px;">
    <h3>🛠️ Additional Tools</h3>
    <a href="../debug_dashboard.php" style="background-color: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;">
        ← Back to Dashboard
    </a>
    <a href="../../config/setup_database.php" style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;">
        🏗️ Database Setup
    </a>
    <a href="../../index.php" style="background-color: #6f42c1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;">
        🏠 Portfolio Home
    </a>
</div>

<!-- Final Summary Section -->
<div style='background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;'>
<h2>📊 Test Summary & Auto-Repair Results</h2>

<?php
$success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 2) : 0;

echo "<div style='display: flex; gap: 20px; margin: 10px 0;'>";
echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>✅ Passed:</strong> $passed_tests";
echo "</div>";
echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>⚠️ Warnings:</strong> $warnings";
echo "</div>";
echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>❌ Failed:</strong> $failed_tests";
echo "</div>";
echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>🔧 Repairs Made:</strong> $repairs_made";
echo "</div>";
echo "</div>";

echo "<p><strong>Total Tests:</strong> $total_tests</p>";
echo "<p><strong>Success Rate:</strong> $success_rate%</p>";

if ($repairs_made > 0) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🔧 Auto-Repair Summary</h3>";
    echo "<p><strong>$repairs_made</strong> automatic repairs were performed to fix missing database components.</p>";
    echo "<p>Your portfolio database structure has been updated to include all required tables and columns.</p>";
    echo "</div>";
}

if ($failed_tests > 0) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Issues Requiring Attention</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if ($warnings > 0) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>⚠️ Warnings</h3>";
    echo "<p>Some non-critical issues were detected but do not affect core functionality.</p>";
    echo "</div>";
}

if ($failed_tests == 0 && $repairs_made > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🎉 Database Fully Repaired</h3>";
    echo "<p>All required portfolio database components are now in place and functional.</p>";
    echo "</div>";
} elseif ($failed_tests == 0 && $repairs_made == 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🎉 Database Healthy</h3>";
    echo "<p>Your portfolio database structure is complete and properly configured.</p>";
    echo "</div>";
}

// Final Summary
echo "<div style='background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h2>📊 Test Summary</h2>";

$success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 2) : 0;

echo "<div style='display: flex; gap: 20px; margin: 10px 0;'>";
echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>✅ Passed:</strong> $passed_tests";
echo "</div>";
echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>⚠️ Warnings:</strong> $warnings";
echo "</div>";
echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>❌ Failed:</strong> $failed_tests";
echo "</div>";
echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px; flex: 1;'>";
echo "<strong>🔧 Repairs Made:</strong> $repairs_made";
echo "</div>";
echo "</div>";

echo "<p><strong>Total Tests:</strong> $total_tests</p>";
echo "<p><strong>Success Rate:</strong> $success_rate%</p>";

if ($repairs_made > 0) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🔧 Auto-Repair Summary</h3>";
    echo "<p><strong>$repairs_made</strong> automatic repairs were performed to fix missing database components.</p>";
    echo "<p>Your portfolio database structure has been updated to include all required tables and columns.</p>";
    echo "</div>";
}

if ($failed_tests > 0) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Issues Requiring Attention</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if ($warnings > 0) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>⚠️ Warnings</h3>";
    echo "<p>Some non-critical issues were detected but do not affect core functionality.</p>";
    echo "</div>";
}

if ($failed_tests == 0 && $repairs_made > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🎉 Database Fully Repaired</h3>";
    echo "<p>All required portfolio database components are now in place and functional.</p>";
    echo "</div>";
} elseif ($failed_tests == 0 && $repairs_made == 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🎉 Database Healthy</h3>";
    echo "<p>Your portfolio database structure is complete and properly configured.</p>";
    echo "</div>";
}

echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; background: white; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f0f0f0; font-weight: bold; }
tr:nth-child(even) { background-color: #f9f9f9; }
</style>

<div style="margin-top: 30px; text-align: center; background: white; padding: 20px; border-radius: 5px;">
    <h3>🛠️ Additional Tools</h3>
    <a href="../debug_dashboard.php" style="background-color: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;">
        ← Back to Dashboard
    </a>
    <a href="../../config/setup_database.php" style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;">
        🏗️ Database Setup
    </a>
    <a href="../../index.php" style="background-color: #6f42c1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;">
        🏠 Portfolio Home
    </a>
</div>
