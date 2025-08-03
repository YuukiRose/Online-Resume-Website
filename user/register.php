<?php
session_start();

// Handle error messages from URL parameters
$error = '';
$successMessage = '';
$formData = [
    'first_name' => '',
    'last_name' => '',
    'username' => '',
    'email' => '',
    'linkedin_profile' => ''
];

// Check for error parameters
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'terms_required':
            $error = 'You must agree to the terms and conditions.';
            break;
        case 'password_mismatch':
            $error = 'Passwords do not match.';
            break;
        case 'weak_password':
            $error = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.';
            break;
        case 'email_exists':
            $error = 'An account with this email already exists.';
            break;
        case 'username_exists':
            $error = 'This username is already taken.';
            break;
        case 'email_failed':
            $error = 'Failed to send verification email. Please try again.';
            break;
        case 'registration_failed':
            $error = 'Registration failed. Please try again.';
            break;
        default:
            $error = 'An error occurred during registration.';
    }
    
    // Restore form data if available
    $formData['first_name'] = $_GET['first_name'] ?? '';
    $formData['last_name'] = $_GET['last_name'] ?? '';
    $formData['username'] = $_GET['username'] ?? '';
    $formData['email'] = $_GET['email'] ?? '';
    $formData['linkedin_profile'] = $_GET['linkedin_profile'] ?? '';
}

// Check for success message from session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Luthor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .back-link {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 14px;
            color: #333;
        }
        
        .checkbox-label input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }
        
        .checkbox-label a {
            color: #667eea;
            text-decoration: none;
        }
        
        .checkbox-label a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="../index.html">← Back to Home</a>
        </div>
        
        <div class="header">
            <h1>Create Account</h1>
            <p>Join our community and share your testimonials</p>
        </div>
        
        <?php if ($successMessage): ?>
            <div class="message success">✅ <?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="process_registration.php" id="registrationForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($formData['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($formData['last_name']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($formData['username']); ?>" required>
                <div class="password-requirements">At least 3 characters, no spaces</div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($formData['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="linkedin_profile">LinkedIn Profile (Optional)</label>
                <input type="url" id="linkedin_profile" name="linkedin_profile" 
                       value="<?php echo htmlspecialchars($formData['linkedin_profile']); ?>" 
                       placeholder="https://linkedin.com/in/your-profile">
                <div class="password-requirements">Optional: Your LinkedIn profile URL for professional networking</div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">At least 8 characters with uppercase, lowercase, number, and special character</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span class="checkmark"></span>
                    I agree to the <a href="../terms.html" target="_blank">Terms and Conditions</a>
                </label>
            </div>
            
            <button type="submit" class="btn">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log in here</a>
        </div>
    </div>
</body>
</html>
