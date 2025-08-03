<?php
/**
 * Admin Authentication Check
 * Include this file at the top of any admin-only pages to ensure proper authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    // Redirect to admin login with return URL
    $return_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Luthor/admin/login.php?return_url=$return_url");
    exit;
}

// Additional security: Verify admin session is valid
try {
    require_once __DIR__ . '/database.php';
    
    $stmt = $pdo->prepare("SELECT * FROM admin_sessions WHERE user_id = ? AND session_token = ? AND expires_at > NOW()");
    $stmt->execute([$_SESSION['admin_user_id'], $_SESSION['session_token']]);
    
    if (!$stmt->fetch()) {
        // Invalid session, destroy and redirect
        session_destroy();
        $return_url = urlencode($_SERVER['REQUEST_URI']);
        header("Location: /Luthor/admin/login.php?error=session_expired&return_url=$return_url");
        exit;
    }
} catch (PDOException $e) {
    // Database error, destroy session and redirect
    session_destroy();
    $return_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Luthor/admin/login.php?error=auth_error&return_url=$return_url");
    exit;
}

// Optional: Update last activity timestamp
try {
    $stmt = $pdo->prepare("UPDATE admin_sessions SET last_activity = NOW() WHERE user_id = ? AND session_token = ?");
    $stmt->execute([$_SESSION['admin_user_id'], $_SESSION['session_token']]);
} catch (PDOException $e) {
    // Log error but don't fail authentication
    error_log("Failed to update admin session activity: " . $e->getMessage());
}

// Authentication successful - user can continue
?>
