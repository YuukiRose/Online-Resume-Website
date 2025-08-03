<?php
/**
 * Enhanced Email Test Script
 * Tests secure email configuration and SMTP functionality
 */

require_once '../config/admin_auth_check.php';
require_once '../config/SecureEmailLoader.php';
require_once '../config/SMTPMailer.php';

echo "<h1>🔐 Secure Email Configuration Test</h1>";

try {
    // Get email configuration status
    $status = SecureEmailLoader::getStatus();
    
    echo "<h2>📊 Configuration Status:</h2>";
    echo "<div style='background: " . ($status['secure'] ? '#d4edda' : '#fff3cd') . "; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>";
    echo "<strong>Status:</strong> " . ucfirst($status['status']) . "<br>";
    echo "<strong>Source:</strong> " . $status['source'] . "<br>";
    echo "<strong>Security:</strong> " . ($status['secure'] ? '🔒 Encrypted' : '⚠️ Unencrypted') . "<br>";
    
    if (isset($status['recommendation'])) {
        echo "<strong>Recommendation:</strong> " . $status['recommendation'] . "<br>";
        echo "<a href='Debug/email_encryption_setup.php' style='color: #007bff;'>🔐 Setup Email Encryption</a>";
    }
    
    if (isset($status['fields_configured'])) {
        echo "<strong>Fields Configured:</strong> " . $status['fields_configured'] . "<br>";
    }
    
    if (isset($status['error'])) {
        echo "<strong>Error:</strong> " . $status['error'] . "<br>";
    }
    
    echo "</div>";
    
    // Get email config
    $emailConfig = SecureEmailLoader::loadConfig();
    
    echo "<h2>📧 Email Configuration:</h2>";
    echo "<ul>";
    echo "<li><strong>SMTP Host:</strong> " . htmlspecialchars($emailConfig['smtp_host']) . "</li>";
    echo "<li><strong>SMTP Port:</strong> " . htmlspecialchars($emailConfig['smtp_port']) . "</li>";
    echo "<li><strong>SMTP Username:</strong> " . htmlspecialchars($emailConfig['smtp_username']) . "</li>";
    echo "<li><strong>SMTP Password:</strong> " . (empty($emailConfig['smtp_password']) ? '❌ NOT SET' : '✅ CONFIGURED') . "</li>";
    echo "<li><strong>From Email:</strong> " . htmlspecialchars($emailConfig['from_email']) . "</li>";
    echo "<li><strong>From Name:</strong> " . htmlspecialchars($emailConfig['from_name']) . "</li>";
    echo "<li><strong>Encryption:</strong> " . htmlspecialchars($emailConfig['encryption']) . "</li>";
    echo "</ul>";
    
    // Test email sending if form is submitted
    if (isset($_POST['test_email']) && !empty($_POST['test_email'])) {
        $test_email = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);
        
        if ($test_email) {
            echo "<h2>Sending Test Email...</h2>";
            
            $subject = "Test Email from Portfolio Admin";
            $html_message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { padding: 20px; background: #f9f9f9; border-radius: 0 0 8px 8px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>✅ Email Test Successful!</h1>
                    </div>
                    <div class='content'>
                        <p>Hello!</p>
                        <p>This is a test email from your Portfolio admin system.</p>
                        <p>If you received this email, your SMTP configuration is working correctly!</p>
                        <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                        <p><strong>Server:</strong> " . $_SERVER['HTTP_HOST'] . "</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mailer = new SMTPMailer($emailConfig);
            $success = $mailer->sendMail($test_email, "Test User", $subject, $html_message, '');
            
            if ($success) {
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "✅ <strong>Email sent successfully!</strong> Check your inbox at $test_email";
                echo "</div>";
                
                // Log successful test
                if (!is_dir('../logs')) mkdir('../logs', 0755, true);
                file_put_contents('../logs/email.log', date('Y-m-d H:i:s') . " - Test email sent successfully to $test_email\n", FILE_APPEND);
            } else {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "❌ <strong>Failed to send email.</strong> Check the error logs for details.";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "⚠️ Please enter a valid email address.";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Configuration Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<h2>Send Test Email</h2>
<form method="POST" action="">
    <div style="margin-bottom: 15px;">
        <label for="test_email" style="display: block; margin-bottom: 5px; font-weight: bold;">Test Email Address:</label>
        <input type="email" id="test_email" name="test_email" required style="width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    <button type="submit" style="background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Send Test Email
    </button>
</form>

<h2>Recent Email Logs</h2>
<?php
$logFile = '../logs/email.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logLines = array_slice(explode("\n", trim($logs)), -10); // Last 10 lines
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 12px;'>";
    foreach ($logLines as $line) {
        if (!empty($line)) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>No email logs found yet.</p>";
}
?>

<div style="margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 5px;">
    <h3>Next Steps:</h3>
    <ol>
        <li>Enter your email address above and click "Send Test Email"</li>
        <li>Check your inbox for the test email</li>
        <li>If successful, the password reset emails will work automatically</li>
        <li>If failed, check your SMTP settings in <code>config/email.php</code></li>
        <li><strong>Remember to delete this file after testing!</strong></li>
    </ol>
</div>

<div style="margin-top: 15px;">
    <a href="../admin/forgot_password.php" style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">
        Test Password Reset
    </a>
    <a href="../admin/login.php" style="background: #667eea; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-left: 10px;">
        Back to Admin
    </a>
</div>
