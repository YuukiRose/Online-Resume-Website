<?php
require_once '../../config/database.php';

echo "<h1>Database Column Standardization</h1>\n";

try {
    // Check current column structure
    $stmt = $pdo->query("DESCRIBE testimonials");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Testimonials Table Structure:</h2>\n";
    $hasMessage = false;
    $hasTestimonial = false;
    
    foreach ($columns as $column) {
        echo "• " . $column['Field'] . " (" . $column['Type'] . ")<br>\n";
        if ($column['Field'] === 'message') $hasMessage = true;
        if ($column['Field'] === 'testimonial') $hasTestimonial = true;
    }
    
    echo "<br>\n";
    
    // Standardize column names
    if ($hasTestimonial && !$hasMessage) {
        echo "<p>🔄 Found 'testimonial' column, renaming to 'message' for consistency...</p>\n";
        $pdo->exec("ALTER TABLE testimonials CHANGE COLUMN testimonial message TEXT NOT NULL");
        echo "<p>✅ Renamed 'testimonial' column to 'message'</p>\n";
    } elseif ($hasMessage && $hasTestimonial) {
        echo "<p>⚠️ Both 'message' and 'testimonial' columns exist. Merging data...</p>\n";
        $pdo->exec("UPDATE testimonials SET message = testimonial WHERE message IS NULL OR message = ''");
        $pdo->exec("ALTER TABLE testimonials DROP COLUMN testimonial");
        echo "<p>✅ Merged columns and kept 'message'</p>\n";
    } elseif ($hasMessage) {
        echo "<p>✅ 'message' column already exists and is correct</p>\n";
    } else {
        echo "<p>❌ Neither 'message' nor 'testimonial' column found</p>\n";
    }
    
    // Check if rating column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM testimonials LIKE 'rating'");
    $ratingExists = $stmt->fetch();
    
    if (!$ratingExists) {
        echo "<p>🔄 Adding 'rating' column...</p>\n";
        $pdo->exec("ALTER TABLE testimonials ADD COLUMN rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5) AFTER message");
        echo "<p>✅ Added 'rating' column</p>\n";
    } else {
        echo "<p>✅ 'rating' column already exists</p>\n";
    }
    
    echo "<h2>✅ Database standardization complete!</h2>\n";
    echo "<p><a href='test_user_dashboard.php'>Test User Dashboard</a></p>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>\n";
}
?>



