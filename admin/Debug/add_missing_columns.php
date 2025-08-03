<?php
// Add missing columns to users table
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h2>Adding Missing Columns to Users Table</h2>";

try {
    echo "<h3>Checking Current Table Structure:</h3>";
    
    // Get current table structure
    $stmt = $pdo->query("DESCRIBE users");
    $currentColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Current columns in users table:</p>";
    echo "<ul>";
    foreach ($currentColumns as $column) {
        echo "<li>" . htmlspecialchars($column['Field']) . " (" . htmlspecialchars($column['Type']) . ")</li>";
    }
    echo "</ul>";
    
    // Check which columns are missing
    $columnNames = array_column($currentColumns, 'Field');
    $missingColumns = [];
    
    if (!in_array('profile_picture_url', $columnNames)) {
        $missingColumns[] = 'profile_picture_url';
    }
    
    if (!in_array('email_verified_at', $columnNames)) {
        $missingColumns[] = 'email_verified_at';
    }
    
    if (!in_array('updated_at', $columnNames)) {
        $missingColumns[] = 'updated_at';
    }
    
    if (empty($missingColumns)) {
        echo "<h3>✅ All columns already exist!</h3>";
        echo "<p>The users table already has all required columns.</p>";
    } else {
        echo "<h3>Adding Missing Columns:</h3>";
        
        foreach ($missingColumns as $column) {
            try {
                if ($column === 'profile_picture_url') {
                    $sql = "ALTER TABLE users ADD COLUMN profile_picture_url VARCHAR(255) NULL AFTER linkedin_profile";
                    $pdo->exec($sql);
                    echo "✅ Added profile_picture_url column<br>";
                } elseif ($column === 'email_verified_at') {
                    $sql = "ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL AFTER status";
                    $pdo->exec($sql);
                    echo "✅ Added email_verified_at column<br>";
                } elseif ($column === 'updated_at') {
                    $sql = "ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at";
                    $pdo->exec($sql);
                    echo "✅ Added updated_at column<br>";
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "ℹ️ Column $column already exists<br>";
                } else {
                    echo "❌ Error adding $column: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "<h3>Updated Table Structure:</h3>";
    
    // Get updated table structure
    $stmt = $pdo->query("DESCRIBE users");
    $updatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th style='padding: 8px; text-align: left;'>Column</th>";
    echo "<th style='padding: 8px; text-align: left;'>Type</th>";
    echo "<th style='padding: 8px; text-align: left;'>Null</th>";
    echo "<th style='padding: 8px; text-align: left;'>Key</th>";
    echo "<th style='padding: 8px; text-align: left;'>Default</th>";
    echo "</tr>";
    
    foreach ($updatedColumns as $column) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Database Schema Update Complete!</h3>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li><a href='../admin/Debug/database_debug.php' target='_blank'>Run Database Debug Tool</a> to verify all tests pass</li>";
    echo "<li><a href='../user/login.php' target='_blank'>Test Login System</a></li>";
    echo "<li><a href='../user/register.php' target='_blank'>Test Registration System</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    echo "<p>Make sure XAMPP MySQL is running and the database connection is working.</p>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
