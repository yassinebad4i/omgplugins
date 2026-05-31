<?php
/**
 * URL Router for Dynamic Pages
 * Handles slug-based routing for apps and games
 */

require_once __DIR__ . '/frontend.php';

class Router {
    private $json;
    private $frontend;
    
    public function __construct() {
        global $json, $frontend;
        $this->json = $json;
        $this->frontend = $frontend;
    }
    
    /**
     * Get slug from URL
     */
    public static function getSlug() {
        // Check if slug is passed as query parameter (app.php?slug=xxx or game.php?slug=xxx)
        if (isset($_GET['slug'])) {
            return Security::sanitize($_GET['slug']);
        }
        
        // Try to get from path (app/xxx.php)
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));
        
        if (count($parts) >= 2) {
            $slug = $parts[count($parts) - 1];
            $slug = str_replace('.php', '', $slug);
            return Security::sanitize($slug);
        }
        
        return null;
    }
    
    /**
     * Route to app detail page
     */
    public function routeApp() {
        $slug = self::getSlug();
        
        if (!$slug) {
            return ['error' => 'Slug not found', 'code' => 404];
        }
        
        $app = $this->json->getBySlug('apps', $slug);
        
        if (!$app || $app['status'] !== 'published') {
            return ['error' => 'App not found', 'code' => 404];
        }
        
        return ['success' => true, 'app' => $app];
    }
    
    /**
     * Route to game detail page
     */
    public function routeGame() {
        $slug = self::getSlug();
        
        if (!$slug) {
            return ['error' => 'Slug not found', 'code' => 404];
        }
        
        $game = $this->json->getBySlug('games', $slug);
        
        if (!$game || $game['status'] !== 'published') {
            return ['error' => 'Game not found', 'code' => 404];
        }
        
        return ['success' => true, 'game' => $game];
    }
    
    /**
     * Handle 404 error
     */
    public static function handle404() {
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/html; charset=utf-8');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0a0d14 0%, #111827 100%);
            color: #e8ecf5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            text-align: center;
            max-width: 500px;
        }
        .code {
            font-size: 5rem;
            font-weight: 900;
            color: #00f0a0;
            line-height: 1;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        p {
            color: #6b7fa3;
            margin-bottom: 2rem;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: #00f0a0;
            color: #000;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been removed.</p>
        <a href="/" class="button">Back to Home</a>
    </div>
</body>
</html>
HTML;
        exit;
    }
}

$router = new Router();
?>
