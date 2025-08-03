<?php
/**
 * Secure Email Configuration
 * Loads email settings from encrypted E.env file or fallback configuration
 * Use admin/Debug/email_encryption_setup.php to set up encrypted email configuration
 */

require_once 'SecureEmailLoader.php';

// Load configuration using the secure loader
return SecureEmailLoader::loadConfig();
?>
