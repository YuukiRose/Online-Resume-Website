<?php
session_start();

// Simple security check - allow uploads if user is in admin folder or logged in
$isAdmin = false;
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $isAdmin = true;
} elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', '/admin/') !== false) {
    $isAdmin = true;
}

if (!$isAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

try {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No valid image uploaded');
    }
    
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.');
    }
    
    // Limit file size to 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File too large. Maximum size is 5MB.');
    }
    
    // Create upload directories if they don't exist
    $uploadDir = 'images/';
    $portfolioDir = 'images/portfolio/';
    $galleryDir = 'images/portfolio/gallery/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($portfolioDir)) {
        mkdir($portfolioDir, 0755, true);
    }
    if (!is_dir($galleryDir)) {
        mkdir($galleryDir, 0755, true);
    }
    
    // Determine upload type and generate appropriate filename
    $section = $_POST['section'] ?? 'work';
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    if ($section === 'intro' || $section === 'about') {
        // For intro and about sections, save to main images directory
        $filename = $section . '_' . time() . '_' . uniqid() . '.' . $extension;
        $mainPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $mainPath)) {
            throw new Exception('Failed to save uploaded file');
        }
        
        echo json_encode([
            'success' => true,
            'image_path' => $uploadDir . $filename,
            'filename' => $filename
        ]);
        
    } else {
        // For portfolio works, use the original logic
        $filename = 'work_' . time() . '_' . uniqid() . '.' . $extension;
        
        $mainPath = $portfolioDir . $filename;
        $galleryPath = $galleryDir . 'g-' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $mainPath)) {
            throw new Exception('Failed to save uploaded file');
        }
        
        // Create a gallery version (could be resized in the future)
        copy($mainPath, $galleryPath);
        
        echo json_encode([
            'success' => true,
            'image_path' => $mainPath,
            'gallery_image_path' => $galleryPath,
            'filename' => $filename
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
