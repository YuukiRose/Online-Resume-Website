<?php
require_once '../../config/admin_auth_check.php';
session_start();

echo "<h1>Current Session Debug</h1>";
echo "<strong>Session ID:</strong> " . session_id() . "<br>";
echo "<strong>Session Contents:</strong><br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['reset_token'])) {
    require_once '../config/database.php';
    
    echo "<h2>Database Check for Token:</h2>";
    $token = $_SESSION['reset_token'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT prt.*, au.username 
            FROM password_reset_tokens prt 
            JOIN admin_users au ON prt.user_id = au.id 
            WHERE prt.token = ?
        ");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<pre>";
            print_r($result);
            echo "</pre>";
            
            echo "<strong>Token Status:</strong><br>";
            echo "- Verified: " . ($result['verified'] ? 'YES' : 'NO') . "<br>";
            echo "- Expired: " . ($result['expires_at'] < date('Y-m-d H:i:s') ? 'YES' : 'NO') . "<br>";
            echo "- Used: " . ($result['used'] ? 'YES' : 'NO') . "<br>";
        } else {
            echo "No token found in database!";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>



