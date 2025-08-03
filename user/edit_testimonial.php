<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$testimonial_id = (int)($_GET['id'] ?? 0);
$message = '';
$error = '';

// Get testimonial if it exists and belongs to this user
try {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ? AND user_id = ?");
    $stmt->execute([$testimonial_id, $user_id]);
    $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$testimonial) {
        header('Location: dashboard.php');
        exit;
    }
    
    // Only allow editing of pending or rejected testimonials
    if ($testimonial['status'] === 'approved') {
        $error = 'Approved testimonials cannot be edited.';
    }
} catch (PDOException $e) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $testimonial['status'] !== 'approved') {
    $name = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    
    if (empty($name) || empty($message_text) || $rating < 1 || $rating > 5) {
        $error = 'Please fill in all required fields and provide a valid rating (1-5 stars).';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE testimonials 
                SET name = ?, company = ?, position = ?, message = ?, rating = ?, status = 'pending', created_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            
            if ($stmt->execute([$name, $company, $position, $message_text, $rating, $testimonial_id, $user_id])) {
                $message = 'Your testimonial has been updated and is awaiting approval.';
                // Refresh testimonial data
                $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ? AND user_id = ?");
                $stmt->execute([$testimonial_id, $user_id]);
                $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to update testimonial. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Testimonial - Luthor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .navigation {
            margin-bottom: 20px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.3s;
            margin-right: 10px;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            height: 120px;
            resize: vertical;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
        
        .required {
            color: #dc3545;
        }
        
        .status-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #6c757d;
        }
        
        .status-pending {
            border-left-color: #ffc107;
        }
        
        .status-rejected {
            border-left-color: #dc3545;
        }
        
        .status-approved {
            border-left-color: #28a745;
        }
        
        .read-only {
            background: #f8f9fa;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Edit Testimonial</h1>
            <p>Update your testimonial details</p>
        </div>
        
        <div class="content">
            <div class="navigation">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
            
            <div class="status-info status-<?php echo $testimonial['status']; ?>">
                <strong>Status: <?php echo ucfirst($testimonial['status']); ?></strong>
                <?php if ($testimonial['status'] === 'pending'): ?>
                    <p>Your testimonial is awaiting admin approval.</p>
                <?php elseif ($testimonial['status'] === 'rejected'): ?>
                    <p>Your testimonial was rejected. You can edit and resubmit it.</p>
                <?php elseif ($testimonial['status'] === 'approved'): ?>
                    <p>Your testimonial has been approved and is live on the website. Approved testimonials cannot be edited.</p>
                <?php endif; ?>
            </div>
            
            <?php if ($message): ?>
                <div class="message success">✅ <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Your Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($testimonial['name']); ?>" 
                           <?php echo $testimonial['status'] === 'approved' ? 'readonly class="read-only"' : 'required'; ?>>
                </div>
                
                <div class="form-group">
                    <label for="company">Company/Organization</label>
                    <input type="text" id="company" name="company" 
                           value="<?php echo htmlspecialchars($testimonial['company'] ?? ''); ?>"
                           <?php echo $testimonial['status'] === 'approved' ? 'readonly class="read-only"' : ''; ?>
                           placeholder="Optional">
                </div>
                
                <div class="form-group">
                    <label for="position">Position/Title</label>
                    <input type="text" id="position" name="position" 
                           value="<?php echo htmlspecialchars($testimonial['position'] ?? ''); ?>"
                           <?php echo $testimonial['status'] === 'approved' ? 'readonly class="read-only"' : ''; ?>
                           placeholder="Optional">
                </div>
                
                <div class="form-group">
                    <label for="rating">Rating <span class="required">*</span></label>
                    <select id="rating" name="rating" 
                            <?php echo $testimonial['status'] === 'approved' ? 'disabled class="read-only"' : 'required'; ?>>
                        <option value="">Select rating...</option>
                        <option value="5" <?php echo $testimonial['rating'] == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 stars)</option>
                        <option value="4" <?php echo $testimonial['rating'] == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 stars)</option>
                        <option value="3" <?php echo $testimonial['rating'] == 3 ? 'selected' : ''; ?>>⭐⭐⭐ (3 stars)</option>
                        <option value="2" <?php echo $testimonial['rating'] == 2 ? 'selected' : ''; ?>>⭐⭐ (2 stars)</option>
                        <option value="1" <?php echo $testimonial['rating'] == 1 ? 'selected' : ''; ?>>⭐ (1 star)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Testimonial <span class="required">*</span></label>
                    <textarea id="message" name="message" 
                              <?php echo $testimonial['status'] === 'approved' ? 'readonly class="read-only"' : 'required'; ?>
                              placeholder="Share your experience..."><?php echo htmlspecialchars($testimonial['message']); ?></textarea>
                </div>
                
                <?php if ($testimonial['status'] !== 'approved'): ?>
                    <button type="submit" class="btn">💾 Update Testimonial</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
