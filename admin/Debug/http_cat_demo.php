<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/HttpCat.php';

// Handle status code display requests
if (isset($_GET['code'])) {
    $statusCode = (int)$_GET['code'];
    if ($statusCode >= 100 && $statusCode <= 599) {
        HttpCat::show($statusCode, null, null, true);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTTP Cat Status Code Demo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        
        .intro {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        
        .status-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        
        .status-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #667eea;
            text-decoration: none;
            color: #333;
        }
        
        .status-code {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .status-name {
            font-size: 0.9em;
            color: #666;
        }
        
        .category-title {
            font-size: 1.2em;
            font-weight: bold;
            margin: 30px 0 15px 0;
            padding: 10px;
            border-radius: 5px;
        }
        
        .success { background-color: #d4edda; color: #155724; }
        .redirect { background-color: #fff3cd; color: #856404; }
        .client-error { background-color: #f8d7da; color: #721c24; }
        .server-error { background-color: #e2e3f7; color: #383d75; }
        
        .custom-test {
            background: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }
        
        .custom-test input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 0 10px;
            width: 100px;
        }
        
        .custom-test button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .custom-test button:hover {
            background: #5a6fd8;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            background: #28a745;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            background: #218838;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐱 HTTP Cat Status Code Demo</h1>
        
        <div class="intro">
            <h3>About HTTP Cats</h3>
            <p>HTTP Cats is a fun API that provides cat images for HTTP status codes. Each status code gets its own adorable cat image that represents the meaning of that HTTP response code.</p>
            <p><strong>API:</strong> <a href="https://http.cat/" target="_blank">https://http.cat/</a></p>
        </div>
        
        <div class="custom-test">
            <h3>🧪 Test Any Status Code</h3>
            <form method="GET">
                <label>Enter HTTP Status Code:</label>
                <input type="number" name="code" min="100" max="599" placeholder="404" required>
                <button type="submit">Show Cat 🐱</button>
            </form>
        </div>
        
        <div class="category-title success">✅ Success (2xx)</div>
        <div class="status-grid">
            <?php 
            $successCodes = [200 => 'OK', 201 => 'Created', 202 => 'Accepted', 204 => 'No Content'];
            foreach ($successCodes as $code => $name): ?>
                <a href="?code=<?= $code ?>" class="status-card">
                    <div class="status-code"><?= $code ?></div>
                    <div class="status-name"><?= $name ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="category-title redirect">🔄 Redirection (3xx)</div>
        <div class="status-grid">
            <?php 
            $redirectCodes = [301 => 'Moved Permanently', 302 => 'Found', 304 => 'Not Modified', 307 => 'Temporary Redirect'];
            foreach ($redirectCodes as $code => $name): ?>
                <a href="?code=<?= $code ?>" class="status-card">
                    <div class="status-code"><?= $code ?></div>
                    <div class="status-name"><?= $name ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="category-title client-error">❌ Client Error (4xx)</div>
        <div class="status-grid">
            <?php 
            $clientErrorCodes = [
                400 => 'Bad Request', 
                401 => 'Unauthorized', 
                403 => 'Forbidden', 
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                408 => 'Request Timeout',
                410 => 'Gone',
                418 => "I'm a Teapot",
                429 => 'Too Many Requests'
            ];
            foreach ($clientErrorCodes as $code => $name): ?>
                <a href="?code=<?= $code ?>" class="status-card">
                    <div class="status-code"><?= $code ?></div>
                    <div class="status-name"><?= $name ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="category-title server-error">💥 Server Error (5xx)</div>
        <div class="status-grid">
            <?php 
            $serverErrorCodes = [
                500 => 'Internal Server Error', 
                502 => 'Bad Gateway', 
                503 => 'Service Unavailable', 
                504 => 'Gateway Timeout'
            ];
            foreach ($serverErrorCodes as $code => $name): ?>
                <a href="?code=<?= $code ?>" class="status-card">
                    <div class="status-code"><?= $code ?></div>
                    <div class="status-name"><?= $name ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="intro">
            <h3>🛠️ Usage Examples</h3>
            <p><strong>Simple Usage:</strong></p>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
// Show a 404 error with cat
HttpCat::show(404);

// Show custom error with cat
HttpCat::show(403, 'Access Denied', 'You need admin privileges');

// Get just the cat URL
$catUrl = HttpCat::getCatUrl(418); // I'm a teapot!
            </pre>
        </div>
        
        <div class="back-link">
            <a href="../debug_dashboard.php">← Back to Debug Dashboard</a>
        </div>
    </div>
</body>
</html>
