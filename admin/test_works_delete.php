<?php
require_once '../config/database.php';

echo "Testing Portfolio Works Database Operations\n\n";

try {
    // Check if portfolio_works table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio_works");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current works count: " . $result['count'] . "\n";
    
    // Show all works
    $stmt = $pdo->query("SELECT id, title, category FROM portfolio_works ORDER BY sort_order");
    $works = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent works in database:\n";
    foreach ($works as $work) {
        echo "ID: {$work['id']}, Title: {$work['title']}, Category: {$work['category']}\n";
    }
    
    // Test if we can delete (simulate the delete operation)
    if (count($works) > 0) {
        $firstWorkId = $works[0]['id'];
        echo "\nTesting delete operation on work ID: $firstWorkId\n";
        
        // Simulate the same delete logic from the form
        $table = 'portfolio_works';
        $allowedTables = ['portfolio_skills', 'portfolio_experience', 'portfolio_education', 'portfolio_works'];
        
        if (in_array($table, $allowedTables)) {
            echo "Table is allowed for deletion.\n";
            
            // Check if the work exists before deletion
            $checkStmt = $pdo->prepare("SELECT title FROM portfolio_works WHERE id = ?");
            $checkStmt->execute([$firstWorkId]);
            $workBeforeDelete = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($workBeforeDelete) {
                echo "Work exists before deletion: " . $workBeforeDelete['title'] . "\n";
                
                // Perform the delete (commented out for safety)
                // $stmt = $pdo->prepare("DELETE FROM portfolio_works WHERE id = ?");
                // $stmt->execute([$firstWorkId]);
                // echo "Delete operation would be executed here.\n";
                
                echo "Delete operation simulated successfully (not actually executed).\n";
            } else {
                echo "Work with ID $firstWorkId not found.\n";
            }
        } else {
            echo "Table NOT allowed for deletion.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
