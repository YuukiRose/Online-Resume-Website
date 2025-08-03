<?php
/**
 * HTTP Cat Status Code Handler
 * A fun utility for displaying HTTP status codes with cat images from http.cat
 */

class HttpCat {
    
    /**
     * Display an HTTP status code with a cat image
     * @param int $statusCode HTTP status code
     * @param string $title Custom title (optional)
     * @param string $message Custom message (optional)
     * @param bool $exit Whether to exit after displaying (default: true)
     */
    public static function show($statusCode = 404, $title = null, $message = null, $exit = true) {
        http_response_code($statusCode);
        
        // Default titles and messages for common status codes
        $defaults = [
            200 => ['title' => 'OK', 'message' => 'Request successful'],
            201 => ['title' => 'Created', 'message' => 'Resource created successfully'],
            400 => ['title' => 'Bad Request', 'message' => 'The request could not be understood by the server'],
            401 => ['title' => 'Unauthorized', 'message' => 'Authentication is required to access this resource'],
            403 => ['title' => 'Forbidden', 'message' => 'You do not have permission to access this resource'],
            404 => ['title' => 'Not Found', 'message' => 'The requested resource could not be found'],
            405 => ['title' => 'Method Not Allowed', 'message' => 'The request method is not allowed for this resource'],
            408 => ['title' => 'Request Timeout', 'message' => 'The server timed out waiting for the request'],
            410 => ['title' => 'Gone', 'message' => 'The requested resource is no longer available'],
            418 => ['title' => "I'm a Teapot", 'message' => 'The server refuses to brew coffee because it is, permanently, a teapot'],
            429 => ['title' => 'Too Many Requests', 'message' => 'You have sent too many requests in a given amount of time'],
            500 => ['title' => 'Internal Server Error', 'message' => 'The server encountered an unexpected condition'],
            502 => ['title' => 'Bad Gateway', 'message' => 'The server received an invalid response from an upstream server'],
            503 => ['title' => 'Service Unavailable', 'message' => 'The server is currently unable to handle the request'],
        ];
        
        $title = $title ?: ($defaults[$statusCode]['title'] ?? 'HTTP Error');
        $message = $message ?: ($defaults[$statusCode]['message'] ?? 'An HTTP error occurred');
        
        // Check if this is an API request
        $isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        
        if ($isApiRequest) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => $title,
                'code' => $statusCode,
                'message' => $message,
                'cat' => "https://http.cat/$statusCode"
            ]);
            if ($exit) exit;
            return;
        }
        
        // For browser requests, show HTML page with cat
        self::renderHtmlPage($statusCode, $title, $message);
        if ($exit) exit;
    }
    
    /**
     * Get the cat image URL for a status code
     * @param int $statusCode HTTP status code
     * @return string Cat image URL
     */
    public static function getCatUrl($statusCode) {
        return "https://http.cat/$statusCode";
    }
    
    /**
     * Check if a cat exists for the given status code
     * @param int $statusCode HTTP status code
     * @return bool Whether a cat image exists
     */
    public static function catExists($statusCode) {
        // Common HTTP status codes that have cats
        $availableCats = [
            100, 101, 102, 200, 201, 202, 204, 206, 207, 300, 301, 302, 303, 304, 307, 308,
            400, 401, 402, 403, 404, 405, 406, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418,
            420, 421, 422, 423, 424, 425, 426, 429, 431, 444, 450, 451, 499,
            500, 501, 502, 503, 504, 506, 507, 508, 509, 510, 511, 521, 522, 523, 525, 599
        ];
        
        return in_array($statusCode, $availableCats);
    }
    
    /**
     * Render the HTML page with cat image
     */
    private static function renderHtmlPage($statusCode, $title, $message) {
        $catUrl = self::getCatUrl($statusCode);
        $backgroundColor = self::getStatusColor($statusCode);
        
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$statusCode $title</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, $backgroundColor);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        
        .error-code {
            font-size: 4em;
            font-weight: bold;
            color: " . self::getTextColor($statusCode) . ";
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .error-title {
            font-size: 1.5em;
            margin: 10px 0;
            color: #2c3e50;
        }
        
        .cat-container {
            margin: 30px 0;
            position: relative;
        }
        
        .cat-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .cat-image:hover {
            transform: scale(1.05);
        }
        
        .error-message {
            color: #666;
            font-size: 1.1em;
            line-height: 1.6;
            margin: 20px 0;
        }
        
        .back-link {
            display: inline-block;
            background: linear-gradient(45deg, " . str_replace(' 0%, ', ', ', $backgroundColor) . ");
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #999;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='error-code'>$statusCode</h1>
        <h2 class='error-title'>$title</h2>
        
        <div class='cat-container'>
            <img src='$catUrl' alt='HTTP $statusCode Cat' class='cat-image' 
                 onerror=\"this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPiR7c3RhdHVzQ29kZX0gQ2F0PC90ZXh0Pjwvc3ZnPg==\">
        </div>
        
        <div class='error-message'>
            $message
        </div>
        
        <a href='javascript:history.back()' class='back-link'>← Go Back</a>
        
        <div class='footer'>
            HTTP Cat provided by <a href='https://http.cat/' target='_blank'>http.cat</a>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Get background color based on status code category
     */
    private static function getStatusColor($statusCode) {
        if ($statusCode >= 200 && $statusCode < 300) {
            return '#27ae60 0%, #2ecc71 100%'; // Green for success
        } elseif ($statusCode >= 300 && $statusCode < 400) {
            return '#f39c12 0%, #e67e22 100%'; // Orange for redirects
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            return '#e74c3c 0%, #c0392b 100%'; // Red for client errors
        } elseif ($statusCode >= 500) {
            return '#8e44ad 0%, #9b59b6 100%'; // Purple for server errors
        } else {
            return '#667eea 0%, #764ba2 100%'; // Default blue
        }
    }
    
    /**
     * Get text color based on status code category
     */
    private static function getTextColor($statusCode) {
        if ($statusCode >= 200 && $statusCode < 300) {
            return '#27ae60'; // Green
        } elseif ($statusCode >= 300 && $statusCode < 400) {
            return '#f39c12'; // Orange
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            return '#e74c3c'; // Red
        } elseif ($statusCode >= 500) {
            return '#8e44ad'; // Purple
        } else {
            return '#667eea'; // Blue
        }
    }
}
?>
