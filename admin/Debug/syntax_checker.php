<?php
require_once '../../config/admin_auth_check.php';

echo "<h1>🔍 PHP Syntax Checker</h1>";
echo "<p>Checking all PHP files for syntax errors...</p>";

function checkSyntax($file) {
    $output = [];
    $returnVar = 0;
    exec('C:\xampp\php\php.exe -l "' . $file . '" 2>&1', $output, $returnVar);
    return [$returnVar === 0, implode("\n", $output)];
}

function scanDirectory($dir, $baseDir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

$baseDir = realpath(__DIR__ . '/../../');
$phpFiles = scanDirectory($baseDir, $baseDir);

$errorCount = 0;
$successCount = 0;
$errors = [];

echo "<div style='margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
echo "<h2>📊 Checking " . count($phpFiles) . " PHP files...</h2>";
echo "</div>";

foreach ($phpFiles as $file) {
    $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $file);
    $relativePath = str_replace('\\', '/', $relativePath);
    
    list($isValid, $output) = checkSyntax($file);
    
    if ($isValid) {
        echo "<div style='color: green; margin: 5px 0;'>✅ {$relativePath}</div>";
        $successCount++;
    } else {
        echo "<div style='color: red; margin: 5px 0; background: #fff5f5; padding: 10px; border-left: 4px solid #red;'>";
        echo "<strong>❌ {$relativePath}</strong><br>";
        echo "<code style='background: #f1f1f1; padding: 5px; display: block; margin-top: 5px;'>" . htmlspecialchars($output) . "</code>";
        echo "</div>";
        $errorCount++;
        $errors[] = ['file' => $relativePath, 'error' => $output];
    }
    
    // Flush output for real-time display
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

echo "<div style='margin: 20px 0; padding: 15px; background: " . ($errorCount > 0 ? '#fff5f5' : '#f0f9ff') . "; border-radius: 5px;'>";
echo "<h2>📋 Summary</h2>";
echo "<p><strong>✅ Files with no syntax errors:</strong> {$successCount}</p>";
echo "<p><strong>❌ Files with syntax errors:</strong> {$errorCount}</p>";

if ($errorCount > 0) {
    echo "<h3>🚨 Files that need attention:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li><strong>{$error['file']}</strong></li>";
    }
    echo "</ul>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='../debug_dashboard.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Back to Debug Dashboard</a>";
echo "</div>";
?>
