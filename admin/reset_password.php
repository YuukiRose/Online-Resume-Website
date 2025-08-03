<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Rose Webb Portfolio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .reset-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .reset-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .reset-header p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .password-strength {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
        .reset-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .reset-btn:hover {
            opacity: 0.9;
        }
        
        .reset-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .error-message,
        .success-message {
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
        }
        
        .requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        .requirements ul {
            margin-left: 1rem;
        }
        
        .requirement {
            margin: 0.2rem 0;
        }
        
        .requirement.met {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <?php
        session_start();
        require_once '../config/database.php';
        
        $access_granted = false;
        $user_id = null;
        $token = null;
        
        // First check session variables
        if (isset($_SESSION['verified_reset_token']) && isset($_SESSION['reset_user_id'])) {
            $access_granted = true;
            $user_id = $_SESSION['reset_user_id'];
            $token = $_SESSION['verified_reset_token'];
        }
        // Fallback: Check database for verified token
        else if (isset($_SESSION['reset_token'])) {
            $token = $_SESSION['reset_token'];
            try {
                $stmt = $pdo->prepare("
                    SELECT prt.user_id 
                    FROM password_reset_tokens prt 
                    WHERE prt.token = ? AND prt.verified = TRUE AND prt.expires_at > NOW() AND prt.used = FALSE
                ");
                $stmt->execute([$token]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $access_granted = true;
                    $user_id = $result['user_id'];
                    // Set session variables for consistency
                    $_SESSION['verified_reset_token'] = $token;
                    $_SESSION['reset_user_id'] = $user_id;
                }
            } catch (PDOException $e) {
                error_log("Database error in reset_password.php: " . $e->getMessage());
            }
        }
        
        if (!$access_granted) {
            echo '<div class="reset-header"><h1>Invalid Access</h1></div>';
            echo '<div class="error-message">Please complete the verification process first.</div>';
            echo '<div style="text-align: center;"><a href="forgot_password.php">Start Password Reset</a></div>';
            echo '</div></body></html>';
            exit;
        }
        ?>
        
        <div class="reset-header">
            <h1>Set New Password</h1>
            <p>Choose a strong password for your admin account</p>
        </div>
        
        <?php
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            if ($error === 'mismatch') {
                echo '<div class="error-message">Passwords do not match. Please try again.</div>';
            } elseif ($error === 'weak') {
                echo '<div class="error-message">Password does not meet security requirements.</div>';
            } elseif ($error === 'failed') {
                echo '<div class="error-message">Failed to update password. Please try again.</div>';
            }
        }
        ?>
        
        <form action="process_reset_password.php" method="POST" id="resetForm">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-strength" id="strengthIndicator"></div>
                <div class="requirements">
                    <ul>
                        <li class="requirement" id="req-length">At least 8 characters</li>
                        <li class="requirement" id="req-upper">One uppercase letter</li>
                        <li class="requirement" id="req-lower">One lowercase letter</li>
                        <li class="requirement" id="req-number">One number</li>
                        <li class="requirement" id="req-special">One special character</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <div id="matchIndicator" style="font-size: 0.8rem; margin-top: 0.5rem;"></div>
            </div>
            
            <button type="submit" class="reset-btn" id="submitBtn" disabled>Update Password</button>
        </form>
    </div>
    
    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const strengthIndicator = document.getElementById('strengthIndicator');
        const matchIndicator = document.getElementById('matchIndicator');
        const submitBtn = document.getElementById('submitBtn');
        
        const requirements = {
            length: /^.{8,}$/,
            upper: /[A-Z]/,
            lower: /[a-z]/,
            number: /[0-9]/,
            special: /[^A-Za-z0-9]/
        };
        
        function checkPasswordStrength(password) {
            let score = 0;
            let metRequirements = 0;
            
            for (const [key, regex] of Object.entries(requirements)) {
                const element = document.getElementById(`req-${key}`);
                if (regex.test(password)) {
                    element.classList.add('met');
                    score += 20;
                    metRequirements++;
                } else {
                    element.classList.remove('met');
                }
            }
            
            let strengthText = '';
            let strengthClass = '';
            
            if (score < 60) {
                strengthText = 'Weak';
                strengthClass = 'strength-weak';
            } else if (score < 100) {
                strengthText = 'Medium';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Strong';
                strengthClass = 'strength-strong';
            }
            
            strengthIndicator.textContent = `Password strength: ${strengthText}`;
            strengthIndicator.className = `password-strength ${strengthClass}`;
            
            return metRequirements === 5;
        }
        
        function checkPasswordMatch() {
            if (confirmInput.value === '') {
                matchIndicator.textContent = '';
                return false;
            }
            
            if (passwordInput.value === confirmInput.value) {
                matchIndicator.textContent = '✓ Passwords match';
                matchIndicator.style.color = '#28a745';
                return true;
            } else {
                matchIndicator.textContent = '✗ Passwords do not match';
                matchIndicator.style.color = '#dc3545';
                return false;
            }
        }
        
        function updateSubmitButton() {
            const strongPassword = checkPasswordStrength(passwordInput.value);
            const passwordsMatch = checkPasswordMatch();
            
            submitBtn.disabled = !(strongPassword && passwordsMatch);
        }
        
        passwordInput.addEventListener('input', updateSubmitButton);
        confirmInput.addEventListener('input', updateSubmitButton);
    </script>
</body>
</html>
