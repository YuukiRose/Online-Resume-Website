<?php
/**
 * Custom 404 Not Found Error Page with HTTP Cats
 */

// Set proper HTTP status code
http_response_code(404);

// Include our HTTP Cat handler
require_once __DIR__ . '/../config/HttpCat.php';

// Show the cute 404 cat error page
HttpCat::show(404, "Page Not Found", "The page you're looking for doesn't exist. It might have moved or been deleted! 🔍");
?>
