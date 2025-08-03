<?php
// Simple 403 error page without dependencies
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: #f8f9fa;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #dc3545; }
        .cat-image { max-width: 400px; width: 100%; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>403 - Access Forbidden</h1>
        <img src="https://http.cat/403" alt="HTTP 403 Cat" class="cat-image">
        <p>You don't have permission to access this resource. This area is protected! 🔒</p>
    </div>
</body>
</html>
