<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your Account - Rwebb Portfolio</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/vendor.css">
    <link rel="stylesheet" href="../css/styles.css">
    
    <!-- favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .verification-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .verification-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .verification-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
        }
        
        .verification-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 16px;
        }
        
        .verification-subtitle {
            font-size: 16px;
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .code-input-group {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }
        
        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #2d3748;
            transition: all 0.3s ease;
        }
        
        .code-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .verify-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .verify-btn:hover {
            transform: translateY(-2px);
        }
        
        .verify-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .resend-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .resend-link:hover {
            color: #764ba2;
        }
        
        .error-message {
            background: #fed7d7;
            color: #e53e3e;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .success-message {
            background: #c6f6d5;
            color: #38a169;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .loading {
            display: none;
            color: #667eea;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .timer {
            color: #e53e3e;
            font-weight: 600;
            margin-left: 5px;
        }
        
        @media (max-width: 600px) {
            .verification-form {
                padding: 30px 20px;
            }
            
            .code-input {
                width: 40px;
                height: 50px;
                font-size: 20px;
            }
            
            .code-input-group {
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="verification-container">
        <div class="verification-form">
            <div class="verification-icon">
                📧
            </div>
            
            <h1 class="verification-title">Verify Your Account</h1>
            <p class="verification-subtitle">
                We've sent a 6-digit verification code to your email address. 
                Please enter it below to activate your account.
            </p>
            
            <div id="messageContainer"></div>
            
            <form id="verificationForm" method="POST" action="process_verification.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <div class="code-input-group">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                </div>
                <input type="hidden" name="verification_code" id="fullCode">
                
                <button type="submit" class="verify-btn" id="verifyBtn">
                    Verify Account
                </button>
                
                <div class="loading" id="loadingIndicator">
                    Verifying your account...
                </div>
            </form>
            
            <p>
                Didn't receive the code? 
                <a href="#" class="resend-link" id="resendLink">Resend code</a>
                <span class="timer" id="resendTimer"></span>
            </p>
            
            <p style="margin-top: 30px; font-size: 14px; color: #718096;">
                <a href="../index.html" style="color: #667eea;">← Back to Home</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInputs = document.querySelectorAll('.code-input');
            const fullCodeInput = document.getElementById('fullCode');
            const verificationForm = document.getElementById('verificationForm');
            const verifyBtn = document.getElementById('verifyBtn');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const messageContainer = document.getElementById('messageContainer');
            const resendLink = document.getElementById('resendLink');
            const resendTimer = document.getElementById('resendTimer');
            
            let resendCooldown = 0;
            
            // Handle input navigation and validation
            codeInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Move to next input if filled
                    if (this.value && index < codeInputs.length - 1) {
                        codeInputs[index + 1].focus();
                    }
                    
                    updateFullCode();
                });
                
                input.addEventListener('keydown', function(e) {
                    // Handle backspace
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        codeInputs[index - 1].focus();
                    }
                    
                    // Handle paste
                    if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                        e.preventDefault();
                        navigator.clipboard.readText().then(text => {
                            const numbers = text.replace(/[^0-9]/g, '').slice(0, 6);
                            numbers.split('').forEach((digit, idx) => {
                                if (codeInputs[idx]) {
                                    codeInputs[idx].value = digit;
                                }
                            });
                            updateFullCode();
                            
                            // Focus the last filled input or next empty one
                            const nextEmpty = Math.min(numbers.length, 5);
                            codeInputs[nextEmpty].focus();
                        });
                    }
                });
            });
            
            function updateFullCode() {
                const code = Array.from(codeInputs).map(input => input.value).join('');
                fullCodeInput.value = code;
                verifyBtn.disabled = code.length !== 6;
            }
            
            // Handle form submission
            verificationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (fullCodeInput.value.length !== 6) {
                    showMessage('Please enter a complete 6-digit code.', 'error');
                    return;
                }
                
                verifyBtn.disabled = true;
                loadingIndicator.style.display = 'block';
                
                const formData = new FormData(this);
                
                fetch('process_verification.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loadingIndicator.style.display = 'none';
                    
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect || '../user/login.php';
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                        verifyBtn.disabled = false;
                        
                        // Clear code inputs on error
                        codeInputs.forEach(input => input.value = '');
                        codeInputs[0].focus();
                        updateFullCode();
                    }
                })
                .catch(error => {
                    loadingIndicator.style.display = 'none';
                    verifyBtn.disabled = false;
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Verification error:', error);
                });
            });
            
            // Handle resend link
            resendLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (resendCooldown > 0) {
                    return;
                }
                
                const token = document.querySelector('input[name="token"]').value;
                
                fetch('resend_verification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `token=${encodeURIComponent(token)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Verification code sent! Please check your email.', 'success');
                        startResendCooldown();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Failed to resend code. Please try again.', 'error');
                    console.error('Resend error:', error);
                });
            });
            
            function showMessage(message, type) {
                messageContainer.innerHTML = `<div class="${type}-message">${message}</div>`;
                setTimeout(() => {
                    if (type === 'error') {
                        messageContainer.innerHTML = '';
                    }
                }, 5000);
            }
            
            function startResendCooldown() {
                resendCooldown = 60;
                resendLink.style.pointerEvents = 'none';
                resendLink.style.opacity = '0.5';
                
                const interval = setInterval(() => {
                    resendTimer.textContent = `(${resendCooldown}s)`;
                    resendCooldown--;
                    
                    if (resendCooldown < 0) {
                        clearInterval(interval);
                        resendTimer.textContent = '';
                        resendLink.style.pointerEvents = 'auto';
                        resendLink.style.opacity = '1';
                    }
                }, 1000);
            }
            
            // Auto-focus first input
            codeInputs[0].focus();
            
            // Check for URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('resent') === '1') {
                showMessage('Verification code sent! Please check your email.', 'success');
            }
        });
    </script>
</body>
</html>
