<?php
/**
 * Custom 500 Internal Server Error Page with HTTP Cats
 */

// Set proper HTTP status code
http_response_code(500);

// Include our HTTP Cat handler
require_once __DIR__ . '/../config/HttpCat.php';

// Show the cute 500 cat error page
HttpCat::show(500, "Internal Server Error", "Something went wrong on our end. We're working to fix it! 💥");
?>
