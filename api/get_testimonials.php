<?php
require_once 'config/database.php';
require_once 'includes/linkedin_helper.php';

// Get approved testimonials
try {
    $stmt = $pdo->prepare("
        SELECT t.name, t.company, t.position, t.message as testimonial, t.avatar, 
               t.linkedin_profile, u.linkedin_profile as user_linkedin, u.first_name, u.last_name
        FROM testimonials t 
        LEFT JOIN users u ON t.user_id = u.id 
        WHERE t.status = 'approved' 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add profile picture URLs to each testimonial
    foreach ($testimonials as &$testimonial) {
        $testimonial['profile_picture_url'] = getUserProfilePicture($testimonial);
        $testimonial['linkedin_url'] = $testimonial['linkedin_profile'] ?: $testimonial['user_linkedin'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($testimonials);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error']);
}
?>
