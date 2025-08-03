<?php
/**
 * Custom 403 Forbidden Error Page with HTTP Cats
 */

// Set proper HTTP status code
http_response_code(403);

// Include our HTTP Cat handler
require_once __DIR__ . '/../config/HttpCat.php';

// Show the cute 403 cat error page
HttpCat::show(403, "Access Forbidden", "You don't have permission to access this resource. This area is protected! 🔒");
?>
