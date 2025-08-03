<?php
/**
 * Email Configuration Test
 * Tests the secure email configuration system
 */

require_once '../../config/SecureEmailLoader.php';

header('Content-Type: application/json');

try {
    // Get configuration status
    $status = SecureEmailLoader::getStatus();
    
    // Try to load configuration
    $config = SecureEmailLoader::loadConfig();
    
    // Hide password for security
    $safeConfig = $config;
    if (isset($safeConfig['smtp_password'])) {
        $safeConfig['smtp_password'] = '***HIDDEN***';
    }
    
    $response = [
        'success' => true,
        'status' => $status,
        'config' => $safeConfig,
        'message' => 'Email configuration loaded successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to load email configuration',
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
