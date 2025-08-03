<?php
session_start();
require_once '../config/database.php';
require_once '../includes/linkedin_helper.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Verify session token
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_sessions WHERE user_id = ? AND session_token = ? AND expires_at > NOW()");
    $stmt->execute([$_SESSION['admin_user_id'], $_SESSION['session_token']]);
    if (!$stmt->fetch()) {
        session_destroy();
        header('Location: login.php?error=1');
        exit;
    }
} catch (PDOException $e) {
    session_destroy();
    header('Location: login.php?error=1');
    exit;
}

// Handle testimonial actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['testimonial_id'])) {
        $action = $_POST['action'];
        $testimonial_id = (int)$_POST['testimonial_id'];
        
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
            $stmt->execute([$testimonial_id]);
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE testimonials SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$testimonial_id]);
        } elseif ($action === 'delete') {
            // Delete avatar file if exists
            $stmt = $pdo->prepare("SELECT avatar FROM testimonials WHERE id = ?");
            $stmt->execute([$testimonial_id]);
            $testimonial = $stmt->fetch();
            if ($testimonial && $testimonial['avatar'] && file_exists('../' . $testimonial['avatar'])) {
                unlink('../' . $testimonial['avatar']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->execute([$testimonial_id]);
        }
    }
}

// Get testimonials
$status_filter = $_GET['status'] ?? 'pending';
$stmt = $pdo->prepare("SELECT t.*, u.first_name, u.last_name, u.linkedin_profile as user_linkedin FROM testimonials t LEFT JOIN users u ON t.user_id = u.id WHERE t.status = ? ORDER BY t.created_at DESC");
$stmt->execute([$status_filter]);
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM testimonials GROUP BY status");
$counts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $counts[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Testimonials</title>
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
            background: #333;
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
            background: #555;
        }
        
        .header-nav a:hover {
            background: #666;
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
            color: #667eea;
        }
        
        .filters {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            background: #e9ecef;
        }
        
        .filter-btn.active {
            background: #667eea;
            color: white;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1.5rem;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .testimonial-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .default-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 1rem;
        }
        
        .testimonial-content {
            margin-bottom: 1rem;
            color: #555;
            line-height: 1.6;
        }
        
        .testimonial-meta {
            font-size: 0.9rem;
            color: #777;
            margin-bottom: 1rem;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #ffc107;
            color: #856404;
        }
        
        .status-approved {
            background: #28a745;
            color: white;
        }
        
        .status-rejected {
            background: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #777;
        }
        
        .linkedin-link {
            margin-left: 0.5rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
        }
        
        .linkedin-link:hover {
            opacity: 0.8;
        }
        
        .linkedin-profile-link {
            color: #0077b5;
            text-decoration: none;
            font-weight: 500;
        }
        
        .linkedin-profile-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard - Testimonials</h1>
        <div class="header-nav">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="debug_dashboard.php">🛠️ Debug Tools</a>
            <a href="manage_users.php">👥 Manage Users</a>
            <a href="create_user.php">➕ Create User</a>
            <a href="../index.php">🌐 View Site</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $counts['pending'] ?? 0; ?></div>
                <div>Pending Approval</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $counts['approved'] ?? 0; ?></div>
                <div>Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $counts['rejected'] ?? 0; ?></div>
                <div>Rejected</div>
            </div>
        </div>
        
        <div class="filters">
            <div class="filter-buttons">
                <a href="?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                    Pending (<?php echo $counts['pending'] ?? 0; ?>)
                </a>
                <a href="?status=approved" class="filter-btn <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">
                    Approved (<?php echo $counts['approved'] ?? 0; ?>)
                </a>
                <a href="?status=rejected" class="filter-btn <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
                    Rejected (<?php echo $counts['rejected'] ?? 0; ?>)
                </a>
            </div>
        </div>
        
        <?php if (empty($testimonials)): ?>
            <div class="empty-state">
                <h3>No testimonials found</h3>
                <p>There are no <?php echo $status_filter; ?> testimonials at the moment.</p>
            </div>
        <?php else: ?>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <?php 
                            $profile_picture_url = getUserProfilePicture($testimonial);
                            if ($profile_picture_url && ($testimonial['avatar'] || $testimonial['linkedin_profile'] || $testimonial['user_linkedin'])): 
                            ?>
                                <img src="<?php echo htmlspecialchars($profile_picture_url); ?>" alt="Profile Picture" class="avatar">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                                <?php if ($testimonial['linkedin_profile'] || $testimonial['user_linkedin']): ?>
                                    <a href="<?php echo htmlspecialchars($testimonial['linkedin_profile'] ?: $testimonial['user_linkedin']); ?>" 
                                       target="_blank" class="linkedin-link" title="View LinkedIn Profile">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#0077b5">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <br>
                                <small><?php echo htmlspecialchars($testimonial['position'] ?: 'No position'); ?></small>
                                <?php if ($testimonial['company']): ?>
                                    <br><small><?php echo htmlspecialchars($testimonial['company']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="testimonial-content">
                            <?php echo nl2br(htmlspecialchars($testimonial['message'])); ?>
                        </div>
                        
                        <div class="testimonial-meta">
                            <span class="status-badge status-<?php echo $testimonial['status']; ?>">
                                <?php echo $testimonial['status']; ?>
                            </span>
                            <br>
                            Email: <?php echo htmlspecialchars($testimonial['email']); ?>
                            <?php if ($testimonial['linkedin_profile'] || $testimonial['user_linkedin']): ?>
                                <br>LinkedIn: <a href="<?php echo htmlspecialchars($testimonial['linkedin_profile'] ?: $testimonial['user_linkedin']); ?>" 
                                                 target="_blank" class="linkedin-profile-link">View Profile</a>
                            <?php endif; ?>
                            <br>
                            Submitted: <?php echo date('M j, Y g:i A', strtotime($testimonial['created_at'])); ?>
                        </div>
                        
                        <div class="actions">
                            <?php if ($testimonial['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-approve">Approve</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-reject">Reject</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this testimonial?')">
                                <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
