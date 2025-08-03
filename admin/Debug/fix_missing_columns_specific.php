<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h2>🔧 Fixing Specific Missing Columns</h2>";
echo "<p>Addressing the 2 failed tests and 1 warning from debug report</p>";

try {
    // Check current table structure first
    echo "<h3>📋 Current Users Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existing_columns = [];
    echo "<ul>";
    foreach ($columns as $column) {
        $existing_columns[] = $column['Field'];
        echo "<li><strong>{$column['Field']}</strong> - {$column['Type']} " . 
             ($column['Null'] === 'YES' ? '(nullable)' : '(not null)') . "</li>";
    }
    echo "</ul>";
    
    $fixed_count = 0;
    $errors = [];
    
    // Fix 1: Add email_verified_at column if missing
    if (!in_array('email_verified_at', $existing_columns)) {
        try {
            echo "<h3>🔧 Adding email_verified_at column...</h3>";
            $pdo->exec("ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL");
            echo "<p style='color: green;'>✅ Successfully added email_verified_at column</p>";
            $fixed_count++;
        } catch (PDOException $e) {
            $error = "Failed to add email_verified_at: " . $e->getMessage();
            $errors[] = $error;
            echo "<p style='color: red;'>❌ $error</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ email_verified_at column already exists</p>";
    }
    
    // Fix 2: Add updated_at column if missing
    if (!in_array('updated_at', $existing_columns)) {
        try {
            echo "<h3>🔧 Adding updated_at column...</h3>";
            $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "<p style='color: green;'>✅ Successfully added updated_at column with auto-update trigger</p>";
            $fixed_count++;
        } catch (PDOException $e) {
            $error = "Failed to add updated_at: " . $e->getMessage();
            $errors[] = $error;
            echo "<p style='color: red;'>❌ $error</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ updated_at column already exists</p>";
    }
    
    // Info about avatar column (warning)
    if (in_array('avatar', $existing_columns)) {
        echo "<h3>⚠️ Avatar Column Information:</h3>";
        echo "<p style='color: orange;'>The 'avatar' column exists but was flagged as unexpected. This might be intentional for user profile images.</p>";
        echo "<p>If you want to remove it, you can run: <code>ALTER TABLE users DROP COLUMN avatar;</code></p>";
    }
    
    echo "<hr>";
    echo "<h3>📊 Summary:</h3>";
    echo "<ul>";
    echo "<li><strong>Columns Fixed:</strong> $fixed_count</li>";
    echo "<li><strong>Errors:</strong> " . count($errors) . "</li>";
    echo "</ul>";
    
    if (count($errors) > 0) {
        echo "<h4>❌ Errors encountered:</h4>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li style='color: red;'>$error</li>";
        }
        echo "</ul>";
    }
    
    // Show updated table structure
    echo "<h3>📋 Updated Users Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $updated_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    foreach ($updated_columns as $column) {
        echo "<tr>";
        echo "<td><strong>{$column['Field']}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><div style='background-color: #e8f5e8; padding: 10px; border: 1px solid #4CAF50; border-radius: 5px;'>";
    echo "<h4>🎯 Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Run the database debug test again to verify the fixes</li>";
    echo "<li>Check if any existing user records need updated_at timestamps populated</li>";
    echo "<li>Test email verification functionality with the new email_verified_at column</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 5px;'>";
    echo "<h4>❌ Database Connection Error:</h4>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
code { background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style>

<div style="margin-top: 20px; text-align: center;">
    <a href="../debug_dashboard.php" style="background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        ← Back to Debug Dashboard
    </a>
</div>
