<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $company = trim($_POST['company']);
    $position = trim($_POST['position']);
    $email = trim($_POST['email']);
    $testimonial = trim($_POST['testimonial']);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($testimonial)) {
        header('Location: testimonial_form.php?error=1');
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: testimonial_form.php?error=1');
        exit;
    }
    
    $avatar_path = null;
    
    // Handle file upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (in_array($_FILES['avatar']['type'], $allowed_types) && $_FILES['avatar']['size'] <= $max_size) {
            $upload_dir = 'uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $avatar_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                $avatar_path = null;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO testimonials (name, company, position, email, message, avatar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $company, $position, $email, $testimonial, $avatar_path]);
        
        header('Location: testimonial_form.php?success=1');
        exit;
    } catch (PDOException $e) {
        header('Location: testimonial_form.php?error=1');
        exit;
    }
}

header('Location: testimonial_form.php');
exit;
?>
