<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h2>LinkedIn Profile Integration Migration</h2>\n";

try {
    // Check if linkedin_profile column exists in users table
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    echo "<h3>Step 1: Update Users Table</h3>\n";
    
    if (!in_array('linkedin_profile', $columnNames)) {
        echo "<p>🔄 Adding linkedin_profile column to users table...</p>\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN linkedin_profile VARCHAR(255) NULL AFTER email");
        echo "<p>✅ Added linkedin_profile column</p>\n";
    } else {
        echo "<p>✅ linkedin_profile column already exists</p>\n";
    }
    
    if (!in_array('profile_picture_url', $columnNames)) {
        echo "<p>🔄 Adding profile_picture_url column to users table...</p>\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture_url VARCHAR(500) NULL AFTER linkedin_profile");
        echo "<p>✅ Added profile_picture_url column</p>\n";
    } else {
        echo "<p>✅ profile_picture_url column already exists</p>\n";
    }
    
    // Check if linkedin_profile column exists in testimonials table
    $stmt = $pdo->query("DESCRIBE testimonials");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $testimonialColumns = array_column($columns, 'Field');
    
    echo "<h3>Step 2: Update Testimonials Table</h3>\n";
    
    if (!in_array('linkedin_profile', $testimonialColumns)) {
        echo "<p>🔄 Adding linkedin_profile column to testimonials table...</p>\n";
        $pdo->exec("ALTER TABLE testimonials ADD COLUMN linkedin_profile VARCHAR(255) NULL AFTER email");
        echo "<p>✅ Added linkedin_profile column to testimonials</p>\n";
    } else {
        echo "<p>✅ linkedin_profile column already exists in testimonials</p>\n";
    }
    
    echo "<h3>Step 3: Verification</h3>\n";
    
    // Show updated table structures
    $stmt = $pdo->query("DESCRIBE users");
    $userColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h4>Users Table Structure:</h4>\n";
    echo "<pre>";
    foreach ($userColumns as $column) {
        echo sprintf("%-20s %-15s %s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL'
        );
    }
    echo "</pre>";
    
    $stmt = $pdo->query("DESCRIBE testimonials");
    $testimonialColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h4>Testimonials Table Structure:</h4>\n";
    echo "<pre>";
    foreach ($testimonialColumns as $column) {
        echo sprintf("%-20s %-15s %s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL'
        );
    }
    echo "</pre>";
    
    echo "<h3>✅ LinkedIn Profile Integration Migration Complete!</h3>\n";
    echo "<p>📋 <strong>Next Steps:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Update user registration form to include LinkedIn profile</li>\n";
    echo "<li>Update user dashboard to allow LinkedIn profile editing</li>\n";
    echo "<li>Update testimonial submission to include LinkedIn profile</li>\n";
    echo "<li>Update testimonial display to use LinkedIn profile pictures</li>\n";
    echo "</ul>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>\n";
}
?>



