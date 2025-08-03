<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h1>User Dashboard Test Script</h1>\n";

try {
    // Check if we have any users
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $userCount = $stmt->fetch()['user_count'];
    echo "<p>Total users in database: $userCount</p>\n";
    
    // Check if we have any testimonials
    $stmt = $pdo->query("SELECT COUNT(*) as testimonial_count FROM testimonials");
    $testimonialCount = $stmt->fetch()['testimonial_count'];
    echo "<p>Total testimonials in database: $testimonialCount</p>\n";
    
    // Check testimonials with user_id
    $stmt = $pdo->query("SELECT COUNT(*) as linked_count FROM testimonials WHERE user_id IS NOT NULL");
    $linkedCount = $stmt->fetch()['linked_count'];
    echo "<p>Testimonials linked to users: $linkedCount</p>\n";
    
    // Check testimonials without user_id
    $stmt = $pdo->query("SELECT COUNT(*) as orphan_count FROM testimonials WHERE user_id IS NULL");
    $orphanCount = $stmt->fetch()['orphan_count'];
    echo "<p>Orphaned testimonials (no user_id): $orphanCount</p>\n";
    
    if ($userCount > 0) {
        echo "<h2>Sample User Data:</h2>\n";
        $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, status FROM users LIMIT 3");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>\n";
        print_r($users);
        echo "</pre>\n";
        
        // Test the user dashboard query for the first user
        if (count($users) > 0) {
            $firstUserId = $users[0]['id'];
            echo "<h2>Testing Dashboard Query for User ID: $firstUserId</h2>\n";
            
            $stmt = $pdo->prepare("
                SELECT id, name, company, message, rating, status, created_at 
                FROM testimonials 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$firstUserId]);
            $userTestimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>Testimonials for this user: " . count($userTestimonials) . "</p>\n";
            if (count($userTestimonials) > 0) {
                echo "<pre>\n";
                print_r($userTestimonials);
                echo "</pre>\n";
            }
            
            // Test user stats query
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
                FROM testimonials 
                WHERE user_id = ?
            ");
            $stmt->execute([$firstUserId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>User Stats:</h3>\n";
            echo "<pre>\n";
            print_r($stats);
            echo "</pre>\n";
        }
    }
    
    echo "<h2>Create Test Data</h2>\n";
    echo "<p>If you need test data, create a user account first:</p>\n";
    echo "<p><a href='user/register.php'>Register a Test User</a></p>\n";
    echo "<p><a href='user/login.php'>Login to Test User</a></p>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>\n";
}
?>



