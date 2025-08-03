<?php
/**
 * Database Debug Tool
 * Comprehensive database health check and table structure verification
 * 
 * This tool checks:
 * - Database connection status
 * - Table existence and structure
 * - Column verification for all tables
 * - Index verification
 * - Character set and collation
 * - Sample data verification
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once '../../config/database.php';

// Handle AJAX fix requests BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_type'])) {
    header('Content-Type: application/json');
    
    $fixType = $_POST['fix_type'];
    $tableName = $_POST['table_name'] ?? '';
    $itemName = $_POST['item_name'] ?? '';
    
    try {
        $success = false;
        $message = '';
        
        // Helper functions for fixing database issues
        function createMissingTable($pdo, $tableName) {
            $tableDefinitions = [
                'users' => "CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    first_name VARCHAR(50) NOT NULL,
                    last_name VARCHAR(50) NOT NULL,
                    linkedin_profile VARCHAR(255) NULL,
                    profile_picture_url VARCHAR(255) NULL,
                    status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
                    email_verified_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    last_login TIMESTAMP NULL
                )",
                'testimonials' => "CREATE TABLE testimonials (
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
                )",
                'user_sessions' => "CREATE TABLE user_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    session_token VARCHAR(255) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                'admin_users' => "CREATE TABLE admin_users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )",
                'admin_sessions' => "CREATE TABLE admin_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    session_token VARCHAR(255) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
                )",
                'password_reset_tokens' => "CREATE TABLE password_reset_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    verification_code VARCHAR(6) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    used BOOLEAN DEFAULT FALSE,
                    verified BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
                )"
            ];
            
            if (isset($tableDefinitions[$tableName])) {
                $pdo->exec($tableDefinitions[$tableName]);
                return true;
            }
            return false;
        }
        
        function addMissingColumn($pdo, $tableName, $columnName) {
            $columnDefinitions = [
                'users' => [
                    'avatar' => "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL"
                ]
            ];
            
            if (isset($columnDefinitions[$tableName][$columnName])) {
                $pdo->exec($columnDefinitions[$tableName][$columnName]);
                return true;
            }
            return false;
        }
        
        function createMissingIndex($pdo, $tableName, $indexName) {
            $indexDefinitions = [
                'idx_testimonials_status' => "CREATE INDEX idx_testimonials_status ON testimonials(status)",
                'idx_testimonials_user_id' => "CREATE INDEX idx_testimonials_user_id ON testimonials(user_id)",
                'idx_testimonials_created_at' => "CREATE INDEX idx_testimonials_created_at ON testimonials(created_at)",
                'idx_users_email' => "CREATE INDEX idx_users_email ON users(email)",
                'idx_users_status' => "CREATE INDEX idx_users_status ON users(status)",
                'idx_user_sessions_token' => "CREATE INDEX idx_user_sessions_token ON user_sessions(session_token)",
                'idx_admin_sessions_token' => "CREATE INDEX idx_admin_sessions_token ON admin_sessions(session_token)",
                'idx_password_reset_tokens_token' => "CREATE INDEX idx_password_reset_tokens_token ON password_reset_tokens(token)"
            ];
            
            if (isset($indexDefinitions[$indexName])) {
                $pdo->exec($indexDefinitions[$indexName]);
                return true;
            }
            return false;
        }
        
        function applyAllFixes($pdo) {
            $fixedItems = [];
            $failedItems = [];
            
            try {
                // Get list of existing tables to check what needs fixing
                $stmt = $pdo->query("SHOW TABLES");
                $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $expectedTables = ['users', 'testimonials', 'user_sessions', 'admin_users', 'admin_sessions', 'password_reset_tokens'];
                
                // Fix missing tables first
                foreach ($expectedTables as $tableName) {
                    if (!in_array($tableName, $existingTables)) {
                        if (createMissingTable($pdo, $tableName)) {
                            $fixedItems[] = "Created table: $tableName";
                        } else {
                            $failedItems[] = "Failed to create table: $tableName";
                        }
                    }
                }
                
                // Fix missing columns
                if (in_array('users', $existingTables) || in_array('users', array_map(function($item) { return str_replace('Created table: ', '', $item); }, $fixedItems))) {
                    $stmt = $pdo->query("DESCRIBE users");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (!in_array('avatar', $columns)) {
                        if (addMissingColumn($pdo, 'users', 'avatar')) {
                            $fixedItems[] = "Added column: users.avatar";
                        } else {
                            $failedItems[] = "Failed to add column: users.avatar";
                        }
                    }
                }
                
                // Fix missing indexes
                $indexes = [
                    'idx_testimonials_status',
                    'idx_testimonials_user_id', 
                    'idx_testimonials_created_at',
                    'idx_users_email',
                    'idx_users_status',
                    'idx_user_sessions_token',
                    'idx_admin_sessions_token',
                    'idx_password_reset_tokens_token'
                ];
                
                foreach ($indexes as $indexName) {
                    try {
                        if (createMissingIndex($pdo, '', $indexName)) {
                            $fixedItems[] = "Created index: $indexName";
                        }
                    } catch (Exception $e) {
                        // Index might already exist or table might not exist yet
                        if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                            $failedItems[] = "Failed to create index: $indexName - " . $e->getMessage();
                        }
                    }
                }
                
                // Insert default admin user if missing
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
                    $adminExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
                    
                    if (!$adminExists) {
                        $pdo->exec("INSERT INTO admin_users (username, password, email) VALUES ('admin', '\$2y\$10\$tgbY4n8H.5TFmwt8qVVK2.Wk5tOXWJ5PkO4K8VHV.8hIJ8v5.yoJG', 'admin@webbr.com')");
                        $fixedItems[] = "Created default admin user";
                    }
                } catch (Exception $e) {
                    $failedItems[] = "Failed to create admin user: " . $e->getMessage();
                }
                
                $message = '';
                if (!empty($fixedItems)) {
                    $message .= "Fixed: " . implode(', ', $fixedItems);
                }
                if (!empty($failedItems)) {
                    $message .= (!empty($fixedItems) ? " | " : "") . "Failed: " . implode(', ', $failedItems);
                }
                
                return [
                    'success' => empty($failedItems),
                    'message' => $message ?: 'No issues found to fix'
                ];
                
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Bulk fix error: ' . $e->getMessage()
                ];
            }
        }
        
        switch ($fixType) {
            case 'missing_table':
                $success = createMissingTable($pdo, $tableName);
                $message = $success ? "Table $tableName created successfully" : "Failed to create table $tableName";
                break;
                
            case 'missing_column':
                $success = addMissingColumn($pdo, $tableName, $itemName);
                $message = $success ? "Column $itemName added to $tableName" : "Failed to add column $itemName";
                break;
                
            case 'missing_index':
                $success = createMissingIndex($pdo, $tableName, $itemName);
                $message = $success ? "Index $itemName created on $tableName" : "Failed to create index $itemName";
                break;
                
            case 'bulk_fix':
                $result = applyAllFixes($pdo);
                $success = $result['success'];
                $message = $result['message'];
                break;
        }
        
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Debug Tool - Luthor Portfolio</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .test-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .test-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            color: #2c3e50;
        }
        .test-content {
            padding: 20px;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .status-info {
            color: #17a2b8;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .icon {
            margin-right: 5px;
        }
        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-error {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
        }
        .fix-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }
        .fix-button:hover {
            background: #218838;
        }
        .fix-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .fix-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Database Debug Tool</h1>
            <p>Comprehensive Database Health Check for Luthor Portfolio</p>
            <p><small>Generated on: <?php echo date('Y-m-d H:i:s'); ?></small></p>
        </div>
        
        <div class="content">
            <script>
            function executeFix(type, table, item) {
                const button = event.target;
                const originalText = button.innerHTML;
                
                button.disabled = true;
                button.innerHTML = '⏳ Fixing...';
                
                const formData = new FormData();
                formData.append('fix_type', type);
                formData.append('table_name', table);
                formData.append('item_name', item);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.innerHTML = '✅ Fixed';
                        button.style.background = '#6c757d';
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        button.innerHTML = '❌ Failed';
                        button.style.background = '#dc3545';
                        alert('Fix failed: ' + data.message);
                        setTimeout(() => {
                            button.disabled = false;
                            button.innerHTML = originalText;
                            button.style.background = '#28a745';
                        }, 3000);
                    }
                })
                .catch(error => {
                    button.innerHTML = '❌ Error';
                    button.style.background = '#dc3545';
                    alert('Error: ' + error.message);
                    setTimeout(() => {
                        button.disabled = false;
                        button.innerHTML = originalText;
                        button.style.background = '#28a745';
                    }, 3000);
                });
            }
            </script>
            
            <?php
            $testResults = [];
            $totalTests = 0;
            $passedTests = 0;
            $failedTests = 0;
            $warningTests = 0;
            
            // Arrays to collect issues for copy-paste
            $failedMessages = [];
            $warningMessages = [];
            
            // Test 1: Database Connection
            echo '<div class="test-section">';
            echo '<div class="test-header">📡 Database Connection Test</div>';
            echo '<div class="test-content">';
            
            $totalTests++;
            try {
                if (isset($pdo) && $pdo instanceof PDO) {
                    echo '<span class="status-success">✅ Connection Status: CONNECTED</span><br>';
                    
                    // Get database info
                    $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as version");
                    $dbInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo '<span class="status-info">📊 Database: ' . htmlspecialchars($dbInfo['current_db']) . '</span><br>';
                    echo '<span class="status-info">🔢 MySQL Version: ' . htmlspecialchars($dbInfo['version']) . '</span><br>';
                    
                    // Test connection with a simple query
                    $pdo->query("SELECT 1");
                    echo '<span class="status-success">✅ Query Test: SUCCESSFUL</span><br>';
                    
                    $passedTests++;
                    $testResults['connection'] = 'PASS';
                } else {
                    throw new Exception("PDO object not initialized");
                }
            } catch (Exception $e) {
                echo '<span class="status-error">❌ Connection Status: FAILED</span><br>';
                echo '<span class="status-error">Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                $failedTests++;
                $failedMessages[] = "Database Connection: " . $e->getMessage();
                $testResults['connection'] = 'FAIL';
            }
            echo '</div></div>';
            
            // Test 2: Table Existence and Structure
            echo '<div class="test-section">';
            echo '<div class="test-header">🗂️ Table Structure Verification</div>';
            echo '<div class="test-content">';
            
            $expectedTables = [
                'users' => [
                    'id' => 'int(11) AUTO_INCREMENT PRIMARY KEY',
                    'username' => 'varchar(50) UNIQUE NOT NULL',
                    'email' => 'varchar(100) UNIQUE NOT NULL',
                    'password' => 'varchar(255) NOT NULL',
                    'first_name' => 'varchar(50) NOT NULL',
                    'last_name' => 'varchar(50) NOT NULL',
                    'linkedin_profile' => 'varchar(255) NULL',
                    'profile_picture_url' => 'varchar(255) NULL',
                    'status' => "enum('active','inactive','suspended') DEFAULT 'inactive'",
                    'email_verified_at' => 'timestamp NULL',
                    'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                    'last_login' => 'timestamp NULL'
                ],
                'testimonials' => [
                    'id' => 'int(11) AUTO_INCREMENT PRIMARY KEY',
                    'user_id' => 'int(11) NULL',
                    'name' => 'varchar(255) NOT NULL',
                    'company' => 'varchar(255)',
                    'position' => 'varchar(255)',
                    'email' => 'varchar(255) NOT NULL',
                    'linkedin_profile' => 'varchar(255) NULL',
                    'message' => 'text NOT NULL',
                    'rating' => 'int(11) DEFAULT 5',
                    'avatar' => 'varchar(255) DEFAULT NULL',
                    'status' => "enum('pending','approved','rejected') DEFAULT 'pending'",
                    'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
                ],
                'user_sessions' => [
                    'id' => 'int(11) AUTO_INCREMENT PRIMARY KEY',
                    'user_id' => 'int(11) NOT NULL',
                    'session_token' => 'varchar(255) NOT NULL',
                    'expires_at' => 'timestamp NOT NULL',
                    'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP'
                ],
                'admin_users' => [
                    'id' => 'int(11) AUTO_INCREMENT PRIMARY KEY',
                    'username' => 'varchar(50) UNIQUE NOT NULL',
                    'password' => 'varchar(255) NOT NULL',
                    'email' => 'varchar(255) NOT NULL',
                    'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP'
                ],
                'admin_sessions' => [
                    'id' => 'int(11) AUTO_INCREMENT PRIMARY KEY',
                    'user_id' => 'int(11) NOT NULL',
                    'session_token' => 'varchar(255) NOT NULL',
                    'expires_at' => 'datetime NOT NULL',
                    'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP'
                ],
                'password_reset_tokens' => [
                    'id' => 'int(11) AUTO_INCREMENT PRIMARY KEY',
                    'user_id' => 'int(11) NOT NULL',
                    'token' => 'varchar(255) NOT NULL',
                    'verification_code' => 'varchar(6) NOT NULL',
                    'expires_at' => 'datetime NOT NULL',
                    'used' => 'tinyint(1) DEFAULT 0',
                    'verified' => 'tinyint(1) DEFAULT 0',
                    'created_at' => 'timestamp DEFAULT CURRENT_TIMESTAMP'
                ]
            ];
            
            try {
                // Get all tables
                $stmt = $pdo->query("SHOW TABLES");
                $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                echo '<h4>Table Existence Check:</h4>';
                foreach ($expectedTables as $tableName => $columns) {
                    $totalTests++;
                    if (in_array($tableName, $existingTables)) {
                        echo '<span class="status-success">✅ Table "' . $tableName . '": EXISTS</span><br>';
                        $passedTests++;
                    } else {
                        echo '<span class="status-error">❌ Table "' . $tableName . '": MISSING</span>';
                        echo '<button class="fix-button" onclick="executeFix(\'missing_table\', \'' . $tableName . '\', \'\')">🔧 Fix Table</button><br>';
                        $failedMessages[] = "Missing table: $tableName";
                        $failedTests++;
                    }
                }
                
                // Check column structure for each table
                echo '<h4>Column Structure Verification:</h4>';
                foreach ($expectedTables as $tableName => $expectedColumns) {
                    if (in_array($tableName, $existingTables)) {
                        echo "<h5>Table: $tableName</h5>";
                        echo '<table>';
                        echo '<tr><th>Column</th><th>Expected</th><th>Actual</th><th>Status</th></tr>';
                        
                        $stmt = $pdo->query("DESCRIBE $tableName");
                        $actualColumns = [];
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $actualColumns[$row['Field']] = $row;
                        }
                        
                        foreach ($expectedColumns as $columnName => $expectedDef) {
                            $totalTests++;
                            if (isset($actualColumns[$columnName])) {
                                echo "<tr>";
                                echo "<td>$columnName</td>";
                                echo "<td><small>$expectedDef</small></td>";
                                $actualDef = $actualColumns[$columnName]['Type'];
                                if ($actualColumns[$columnName]['Null'] === 'NO' && !strpos($expectedDef, 'NOT NULL')) {
                                    // Handle cases where we expect NULL but got NOT NULL
                                }
                                echo "<td><small>$actualDef</small></td>";
                                echo '<td><span class="badge badge-success">EXISTS</span></td>';
                                echo "</tr>";
                                $passedTests++;
                            } else {
                                echo "<tr>";
                                echo "<td>$columnName</td>";
                                echo "<td><small>$expectedDef</small></td>";
                                echo "<td><em>MISSING</em></td>";
                                echo '<td><span class="badge badge-error">MISSING</span> <button class="fix-button" onclick="executeFix(\'missing_column\', \'' . $tableName . '\', \'' . $columnName . '\')">🔧 Fix</button></td>';
                                echo "</tr>";
                                $failedMessages[] = "Missing column: $tableName.$columnName";
                                $failedTests++;
                            }
                        }
                        
                        // Check for unexpected columns
                        foreach ($actualColumns as $columnName => $columnInfo) {
                            if (!isset($expectedColumns[$columnName])) {
                                echo "<tr>";
                                echo "<td>$columnName</td>";
                                echo "<td><em>Not Expected</em></td>";
                                echo "<td><small>" . $columnInfo['Type'] . "</small></td>";
                                echo '<td><span class="badge badge-warning">EXTRA</span></td>';
                                echo "</tr>";
                                $warningMessages[] = "Unexpected column: $tableName.$columnName (" . $columnInfo['Type'] . ")";
                                $warningTests++;
                            }
                        }
                        
                        echo '</table>';
                    }
                }
                
            } catch (Exception $e) {
                echo '<span class="status-error">❌ Table Structure Check: FAILED</span><br>';
                echo '<span class="status-error">Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                $failedMessages[] = "Table Structure Check: " . $e->getMessage();
                $failedTests++;
            }
            echo '</div></div>';
            
            // Test 3: Index Verification
            echo '<div class="test-section">';
            echo '<div class="test-header">🔍 Index Verification</div>';
            echo '<div class="test-content">';
            
            $expectedIndexes = [
                'testimonials' => ['idx_testimonials_status', 'idx_testimonials_user_id', 'idx_testimonials_created_at'],
                'users' => ['idx_users_email', 'idx_users_status'],
                'user_sessions' => ['idx_user_sessions_token'],
                'admin_sessions' => ['idx_admin_sessions_token'],
                'password_reset_tokens' => ['idx_password_reset_tokens_token']
            ];
            
            try {
                foreach ($expectedIndexes as $tableName => $indexes) {
                    if (in_array($tableName, $existingTables)) {
                        echo "<h5>Indexes for table: $tableName</h5>";
                        $stmt = $pdo->query("SHOW INDEX FROM $tableName");
                        $actualIndexes = [];
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $actualIndexes[] = $row['Key_name'];
                        }
                        
                        foreach ($indexes as $indexName) {
                            $totalTests++;
                            if (in_array($indexName, $actualIndexes)) {
                                echo '<span class="status-success">✅ Index "' . $indexName . '": EXISTS</span><br>';
                                $passedTests++;
                            } else {
                                echo '<span class="status-warning">⚠️ Index "' . $indexName . '": MISSING (Performance may be affected)</span>';
                                echo '<button class="fix-button" onclick="executeFix(\'missing_index\', \'' . $tableName . '\', \'' . $indexName . '\')">🔧 Fix Index</button><br>';
                                $warningMessages[] = "Missing index: $indexName on table $tableName";
                                $warningTests++;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                echo '<span class="status-error">❌ Index Check: FAILED</span><br>';
                echo '<span class="status-error">Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                $failedMessages[] = "Index Check: " . $e->getMessage();
                $failedTests++;
            }
            echo '</div></div>';
            
            // Test 4: Character Set and Collation
            echo '<div class="test-section">';
            echo '<div class="test-header">🌍 Character Set and Collation</div>';
            echo '<div class="test-content">';
            
            try {
                $stmt = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = DATABASE()");
                $charset = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $totalTests++;
                if ($charset['DEFAULT_CHARACTER_SET_NAME'] === 'utf8mb4') {
                    echo '<span class="status-success">✅ Database Character Set: utf8mb4 (Recommended)</span><br>';
                    $passedTests++;
                } else {
                    echo '<span class="status-warning">⚠️ Database Character Set: ' . $charset['DEFAULT_CHARACTER_SET_NAME'] . ' (utf8mb4 recommended)</span><br>';
                    $warningMessages[] = "Character set is " . $charset['DEFAULT_CHARACTER_SET_NAME'] . " (utf8mb4 recommended)";
                    $warningTests++;
                }
                
                echo '<span class="status-info">📝 Database Collation: ' . $charset['DEFAULT_COLLATION_NAME'] . '</span><br>';
                
            } catch (Exception $e) {
                echo '<span class="status-error">❌ Character Set Check: FAILED</span><br>';
                echo '<span class="status-error">Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                $failedMessages[] = "Character Set Check: " . $e->getMessage();
                $failedTests++;
            }
            echo '</div></div>';
            
            // Test 5: Sample Data Check
            echo '<div class="test-section">';
            echo '<div class="test-header">📊 Sample Data Verification</div>';
            echo '<div class="test-content">';
            
            try {
                $dataCounts = [];
                foreach (['users', 'testimonials', 'admin_users'] as $table) {
                    if (in_array($table, $existingTables)) {
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                        $dataCounts[$table] = $count;
                        
                        if ($table === 'admin_users' && $count === 0) {
                            echo '<span class="status-warning">⚠️ No admin users found - you may need to run the setup script</span><br>';
                            $warningMessages[] = "No admin users found in database";
                        } else {
                            echo '<span class="status-info">📈 ' . ucfirst($table) . ': ' . $count . ' records</span><br>';
                        }
                    }
                }
                
                // Check admin user specifically
                if (in_array('admin_users', $existingTables)) {
                    $stmt = $pdo->query("SELECT username FROM admin_users WHERE username = 'admin'");
                    $defaultAdmin = $stmt->fetch();
                    
                    $totalTests++;
                    if ($defaultAdmin) {
                        echo '<span class="status-success">✅ Default admin user exists</span><br>';
                        $passedTests++;
                    } else {
                        echo '<span class="status-warning">⚠️ Default admin user not found</span><br>';
                        $warningMessages[] = "Default admin user 'admin' not found";
                        $warningTests++;
                    }
                }
                
            } catch (Exception $e) {
                echo '<span class="status-error">❌ Sample Data Check: FAILED</span><br>';
                echo '<span class="status-error">Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                $failedMessages[] = "Sample Data Check: " . $e->getMessage();
                $failedTests++;
            }
            echo '</div></div>';
            
            // Test 6: Foreign Key Constraints
            echo '<div class="test-section">';
            echo '<div class="test-header">🔗 Foreign Key Constraints</div>';
            echo '<div class="test-content">';
            
            try {
                $stmt = $pdo->query("
                    SELECT 
                        TABLE_NAME,
                        COLUMN_NAME,
                        CONSTRAINT_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($foreignKeys) > 0) {
                    echo '<table>';
                    echo '<tr><th>Table</th><th>Column</th><th>References</th><th>Status</th></tr>';
                    
                    foreach ($foreignKeys as $fk) {
                        echo '<tr>';
                        echo '<td>' . $fk['TABLE_NAME'] . '</td>';
                        echo '<td>' . $fk['COLUMN_NAME'] . '</td>';
                        echo '<td>' . $fk['REFERENCED_TABLE_NAME'] . '.' . $fk['REFERENCED_COLUMN_NAME'] . '</td>';
                        echo '<td><span class="badge badge-success">ACTIVE</span></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    
                    $totalTests++;
                    $passedTests++;
                    echo '<span class="status-success">✅ Foreign key constraints are properly configured</span><br>';
                } else {
                    $totalTests++;
                    $warningMessages[] = "No foreign key constraints found";
                    $warningTests++;
                    echo '<span class="status-warning">⚠️ No foreign key constraints found</span><br>';
                }
                
            } catch (Exception $e) {
                echo '<span class="status-error">❌ Foreign Key Check: FAILED</span><br>';
                echo '<span class="status-error">Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
                $failedMessages[] = "Foreign Key Check: " . $e->getMessage();
                $failedTests++;
            }
            echo '</div></div>';
            
            // Summary Statistics
            echo '<div class="test-section">';
            echo '<div class="test-header">📈 Test Summary</div>';
            echo '<div class="test-content">';
            
            echo '<div class="summary-stats">';
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $totalTests . '</div>';
            echo '<div class="stat-label">Total Tests</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number" style="color: #28a745;">' . $passedTests . '</div>';
            echo '<div class="stat-label">Passed</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number" style="color: #ffc107;">' . $warningTests . '</div>';
            echo '<div class="stat-label">Warnings</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number" style="color: #dc3545;">' . $failedTests . '</div>';
            echo '<div class="stat-label">Failed</div>';
            echo '</div>';
            echo '</div>';
            
            $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
            
            if ($failedTests === 0 && $warningTests === 0) {
                echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">';
                echo '<strong>🎉 Excellent!</strong> All database checks passed successfully. Your database is properly configured and ready for production.';
                echo '</div>';
            } elseif ($failedTests === 0) {
                echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;">';
                echo '<strong>⚠️ Good with warnings.</strong> Your database is functional but has some optimization opportunities.';
                echo '</div>';
            } else {
                echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">';
                echo '<strong>❌ Issues detected.</strong> Some critical database components are missing or misconfigured. Please review the failed tests above.';
                echo '</div>';
            }
            
            echo '<p><strong>Overall Success Rate: ' . $successRate . '%</strong></p>';
            
            echo '</div></div>';
            
            // Bulk Fix Section
            if ($failedTests > 0 || $warningTests > 0) {
                echo '<div class="test-section">';
                echo '<div class="test-header">🛠️ Quick Fix All Issues</div>';
                echo '<div class="test-content">';
                echo '<div class="fix-section">';
                echo '<h4>Automated Database Repair</h4>';
                echo '<p>Click the button below to automatically fix all detected issues:</p>';
                echo '<button class="fix-button" style="padding: 10px 20px; font-size: 14px;" onclick="fixAllIssues()">🔧 Fix All Issues</button>';
                echo '<div id="bulk-fix-status" style="margin-top: 10px;"></div>';
                echo '</div>';
                echo '</div></div>';
                
                echo '<script>';
                echo 'function fixAllIssues() {';
                echo '    const button = event.target;';
                echo '    const status = document.getElementById("bulk-fix-status");';
                echo '    button.disabled = true;';
                echo '    button.innerHTML = "⏳ Applying Fixes...";';
                echo '    status.innerHTML = "<p>🔄 Starting bulk fix process...</p>";';
                echo '    ';
                echo '    const formData = new FormData();';
                echo '    formData.append("fix_type", "bulk_fix");';
                echo '    ';
                echo '    fetch("", {';
                echo '        method: "POST",';
                echo '        body: formData';
                echo '    })';
                echo '    .then(response => response.json())';
                echo '    .then(data => {';
                echo '        if (data.success) {';
                echo '            status.innerHTML = "<p style=\"color: #28a745;\">✅ All fixes applied successfully! Reloading page...</p>";';
                echo '            setTimeout(() => location.reload(), 2000);';
                echo '        } else {';
                echo '            status.innerHTML = "<p style=\"color: #dc3545;\">❌ Some fixes failed: " + data.message + "</p>";';
                echo '            button.disabled = false;';
                echo '            button.innerHTML = "🔧 Fix All Issues";';
                echo '        }';
                echo '    })';
                echo '    .catch(error => {';
                echo '        status.innerHTML = "<p style=\"color: #dc3545;\">❌ Error: " + error.message + "</p>";';
                echo '        button.disabled = false;';
                echo '        button.innerHTML = "🔧 Fix All Issues";';
                echo '    });';
                echo '}';
                echo '</script>';
            }
            
            // Copy-Paste Issues Section
            if (!empty($failedMessages) || !empty($warningMessages)) {
                echo '<div class="test-section">';
                echo '<div class="test-header">📋 Copy-Paste Issues Summary</div>';
                echo '<div class="test-content">';
                
                echo '<p>Copy the text below to share issues with support:</p>';
                
                $issuesSummary = "=== DATABASE DEBUG ISSUES ===\n";
                $issuesSummary .= "Generated: " . date('Y-m-d H:i:s') . "\n";
                $issuesSummary .= "Total Tests: $totalTests | Passed: $passedTests | Warnings: $warningTests | Failed: $failedTests\n";
                $issuesSummary .= "Success Rate: $successRate%\n\n";
                
                if (!empty($failedMessages)) {
                    $issuesSummary .= "FAILED TESTS (" . count($failedMessages) . "):\n";
                    foreach ($failedMessages as $i => $message) {
                        $issuesSummary .= ($i + 1) . ". " . $message . "\n";
                    }
                    $issuesSummary .= "\n";
                }
                
                if (!empty($warningMessages)) {
                    $issuesSummary .= "WARNINGS (" . count($warningMessages) . "):\n";
                    foreach ($warningMessages as $i => $message) {
                        $issuesSummary .= ($i + 1) . ". " . $message . "\n";
                    }
                    $issuesSummary .= "\n";
                }
                
                $issuesSummary .= "=== END ISSUES ===";
                
                echo '<textarea id="issues-summary" readonly style="width: 100%; height: 300px; font-family: monospace; font-size: 12px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">';
                echo htmlspecialchars($issuesSummary);
                echo '</textarea>';
                
                echo '<div style="margin-top: 10px;">';
                echo '<button onclick="copyToClipboard()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-right: 10px;">📋 Copy to Clipboard</button>';
                echo '<button onclick="selectAllText()" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✨ Select All</button>';
                echo '</div>';
                
                echo '<script>';
                echo 'function copyToClipboard() {';
                echo '    const textarea = document.getElementById("issues-summary");';
                echo '    textarea.select();';
                echo '    document.execCommand("copy");';
                echo '    alert("Issues summary copied to clipboard!");';
                echo '}';
                echo 'function selectAllText() {';
                echo '    const textarea = document.getElementById("issues-summary");';
                echo '    textarea.select();';
                echo '}';
                echo '</script>';
                
                echo '</div></div>';
            } else {
                echo '<div class="test-section">';
                echo '<div class="test-header">🎉 No Issues Found</div>';
                echo '<div class="test-content">';
                echo '<p style="color: #28a745; font-weight: bold;">Congratulations! No failed tests or warnings were detected. Your database is properly configured!</p>';
                echo '</div></div>';
            }
            ?>
        </div>
    </div>
</body>
</html>


