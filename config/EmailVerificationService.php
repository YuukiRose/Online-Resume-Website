<?php
/**
 * Email Verification Service
 * Handles sending verification emails for different purposes
 */

class EmailVerificationService {
    private $fromEmail;
    private $fromName;
    private $baseUrl;
    
    public function __construct($fromEmail = 'noreply@rosewebbdev.com', $fromName = 'Rose Webb Portfolio', $baseUrl = null) {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->baseUrl = $baseUrl ?: $this->getBaseUrl();
    }
    
    /**
     * Get the base URL for the application
     * @return string Base URL
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Send password reset verification email
     * @param string $email Recipient email
     * @param string $username Username
     * @param string $verificationCode 6-digit code
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendPasswordResetEmail($email, $username, $verificationCode, $token) {
        $subject = 'Password Reset Verification - Rose Webb Portfolio';
        
        $verificationUrl = $this->baseUrl . '/admin/verify_reset.php?token=' . urlencode($token);
        
        $message = $this->buildEmailTemplate([
            'title' => 'Password Reset Request',
            'greeting' => "Hello {$username}",
            'main_message' => 'You have requested to reset your password. To proceed, please use the verification code below:',
            'verification_code' => $verificationCode,
            'action_url' => $verificationUrl,
            'action_text' => 'Verify & Reset Password',
            'additional_info' => [
                'This code is valid for 15 minutes.',
                'If you did not request this password reset, please ignore this email.',
                'For security reasons, this link will expire after use.'
            ],
            'purpose' => 'password_reset'
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send account activation email for new users
     * @param string $email Recipient email
     * @param string $username Username
     * @param string $verificationCode 6-digit code
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendAccountActivationEmail($email, $username, $verificationCode, $token) {
        $subject = 'Activate Your Account - Rose Webb Portfolio';
        
        $verificationUrl = $this->baseUrl . '/user/verify_account.php?token=' . urlencode($token);
        
        $message = $this->buildEmailTemplate([
            'title' => 'Welcome! Activate Your Account',
            'greeting' => "Welcome {$username}!",
            'main_message' => 'Thank you for creating an account. To complete your registration and activate your account, please use the verification code below:',
            'verification_code' => $verificationCode,
            'action_url' => $verificationUrl,
            'action_text' => 'Activate Account',
            'additional_info' => [
                'This verification code is valid for 15 minutes.',
                'You must verify your email address to access your account.',
                'Keep this email safe for your records.'
            ],
            'purpose' => 'account_activation'
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send email verification for existing users
     * @param string $email Recipient email
     * @param string $username Username
     * @param string $verificationCode 6-digit code
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendEmailVerificationEmail($email, $username, $verificationCode, $token) {
        $subject = 'Email Verification - Rose Webb Portfolio';
        
        $verificationUrl = $this->baseUrl . '/user/verify_email.php?token=' . urlencode($token);
        
        $message = $this->buildEmailTemplate([
            'title' => 'Verify Your Email Address',
            'greeting' => "Hello {$username}",
            'main_message' => 'Please verify your email address to ensure account security. Use the verification code below:',
            'verification_code' => $verificationCode,
            'action_url' => $verificationUrl,
            'action_text' => 'Verify Email',
            'additional_info' => [
                'This verification code expires in 15 minutes.',
                'If you did not request this verification, please contact support.',
                'Your account security is important to us.'
            ],
            'purpose' => 'email_verification'
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send login verification email (2FA for login)
     * @param string $email Recipient email
     * @param string $username Username
     * @param string $verificationCode 6-digit code
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendLoginVerificationEmail($email, $username, $verificationCode, $token) {
        $subject = 'Login Verification - Rose Webb Portfolio';
        
        $verificationUrl = $this->baseUrl . '/user/verify_login.php?token=' . urlencode($token);
        
        $message = $this->buildEmailTemplate([
            'title' => 'Login Verification Required',
            'greeting' => "Hello {$username}",
            'main_message' => 'A login attempt was made to your account. To complete the login process, please use the verification code below:',
            'verification_code' => $verificationCode,
            'action_url' => $verificationUrl,
            'action_text' => 'Verify Login',
            'additional_info' => [
                'This code is valid for 15 minutes.',
                'If this was not you, please change your password immediately.',
                'Login time: ' . date('Y-m-d H:i:s T')
            ],
            'purpose' => 'login_verification'
        ]);
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Build HTML email template
     * @param array $data Template data
     * @return string HTML email content
     */
    private function buildEmailTemplate($data) {
        $verificationCode = $data['verification_code'];
        $title = $data['title'];
        $greeting = $data['greeting'];
        $mainMessage = $data['main_message'];
        $actionUrl = $data['action_url'];
        $actionText = $data['action_text'];
        $additionalInfo = $data['additional_info'];
        $purpose = $data['purpose'];
        
        $year = date('Y');
        
        return "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background-color: #f4f4f4;
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background: white; 
            border-radius: 10px; 
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 24px; 
        }
        .content { 
            padding: 30px 20px; 
        }
        .verification-code { 
            background: #f8f9fa; 
            border: 2px dashed #667eea; 
            border-radius: 8px; 
            padding: 20px; 
            text-align: center; 
            margin: 20px 0; 
        }
        .code { 
            font-size: 32px; 
            font-weight: bold; 
            color: #667eea; 
            letter-spacing: 4px; 
            font-family: monospace;
        }
        .btn { 
            display: inline-block; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 5px; 
            margin: 20px 0;
            font-weight: bold;
        }
        .info-list { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 20px 0; 
        }
        .info-list ul { 
            margin: 0; 
            padding-left: 20px; 
        }
        .footer { 
            background: #f8f9fa; 
            padding: 20px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #eee;
        }
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>{$title}</h1>
        </div>
        
