<?php
// Secure directory - Access denied using HTTP Cat
require_once '../config/HttpCat.php';

// Show 403 Forbidden with cute cat
HttpCat::show(403, 'Access Forbidden', 'This directory contains sensitive configuration files and is protected from web access. All access attempts are logged for security monitoring.');
?>
