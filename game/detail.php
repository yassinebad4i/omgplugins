<?php
/**
 * Dynamic Game Detail Page
 * Displays individual game with full details, SEO, and download tracking
 */

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/json.php';
require_once __DIR__ . '/../includes/frontend.php';
require_once __DIR__ . '/../includes/router.php';

global $json, $frontend, $router;

$settings = $frontend->getSettings();
$slug = Router::getSlug();

if (!$slug) {
    Router::handle404();
}

$game = $json->getBySlug('games', $slug);

if (!$game || $game['status'] !== 'published') {
    Router::handle404();
}

// Track download if requested
if (isset($_GET['download'])) {
    $frontend->trackDownload($game['id'], 'game');
    if (!empty($game['download_url'])) {
        header('Location: ' . $game['download_url']);
        exit;
    }
}

$downloads = $frontend->getDownloadCount($game['id'], 'game');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $frontend->renderMetaTags($game, 'game'); ?>
    <link rel="icon" href="<?php echo Security::escape($settings['favicon'] ?? '/favicon.ico'); ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0d14;
            color: #e8ecf5;
            line-height: 1.6;
        }
        
        header {
            background: #111827;
            border-bottom: 1px solid #1f2d45;
            padding: 1.5rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00f0a0;
            text-decoration: none;
        }
        
        nav a {
            color: #6b7fa3;
            text-decoration: none;
            margin-left: 1.5rem;
            transition: color 0.2s;
        }
        
        nav a:hover {
            color: #00f0a0;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .breadcrumb {
            margin-bottom: 2rem;
            color: #6b7fa3;
            font-size: 0.95rem;
        }
        
        .breadcrumb a {
            color: #00f0a0;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
            align-items: start;
        }
        
        .hero-image {
            width: 100%;
            height: 400px;
            background: #1f2d45;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: #6b7fa3;
        }
        
        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .hero-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #00f0a0 0%, #00d4ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .meta-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .meta-item {
            background: #111827;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #1f2d45;
            text-align: center;
        }
        
        .meta-label {
            color: #6b7fa3;
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
        }
        
        .meta-value {
            color: #00f0a0;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #00f0a0;
            color: #000;
        }
        
        .btn-primary:hover {
            box-shadow: 0 0 20px rgba(0, 240, 160, 0.5);
        }
        
        .btn-secondary {
            background: #1f2d45;
            color: #00f0a0;
            border: 2px solid #00f0a0;
        }
        
        .btn-secondary:hover {
            background: #00f0a0;
            color: #000;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .section-title span {
            width: 4px;
            height: 1.5rem;
            background: #00f0a0;
            border-radius: 2px;
        }
        
        .description {
            color: #9ca3af;
            line-height: 1.8;
            padding: 1.5rem;
            background: #111827;
            border-radius: 8px;
            border-left: 4px solid #00f0a0;
        }
        
        .specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .spec-item {
            background: #111827;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #1f2d45;
        }
        
        .spec-label {
            color: #6b7fa3;
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
        }
        
        .spec-value {
            color: #e8ecf5;
            font-weight: 600;
        }
        
        footer {
            background: #111827;
            border-top: 1px solid #1f2d45;
            padding: 2rem;
            text-align: center;
            color: #6b7fa3;
            margin-top: 4rem;
        }
        
        @media (max-width: 768px) {
            .hero {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .hero-content h1 {
                font-size: 1.8rem;
            }
            
            .meta-info {
                grid-template-columns: 1fr;
            }
            
            .specs {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="/" class="logo">🎮 <?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?></a>
            <nav>
                <a href="/app/">Apps</a>
                <a href="/game/">Games</a>
                <a href="/admin/login.php">Admin</a>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <div class="breadcrumb">
            <a href="/">Home</a> / <a href="/game/">Games</a> / <span><?php echo Security::escape($game['title'] ?? ''); ?></span>
        </div>
        
        <div class="hero">
            <div class="hero-image">
                <?php if (!empty($game['image'])): ?>
                <img src="<?php echo Security::escape($game['image']); ?>" alt="<?php echo Security::escape($game['title'] ?? ''); ?>">
                <?php else: ?>
                🎯
                <?php endif; ?>
            </div>
            
            <div class="hero-content">
                <h1><?php echo Security::escape($game['title'] ?? ''); ?></h1>
                
                <div class="meta-info">
                    <div class="meta-item">
                        <div class="meta-label">Genre</div>
                        <div class="meta-value"><?php echo Security::escape($game['genre'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Downloads</div>
                        <div class="meta-value"><?php echo number_format($downloads); ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Version</div>
                        <div class="meta-value"><?php echo Security::escape($game['version'] ?? '1.0'); ?></div>
                    </div>
                </div>
                
                <div class="buttons">
                    <a href="?download=1" class="btn btn-primary">⬇️ Download Now</a>
                    <a href="<?php echo !empty($game['download_url']) ? Security::escape($game['download_url']) : '#'; ?>" target="_blank" class="btn btn-secondary">🔗 Visit Site</a>
                </div>
                
                <div style="color: #6b7fa3; font-size: 0.9rem;">
                    <p>Published: <?php echo date('M d, Y', strtotime($game['created_at'] ?? 'now')); ?></p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($game['description'])): ?>
        <div class="section">
            <h2 class="section-title"><span></span>About</h2>
            <div class="description">
                <?php echo nl2br(Security::escape($game['description'] ?? '')); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2 class="section-title"><span></span>Game Details</h2>
            <div class="specs">
                <div class="spec-item">
                    <div class="meta-label">Genre</div>
                    <div class="spec-value"><?php echo Security::escape($game['genre'] ?? 'N/A'); ?></div>
                </div>
                <div class="spec-item">
                    <div class="meta-label">Category</div>
                    <div class="spec-value"><?php echo Security::escape($game['category'] ?? 'N/A'); ?></div>
                </div>
                <div class="spec-item">
                    <div class="meta-label">Version</div>
                    <div class="spec-value"><?php echo Security::escape($game['version'] ?? '1.0'); ?></div>
                </div>
                <div class="spec-item">
                    <div class="meta-label">Downloads</div>
                    <div class="spec-value"><?php echo number_format($downloads); ?></div>
                </div>
                <div class="spec-item">
                    <div class="meta-label">Updated</div>
                    <div class="spec-value"><?php echo date('M d, Y', strtotime($game['updated_at'] ?? $game['created_at'] ?? 'now')); ?></div>
                </div>
                <div class="spec-item">
                    <div class="meta-label">Published</div>
                    <div class="spec-value"><?php echo date('M d, Y', strtotime($game['created_at'] ?? 'now')); ?></div>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <p><?php echo Security::escape($settings['footer_text'] ?? '© 2024 OMGPlugins. All rights reserved.'); ?></p>
    </footer>
</body>
</html>