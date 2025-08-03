<?php
session_start();
require_once '../config/database.php';
require_once '../includes/linkedin_helper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $linkedin_profile = trim($_POST['linkedin_profile'] ?? '');
        
        if (empty($first_name) || empty($last_name)) {
            $error = 'First name and last name are required.';
        } elseif (!empty($linkedin_profile) && !filter_var($linkedin_profile, FILTER_VALIDATE_URL)) {
            $error = 'Please enter a valid LinkedIn profile URL.';
        } elseif (!empty($linkedin_profile) && !preg_match('/linkedin\.com\/in\//', $linkedin_profile)) {
            $error = 'LinkedIn profile must be a valid LinkedIn profile URL.';
        } else {
            // Handle profile picture upload
            $avatar_path = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/avatars/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_info = pathinfo($_FILES['profile_picture']['name']);
                $extension = strtolower($file_info['extension']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($extension, $allowed_extensions) && $_FILES['profile_picture']['size'] <= 5 * 1024 * 1024) {
                    $filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        $avatar_path = 'uploads/avatars/' . $filename;
                        
                        // Delete old avatar if it exists
                        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $old_avatar = $stmt->fetchColumn();
                        if ($old_avatar && file_exists('../' . $old_avatar)) {
                            unlink('../' . $old_avatar);
                        }
                    } else {
                        $error = 'Failed to upload profile picture. Please try again.';
                    }
                } else {
                    $error = 'Invalid file type or size. Please upload a JPG, PNG, or WebP image under 5MB.';
                }
            }
            
            if (!$error) {
                try {
                    if ($avatar_path) {
                        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, linkedin_profile = ?, avatar = ? WHERE id = ?");
                        $success = $stmt->execute([$first_name, $last_name, $linkedin_profile ?: null, $avatar_path, $user_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, linkedin_profile = ? WHERE id = ?");
                        $success = $stmt->execute([$first_name, $last_name, $linkedin_profile ?: null, $user_id]);
                    }
                    
                    if ($success) {
                        $message = 'Profile updated successfully!';
                        $_SESSION['first_name'] = $first_name;
                        $_SESSION['last_name'] = $last_name;
                    } else {
                        $error = 'Failed to update profile. Please try again.';
                    }
                } catch (PDOException $e) {
                    $error = 'Database error. Please try again later.';
                }
            }
        }
    } elseif ($action === 'submit_testimonial') {
        $name = trim($_POST['name'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $message_text = trim($_POST['message'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        
        if (empty($name) || empty($message_text) || $rating < 1 || $rating > 5) {
            $error = 'Please fill in all required fields and provide a valid rating (1-5 stars).';
        } else {
            try {
                // Get user's email and LinkedIn profile for the testimonial
                $stmt = $pdo->prepare("SELECT email, linkedin_profile FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $pdo->prepare("
                    INSERT INTO testimonials (user_id, name, company, position, email, linkedin_profile, message, rating, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                
                if ($stmt->execute([$user_id, $name, $company, $position, $user_info['email'], $user_info['linkedin_profile'], $message_text, $rating])) {
                    $message = 'Your testimonial has been submitted and is awaiting approval.';
                } else {
                    $error = 'Failed to submit testimonial. Please try again.';
                }
            } catch (PDOException $e) {
                $error = 'Database error. Please try again later.';
            }
        }
    } elseif ($action === 'delete_testimonial') {
        $testimonial_id = (int)($_POST['testimonial_id'] ?? 0);
        
        try {
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$testimonial_id, $user_id])) {
                $message = 'Testimonial deleted successfully.';
            } else {
                $error = 'Failed to delete testimonial.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
        }
    }
}

// Get user's testimonials
try {
    $stmt = $pdo->prepare("
        SELECT id, name, company, message, rating, status, created_at 
        FROM testimonials 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $testimonials = [];
    $error = 'Failed to load testimonials.';
}

// Get user data for profile form
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, linkedin_profile FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $user_data = [];
}

// Get user stats
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
        FROM testimonials 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Luthor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 1.5rem;
        }
        
        .header-nav a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 3px;
            background: rgba(255,255,255,0.2);
        }
        
        .header-nav a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-total { color: #6c757d; }
        .stat-approved { color: #28a745; }
        .stat-pending { color: #ffc107; }
        .stat-rejected { color: #dc3545; }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .testimonials-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .field-help {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="url"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        textarea {
            height: 120px;
            resize: vertical;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .testimonial-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #ddd;
        }
        
        .testimonial-approved {
            border-left-color: #28a745;
        }
        
        .testimonial-pending {
            border-left-color: #ffc107;
        }
        
        .testimonial-rejected {
            border-left-color: #dc3545;
        }
        
        .testimonial-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        
        .no-testimonials {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        .required {
            color: #dc3545;
        }
        
        .profile-pic-info {
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        
        .profile-pic-info.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .profile-pic-info.info {
            background: #cce7ff;
            color: #004085;
            border: 1px solid #99d3ff;
        }
        
        .profile-pic-info:not(.success):not(.info) {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! 👋</h1>
        <div class="header-nav">
            <span>@<?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../index.php">🏠 Home</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number stat-total"><?php echo $stats['total']; ?></div>
                <div>Total Testimonials</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-approved"><?php echo $stats['approved']; ?></div>
                <div>Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-pending"><?php echo $stats['pending']; ?></div>
                <div>Pending Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-rejected"><?php echo $stats['rejected']; ?></div>
                <div>Rejected</div>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="main-content">
            <div class="form-section">
                <h2>� Profile Settings</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="profile_picture">Profile Picture (Optional)</label>
                        <input type="file" id="profile_picture" name="profile_picture" 
                               accept="image/jpeg,image/jpg,image/png,image/webp">
                        <div class="field-help">Upload your profile picture (JPG, PNG, WebP, max 5MB). This will override your LinkedIn profile picture.</div>
                        <?php echo getProfilePictureMessage($user_data); ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="linkedin_profile">LinkedIn Profile (Optional)</label>
                        <input type="url" id="linkedin_profile" name="linkedin_profile" 
                               value="<?php echo htmlspecialchars($user_data['linkedin_profile'] ?? ''); ?>" 
                               placeholder="https://linkedin.com/in/your-profile">
                        <div class="field-help">If no profile picture is uploaded, we'll try to fetch your LinkedIn profile picture or use your initials</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <div class="form-section">
                <h2>�📝 Submit New Testimonial</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="submit_testimonial">
                    
                    <div class="form-group">
                        <label for="name">Your Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="company">Company/Organization</label>
                        <input type="text" id="company" name="company" placeholder="Optional">
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Your Position/Title</label>
                        <input type="text" id="position" name="position" placeholder="e.g., IT Manager, Software Developer">
                    </div>
                    
                    <div class="form-group">
                        <label for="rating">Rating <span class="required">*</span></label>
                        <select id="rating" name="rating" required>
                            <option value="">Select rating...</option>
                            <option value="5">⭐⭐⭐⭐⭐ (5 stars)</option>
                            <option value="4">⭐⭐⭐⭐ (4 stars)</option>
                            <option value="3">⭐⭐⭐ (3 stars)</option>
                            <option value="2">⭐⭐ (2 stars)</option>
                            <option value="1">⭐ (1 star)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Testimonial <span class="required">*</span></label>
                        <textarea id="message" name="message" placeholder="Share your experience..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Submit Testimonial</button>
                </form>
            </div>
            
            <div class="testimonials-section">
                <h2>📋 My Testimonials</h2>
                
                <?php if (empty($testimonials)): ?>
                    <div class="no-testimonials">
                        <h3>No testimonials yet</h3>
                        <p>Submit your first testimonial using the form on the left!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-card testimonial-<?php echo $testimonial['status']; ?>">
                            <div class="testimonial-header">
                                <span class="status-badge status-<?php echo $testimonial['status']; ?>">
                                    <?php echo ucfirst($testimonial['status']); ?>
                                </span>
                                <small><?php echo date('M j, Y', strtotime($testimonial['created_at'])); ?></small>
                            </div>
                            
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php echo $i <= $testimonial['rating'] ? '⭐' : '☆'; ?>
                                <?php endfor; ?>
                            </div>
                            
                            <h4><?php echo htmlspecialchars($testimonial['name']); ?>
                                <?php if ($testimonial['company']): ?>
                                    <small> - <?php echo htmlspecialchars($testimonial['company']); ?></small>
                                <?php endif; ?>
                            </h4>
                            
                            <p><?php echo nl2br(htmlspecialchars($testimonial['message'])); ?></p>
                            
                            <?php if ($testimonial['status'] === 'pending' || $testimonial['status'] === 'rejected'): ?>
                                <div style="margin-top: 1rem; display: flex; gap: 10px;">
                                    <a href="edit_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-sm" style="background: #17a2b8; text-decoration: none;">✏️ Edit</a>
                                    <form method="POST" action="" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this testimonial?')">
                                        <input type="hidden" name="action" value="delete_testimonial">
                                        <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
