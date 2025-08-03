# 🔐 Database Security Guide

## Overview
Your database credentials are currently stored in plain text and need to be encrypted for security.

## Current Situation
**⚠️ SECURITY RISK:** Plain text credentials in `config/database.php`:
```php
$host = 'mysql-200-133.mysql.prositehosting.net';
$dbname = 'DB_01_RWEBB_RES';  
$username = 'RoseWebb';
$password = 'WhiteRabbit435$';
```

## Security Solutions

### Method 1: Encrypted Configuration (Recommended)

#### Step 1: Encrypt Credentials
1. Visit: `http://localhost/Luthor/admin/encrypt_database.php`
2. Your current credentials are pre-filled
3. Click "Encrypt Database Credentials"
4. Copy the encrypted configuration

#### Step 2: Update Database Config
Replace the entire contents of `config/database.php` with the encrypted version.

#### Step 3: Security Cleanup
**IMPORTANT:** Delete `encrypt_database.php` after use!

### Method 2: Environment Variables (.env file) - Maximum Security

#### Step 1: Create .env File
1. Copy `.env.example` to `.env`
2. Update `.env` with your actual credentials:
```bash
DB_HOST=mysql-200-133.mysql.prositehosting.net
DB_NAME=DB_01_RWEBB_RES
DB_USERNAME=RoseWebb
DB_PASSWORD=WhiteRabbit435$
```

#### Step 2: Update Database Config
Replace `config/database.php` with environment-aware version:
```php
<?php
require_once 'EnvLoader.php';
EnvLoader::load();

$host = EnvLoader::get('DB_HOST', 'localhost');
$dbname = EnvLoader::get('DB_NAME', 'luthor_portfolio');  
$username = EnvLoader::get('DB_USERNAME', 'root');
$password = EnvLoader::get('DB_PASSWORD', '');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

#### Step 3: Secure the .env File
- **NEVER** commit `.env` to version control
- Set proper file permissions (readable only by web server)
- Keep `.env` outside web root if possible

## Security Features

### ✅ Encryption Benefits
1. **AES-256-CBC encryption** for stored credentials
2. **Automatic decryption** when needed
3. **No plain text storage** in code
4. **Environment variable support**

### ✅ Additional Security
- **Secure key management**
- **Configurable encryption methods**
- **Environment-specific configs**
- **Security logging**

## Production Recommendations

### For Shared Hosting:
- Use **Method 1** (encrypted configuration)
- Delete encryption tools after use
- Regular security audits

### For VPS/Dedicated Servers:
- Use **Method 2** (.env file)
- Set proper file permissions
- Use system environment variables
- Monitor access logs

## Testing Your Security

1. **Encrypt credentials**: Use the encryption tool
2. **Update configuration**: Replace database.php
3. **Test connection**: Verify site still works
4. **Check logs**: Review security.log for any issues
5. **Delete tools**: Remove encryption utilities

## File Security Checklist

### ✅ Secure Files:
- `config/database.php` - No plain text credentials
- `config/security.php` - Environment-aware encryption keys
- `.env` - Proper permissions, not in version control
- `logs/` - Proper permissions for security logs

### ⚠️ Remove After Setup:
- `admin/encrypt_database.php` - Delete after encrypting credentials
- `.env.example` - Remove or ensure it has no real credentials

## Emergency Access

If you get locked out due to encryption issues:
1. Check error logs for decryption problems
2. Verify encryption key is correct
3. Use plain text temporarily to diagnose
4. Re-encrypt with proper key

## Best Practices

1. **Regular Key Rotation**: Change encryption keys periodically
2. **Access Monitoring**: Log database connection attempts
3. **Backup Security**: Encrypt backup files containing credentials
4. **Network Security**: Use SSL/TLS for database connections
5. **Principle of Least Privilege**: Database user should have minimal required permissions

Your database credentials will be secure once encrypted! 🔒
