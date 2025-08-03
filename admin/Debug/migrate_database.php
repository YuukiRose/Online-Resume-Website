<?php
require_once '../../config/database.php';

echo "<h1>🔧 Complete Database Migration & Fix Script</h1>\n";
echo "<p>This script will ensure your database is properly set up for the user testimonial system.</p>\n";

try {
    echo "<h2>Step 1: Check and Create Users Table</h2>\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $usersTableExists = $stmt->fetch();
    
    if (!$usersTableExists) {
        echo "<p>❌ Users table missing. Creating...</p>\n";
        $createUsersTable = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        )";
        $pdo->exec($createUsersTable);
        echo "<p>✅ Users table created successfully</p>\n";
    } else {
        echo "<p>✅ Users table already exists</p>\n";
    }
    
    echo "<h2>Step 2: Update Testimonials Table Structure</h2>\n";
    
    // Get current columns
    $stmt = $pdo->query("DESCRIBE testimonials");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    // Check and add user_id column
    if (!in_array('user_id', $columnNames)) {
        echo "<p>🔄 Adding user_id column...</p>\n";
        $pdo->exec("ALTER TABLE testimonials ADD COLUMN user_id INT NULL AFTER id");
        echo "<p>✅ Added user_id column</p>\n";
    } else {
        echo "<p>✅ user_id column already exists</p>\n";
    }
    
    // Standardize testimonial/message column
    if (in_array('testimonial', $columnNames) && !in_array('message', $columnNames)) {
        echo "<p>🔄 Renaming 'testimonial' column to 'message'...</p>\n";
        $pdo->exec("ALTER TABLE testimonials CHANGE COLUMN testimonial message TEXT NOT NULL");
        echo "<p>✅ Renamed testimonial column to message</p>\n";
    } elseif (in_array('testimonial', $columnNames) && in_array('message', $columnNames)) {
        echo "<p>🔄 Merging testimonial and message columns...</p>\n";
        $pdo->exec("UPDATE testimonials SET message = COALESCE(NULLIF(message, ''), testimonial) WHERE message IS NULL OR message = ''");
        $pdo->exec("ALTER TABLE testimonials DROP COLUMN testimonial");
        echo "<p>✅ Merged columns successfully</p>\n";
    } else {
        echo "<p>✅ Message column is correct</p>\n";
    }
    
    // Check and add rating column
    if (!in_array('rating', $columnNames)) {
        echo "<p>🔄 Adding rating column...</p>\n";
        $pdo->exec("ALTER TABLE testimonials ADD COLUMN rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5) AFTER message");
        echo "<p>✅ Added rating column</p>\n";
    } else {
        echo "<p>✅ Rating column already exists</p>\n";
    }
    
    echo "<h2>Step 3: Add Foreign Key Constraints</h2>\n";
    
    // Check if foreign key exists
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'testimonials' 
        AND COLUMN_NAME = 'user_id' 
        AND REFERENCED_TABLE_NAME = 'users'
    ");
    $fkExists = $stmt->fetch();
    
    if (!$fkExists) {
        echo "<p>🔄 Adding foreign key constraint...</p>\n";
        try {
            $pdo->exec("ALTER TABLE testimonials ADD CONSTRAINT fk_testimonials_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
            echo "<p>✅ Foreign key constraint added</p>\n";
        } catch (PDOException $e) {
            echo "<p>⚠️ Foreign key constraint already exists or couldn't be added: " . $e->getMessage() . "</p>\n";
        }
    } else {
        echo "<p>✅ Foreign key constraint already exists</p>\n";
    }
    
    echo "<h2>Step 4: Create User Sessions Table</h2>\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_sessions'");
    $userSessionsExists = $stmt->fetch();
    
    if (!$userSessionsExists) {
        echo "<p>🔄 Creating user_sessions table...</p>\n";
        $createUserSessionsTable = "
        CREATE TABLE user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $pdo->exec($createUserSessionsTable);
        echo "<p>✅ User sessions table created</p>\n";
    } else {
        echo "<p>✅ User sessions table already exists</p>\n";
    }
    
    echo "<h2>Step 5: Database Status Summary</h2>\n";
    
    // Show final table structure
    $stmt = $pdo->query("DESCRIBE testimonials");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Final Testimonials Table Structure:</h3>\n";
    echo "<ul>\n";
    foreach ($finalColumns as $column) {
        echo "<li><strong>" . $column['Field'] . "</strong> - " . $column['Type'] . 
             ($column['Null'] === 'YES' ? ' (nullable)' : ' (required)') . "</li>\n";
    }
    echo "</ul>\n";
    
    // Count data
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM testimonials");
    $totalTestimonials = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    echo "<h3>Data Summary:</h3>\n";
    echo "<ul>\n";
    echo "<li>Total testimonials: $totalTestimonials</li>\n";
    echo "<li>Total users: $totalUsers</li>\n";
    echo "</ul>\n";
    
    echo "<h2>✅ Migration Complete!</h2>\n";
    echo "<p><strong>Your database is now ready for the user testimonial system.</strong></p>\n";
    echo "<p><a href='user/register.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test User Registration</a></p>\n";
    echo "<p><a href='admin/dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Test Admin Dashboard</a></p>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Migration failed: " . $e->getMessage() . "</p>\n";
    echo "<p>Please check your database connection and try again.</p>\n";
}
?>