        <div class='content'>
            <p><strong>{$greeting}</strong></p>
            
            <p>{$mainMessage}</p>
            
            <div class='verification-code'>
                <p><strong>Your Verification Code:</strong></p>
                <div class='code'>{$verificationCode}</div>
                <p style='font-size: 14px; color: #666;'>Enter this code to continue</p>
            </div>
            
            <div style='text-align: center;'>
                <a href='{$actionUrl}' class='btn'>{$actionText}</a>
            </div>
            
            <div class='info-list'>
                <strong>Important Information:</strong>
                <ul>";
        
        foreach ($additionalInfo as $info) {
            $html .= "<li>{$info}</li>";
        }
        
        $html .= "
                </ul>
            </div>
            
            <div class='security-notice'>
                <strong>Security Notice:</strong> This email contains sensitive information. Do not share this code with anyone. Our team will never ask for your verification code.
            </div>
        </div>
        
        <div class='footer'>
            <p>&copy; {$year} Rose Webb Portfolio. All rights reserved.</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you need help, please contact our support team.</p>
        </div>
    </div>
</body>
</html>";
        
        return $html;
    }
    
    /**
     * Send email using PHP's mail function
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email content
     * @return bool Success status
     */
    private function sendEmail($to, $subject, $message) {
        $headers = [
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headerString = implode("\r\n", $headers);
        
        try {
            $result = mail($to, $subject, $message, $headerString);
            
            if ($result) {
                error_log("Verification email sent successfully to: " . $to);
                return true;
            } else {
                error_log("Failed to send verification email to: " . $to);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Test email configuration
     * @param string $testEmail Email to send test to
     * @return bool Success status
     */
    public function testEmailConfiguration($testEmail) {
        $subject = 'Email Configuration Test - Rose Webb Portfolio';
        $message = $this->buildEmailTemplate([
            'title' => 'Email Test',
            'greeting' => 'Hello',
            'main_message' => 'This is a test email to verify your email configuration is working correctly.',
            'verification_code' => '123456',
            'action_url' => '#',
            'action_text' => 'Test Link',
            'additional_info' => [
                'This is a test email.',
                'If you receive this, your email configuration is working.',
                'You can safely ignore this message.'
            ],
            'purpose' => 'test'
        ]);
        
        return $this->sendEmail($testEmail, $subject, $message);
    }
}
?>
