<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

$message = '';
$error = '';

try {
    // Add new columns to existing tables if they don't exist
    echo "<h2>Migrating Timeline Columns</h2>";
    
    // Check and add columns to experience table
    echo "<h3>Updating Experience Table:</h3>";
    
    try {
        $pdo->exec("ALTER TABLE portfolio_experience ADD COLUMN date_start DATE NULL AFTER position");
        echo "✅ Added date_start column to portfolio_experience<br>";
    } catch (PDOException $e) {
        echo "ℹ️ date_start column already exists in portfolio_experience<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE portfolio_experience ADD COLUMN date_end DATE NULL AFTER date_start");
        echo "✅ Added date_end column to portfolio_experience<br>";
    } catch (PDOException $e) {
        echo "ℹ️ date_end column already exists in portfolio_experience<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE portfolio_experience ADD COLUMN is_present BOOLEAN DEFAULT FALSE AFTER date_end");
        echo "✅ Added is_present column to portfolio_experience<br>";
    } catch (PDOException $e) {
        echo "ℹ️ is_present column already exists in portfolio_experience<br>";
    }
    
    // Check and add columns to education table
    echo "<h3>Updating Education Table:</h3>";
    
    try {
        $pdo->exec("ALTER TABLE portfolio_education ADD COLUMN date_start DATE NULL AFTER qualification");
        echo "✅ Added date_start column to portfolio_education<br>";
    } catch (PDOException $e) {
        echo "ℹ️ date_start column already exists in portfolio_education<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE portfolio_education ADD COLUMN date_end DATE NULL AFTER date_start");
        echo "✅ Added date_end column to portfolio_education<br>";
    } catch (PDOException $e) {
        echo "ℹ️ date_end column already exists in portfolio_education<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE portfolio_education ADD COLUMN is_present BOOLEAN DEFAULT FALSE AFTER date_end");
        echo "✅ Added is_present column to portfolio_education<br>";
    } catch (PDOException $e) {
        echo "ℹ️ is_present column already exists in portfolio_education<br>";
    }
    
    echo "<h3>Migration Completed Successfully!</h3>";
    echo "<p>Your portfolio tables now support timeline management with start dates, end dates, and present status.</p>";
    echo "<p><strong>Note:</strong> The date inputs now use month/year format (YYYY-MM) for better timeline management.</p>";
    echo "<p><strong>Format:</strong> Dates are stored as the first day of the month (YYYY-MM-01) for database compatibility.</p>";
    echo "<p>Existing entries will need their dates to be manually entered through the portfolio editor.</p>";
    
} catch (PDOException $e) {
    $error = "Migration error: " . $e->getMessage();
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($error) . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Timeline Columns Migration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2 { color: #2c3e50; }
        h3 { color: #34495e; margin-top: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Timeline Columns Migration Tool</h1>
    <p>This tool adds the new timeline management columns to your existing portfolio tables.</p>
    
    <div class="back-link" style="margin-top: 30px;">
        <a href="../comprehensive_portfolio_editor.php">← Back to Portfolio Editor</a> | 
        <a href="../debug_dashboard.php">← Back to Debug Dashboard</a>
    </div>
</body>
</html>
