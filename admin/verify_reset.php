<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Reset Code - Rose Webb Portfolio</title>
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
        
        .verify-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .verify-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .verify-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .verify-header p {
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
            text-align: center;
            letter-spacing: 2px;
        }
        
        .code-input {
            font-size: 1.5rem !important;
            font-weight: bold;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .verify-btn {
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
        
        .verify-btn:hover {
            opacity: 0.9;
        }
        
        .error-message,
        .info-message,
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
        
        .info-message {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .resend-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .resend-link a {
            color: #28a745;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-header">
            <h1>Enter Verification Code</h1>
            <p>Check your email for the 6-digit verification code</p>
        </div>
        
        <?php
        session_start();
        
        if (!isset($_SESSION['reset_token'])) {
            echo '<div class="error-message">Invalid session. Please start the password reset process again.</div>';
            echo '<div class="back-link"><a href="forgot_password.php">← Start Over</a></div>';
            echo '</div></body></html>';
            exit;
        }
        
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            if ($error === 'invalid') {
                echo '<div class="error-message">Invalid verification code. Please try again.</div>';
            } elseif ($error === 'expired') {
                echo '<div class="error-message">Verification code has expired. Please request a new one.</div>';
            }
        }
        ?>
        
        <div class="success-message">
            ✅ Verification code sent! Check your email and enter the 6-digit code below.
        </div>
        
        <form action="process_verify_reset.php" method="POST">
            <div class="form-group">
                <label for="verification_code">Verification Code</label>
                <input type="text" id="verification_code" name="verification_code" class="code-input" 
                       maxlength="6" pattern="[0-9]{6}" required placeholder="000000">
            </div>
            
            <button type="submit" class="verify-btn">Verify Code</button>
        </form>
        
        <div class="resend-link">
            <a href="forgot_password.php">Didn't receive the code? Send again</a>
        </div>
        
        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
    
    <script>
        // Auto-format verification code input
        document.getElementById('verification_code').addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Auto-submit when 6 digits entered
        document.getElementById('verification_code').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                // Small delay to let user see the complete code
                setTimeout(() => {
                    this.form.submit();
                }, 500);
            }
        });
    </script>
</body>
</html>
