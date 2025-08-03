<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    // Delete session from database
    if (isset($_SESSION['session_token'])) {
        $stmt = $pdo->prepare("DELETE FROM admin_sessions WHERE session_token = ?");
        $stmt->execute([$_SESSION['session_token']]);
    }
}

session_destroy();
header('Location: login.php?logout=1');
exit;
?>
