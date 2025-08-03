<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

try {
    // Add verified column to password_reset_tokens table
    $sql = "ALTER TABLE password_reset_tokens ADD COLUMN verified BOOLEAN DEFAULT FALSE";
    $pdo->exec($sql);
    echo "Successfully added 'verified' column to password_reset_tokens table.";
} catch(PDOException $e) {
    // Column might already exist
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'verified' already exists in password_reset_tokens table.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>



