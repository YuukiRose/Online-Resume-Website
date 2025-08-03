<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt Email Password - Rose Webb Portfolio</title>
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
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .encrypt-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 600px;
        }
        
        .encrypt-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .encrypt-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .encrypt-header p {
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
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
            font-family: monospace;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .encrypt-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.3s;
            margin-bottom: 1rem;
        }
        
        .encrypt-btn:hover {
            opacity: 0.9;
        }
        
        .result {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            border-left: 4px solid #28a745;
        }
        
        .result h3 {
            color: #28a745;
            margin-bottom: 0.5rem;
        }
        
        .copy-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .instructions {
            background: #e9ecef;
            padding: 1.5rem;
            border-radius: 5px;
            margin-top: 2rem;
        }
        
        .instructions h3 {
            color: #495057;
            margin-bottom: 1rem;
        }
        
        .instructions ol {
            margin-left: 1rem;
        }
        
        .instructions li {
            margin-bottom: 0.5rem;
            color: #6c757d;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="encrypt-container">
        <div class="encrypt-header">
            <h1>🔒 Email Password Encryption</h1>
            <p>Encrypt your email password for secure storage</p>
        </div>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong> This tool helps encrypt your email password. Make sure you're on a secure connection and delete this file after use.
        </div>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['encrypt'])) {
            require_once '../../config/SecureConfig.php';
            
            $password = $_POST['password'];
            $encrypted = SecureConfig::encrypt($password);
            
            echo '<div class="success">✅ Password encrypted successfully!</div>';
            echo '<div class="result">';
            echo '<h3>Encrypted Password:</h3>';
            echo '<textarea readonly onclick="this.select()" id="encryptedPassword">' . htmlspecialchars($encrypted) . '</textarea>';
            echo '<button class="copy-btn" onclick="copyToClipboard()">Copy to Clipboard</button>';
            echo '</div>';
        }
        ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Email Password to Encrypt:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your actual email password">
            </div>
            
            <button type="submit" name="encrypt" class="encrypt-btn">🔒 Encrypt Password</button>
        </form>
        
        <div class="instructions">
            <h3>📋 How to Use:</h3>
            <ol>
                <li>Enter your actual email password above</li>
                <li>Click "Encrypt Password" to generate encrypted version</li>
                <li>Copy the encrypted password from the result box</li>
                <li>Replace the password in <code>config/email.php</code></li>
                <li><strong>Delete this file after use for security!</strong></li>
            </ol>
            
            <h3>🔧 Update email.php:</h3>
            <p>Replace <code>'smtp_password' => 'your-app-password'</code> with:</p>
            <p><code>'smtp_password' => 'ENCRYPTED_PASSWORD_HERE'</code></p>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="admin/login.php" style="color: #667eea; text-decoration: none;">← Back to Admin Login</a>
        </div>
    </div>
    
    <script>
        function copyToClipboard() {
            const textarea = document.getElementById('encryptedPassword');
            textarea.select();
            document.execCommand('copy');
            
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = '✅ Copied!';
            btn.style.background = '#28a745';
            
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = '#28a745';
            }, 2000);
        }
        
        // Clear form after successful encryption
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['encrypt'])): ?>
        document.getElementById('password').value = '';
        <?php endif; ?>
    </script>
</body>
</html>


