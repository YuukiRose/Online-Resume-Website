<?php
/**
 * SMTP Configuration Checker
 * Diagnose SMTP connection issues
 */
require_once '../config/admin_auth_check.php';

require_once '../config/SecureConfig.php';

echo "<h1>SMTP Configuration Diagnostic</h1>";

try {
    $emailConfig = SecureConfig::getEmailConfig();
    
    echo "<h2>📋 Configuration Check</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
    
    // Check each configuration
    $checks = [
        'SMTP Host' => [$emailConfig['smtp_host'], !empty($emailConfig['smtp_host'])],
        'SMTP Port' => [$emailConfig['smtp_port'], in_array($emailConfig['smtp_port'], [25, 465, 587, 2525])],
        'Username' => [$emailConfig['smtp_username'], !empty($emailConfig['smtp_username'])],
        'Password' => ['***HIDDEN***', !empty($emailConfig['smtp_password'])],
        'From Email' => [$emailConfig['from_email'], filter_var($emailConfig['from_email'], FILTER_VALIDATE_EMAIL)],
        'From Name' => [$emailConfig['from_name'], !empty($emailConfig['from_name'])],
        'Encryption' => [$emailConfig['encryption'], in_array($emailConfig['encryption'], ['ssl', 'tls', 'none'])]
    ];
    
    foreach ($checks as $name => $check) {
        $value = $check[0];
        $valid = $check[1];
        $status = $valid ? '✅ OK' : '❌ Invalid';
        $color = $valid ? 'green' : 'red';
        echo "<tr><td><strong>$name</strong></td><td>$value</td><td style='color: $color;'>$status</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>🔧 Port/Encryption Recommendations</h2>";
    echo "<ul>";
    echo "<li><strong>Port 25:</strong> Plain SMTP (usually blocked by ISPs)</li>";
    echo "<li><strong>Port 587:</strong> SMTP with STARTTLS (use encryption: 'tls')</li>";
    echo "<li><strong>Port 465:</strong> SMTP over SSL (use encryption: 'ssl')</li>";
    echo "<li><strong>Port 2525:</strong> Alternative SMTP (use encryption: 'tls')</li>";
    echo "</ul>";
    
    $currentPort = $emailConfig['smtp_port'];
    $currentEncryption = $emailConfig['encryption'];
    
    if ($currentPort == 465 && $currentEncryption != 'ssl') {
        echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "⚠️ <strong>Configuration Warning:</strong> Port 465 should use 'ssl' encryption, but you have '$currentEncryption'.";
        echo "</div>";
    }
    
    if ($currentPort == 587 && $currentEncryption != 'tls') {
        echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "⚠️ <strong>Configuration Warning:</strong> Port 587 should use 'tls' encryption, but you have '$currentEncryption'.";
        echo "</div>";
    }
    
    echo "<h2>🧪 Connection Test</h2>";
    echo "<p>Testing connection to {$emailConfig['smtp_host']}:{$emailConfig['smtp_port']}...</p>";
    
    // Test basic connectivity
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $connectionTest = false;
    $errorMessage = '';
    
    try {
        if ($emailConfig['encryption'] === 'ssl' || $emailConfig['smtp_port'] == 465) {
            $socket = @stream_socket_client(
                "ssl://{$emailConfig['smtp_host']}:{$emailConfig['smtp_port']}",
                $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context
            );
        } else {
            $socket = @stream_socket_client(
                "tcp://{$emailConfig['smtp_host']}:{$emailConfig['smtp_port']}",
                $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context
            );
        }
        
        if ($socket) {
            $response = fgets($socket, 515);
            fclose($socket);
            $connectionTest = true;
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>Connection successful!</strong> Server response: " . htmlspecialchars(trim($response));
            echo "</div>";
        } else {
            $errorMessage = "$errstr ($errno)";
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
    
    if (!$connectionTest) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Connection failed:</strong> $errorMessage";
        echo "</div>";
        
        echo "<h3>🔍 Troubleshooting Steps:</h3>";
        echo "<ol>";
        echo "<li>Check if your firewall allows outbound connections on port {$emailConfig['smtp_port']}</li>";
        echo "<li>Verify the SMTP server hostname is correct</li>";
        echo "<li>Try different ports (587 with TLS, or 25 if available)</li>";
        echo "<li>Check with your email provider for correct SMTP settings</li>";
        echo "<li>Some ISPs block SMTP ports - consider using port 587 or 2525</li>";
        echo "</ol>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<div style="margin-top: 20px;">
    <a href="email_test.php" style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">
        Test Email Sending
    </a>
    <a href="../admin/login.php" style="background: #667eea; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin-left: 10px;">
        Back to Admin
    </a>
</div>
