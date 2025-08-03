<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h1>Database Table Inspection</h1>";

try {
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'portfolio_experience'");
    $expTableExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'portfolio_education'");
    $eduTableExists = $stmt->rowCount() > 0;
    
    echo "<h2>Table Existence:</h2>";
    echo "<p>portfolio_experience: " . ($expTableExists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
    echo "<p>portfolio_education: " . ($eduTableExists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
    
    if ($expTableExists) {
        echo "<h2>Experience Table Structure:</h2>";
        $stmt = $pdo->query("DESCRIBE portfolio_experience");
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td><td>{$row['Extra']}</td></tr>";
        }
        echo "</table>";
        
        echo "<h3>Experience Data:</h3>";
        $stmt = $pdo->query("SELECT id, company, position, timeframe FROM portfolio_experience ORDER BY id");
        echo "<table border='1'><tr><th>ID</th><th>Company</th><th>Position</th><th>Timeframe</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['company']}</td><td>{$row['position']}</td><td>{$row['timeframe']}</td></tr>";
        }
        echo "</table>";
    }
    
    if ($eduTableExists) {
        echo "<h2>Education Table Structure:</h2>";
        $stmt = $pdo->query("DESCRIBE portfolio_education");
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Extra']}</td></tr>";
        }
        echo "</table>";
        
        echo "<h3>Education Data:</h3>";
        $stmt = $pdo->query("SELECT id, institution, qualification, timeframe FROM portfolio_education ORDER BY id");
        echo "<table border='1'><tr><th>ID</th><th>Institution</th><th>Qualification</th><th>Timeframe</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['institution']}</td><td>{$row['qualification']}</td><td>{$row['timeframe']}</td></tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>
