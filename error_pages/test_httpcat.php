<?php
// Simple test to check if HttpCat is working
try {
    require_once __DIR__ . '/../config/HttpCat.php';
    echo "HttpCat class loaded successfully!<br>";
    
    // Test the show method without exiting
    HttpCat::show(403, "Test", "This is a test", false);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
