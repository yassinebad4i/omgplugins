<?php
/**
 * OMGPlugins Frontend - Homepage
 * Displays latest apps and games with dynamic content from JSON
 */

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/json.php';
require_once __DIR__ . '/includes/frontend.php';

global $json, $frontend;

$settings = $frontend->getSettings();
$latestApps = $frontend->getPublishedApps(6);
$latestGames = $frontend->getPublishedGames(6);
$categories = $frontend->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?> - <?php echo Security::escape($settings['site_description'] ?? 'Self-hosted CMS'); ?></title>
    <meta name="description" content="<?php echo Security::escape($settings['site_description'] ?? ''); ?>">
    <meta property="og:title" content="<?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?>">
    <meta property="og:description" content="<?php echo Security::escape($settings['site_description'] ?? ''); ?>">
    <meta property="og:image" content="<?php echo Security::escape($settings['logo'] ?? '/images/logo.png'); ?>">
    <meta name="twitter:card" content="summary_large_image">
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .search-box {
            flex: 1;
            max-width: 400px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.7rem 1rem;
            background: #1f2d45;
            border: 1px solid #374151;
            border-radius: 8px;
            color: #e8ecf5;
            font-size: 0.95rem;
        }
        
        .search-box input::placeholder {
            color: #6b7fa3;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #00f0a0;
            box-shadow: 0 0 12px rgba(0, 240, 160, 0.2);
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #1f2d45;
            border: 1px solid #374151;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            z-index: 10;
        }
        
        .search-results.active {
            display: block;
        }
        
        .search-result-item {
            padding: 1rem;
            border-bottom: 1px solid #374151;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .search-result-item:hover {
            background: #111827;
        }
        
        .search-result-item a {
            color: #e8ecf5;
            text-decoration: none;
            display: block;
        }
        
        .search-result-type {
            font-size: 0.8rem;
            color: #00f0a0;
            font-weight: 600;
        }
        
        .search-result-title {
            font-weight: 600;
            margin-top: 0.3rem;
        }
        
        nav {
            display: flex;
            gap: 1.5rem;
        }
        
        nav a {
            color: #6b7fa3;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        nav a:hover {
            color: #00f0a0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero {
            text-align: center;
            padding: 3rem 0;
            border-bottom: 1px solid #1f2d45;
            margin-bottom: 3rem;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #00f0a0 0%, #00d4ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero p {
            color: #6b7fa3;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .section {
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .section-title span {
            width: 4px;
            height: 2rem;
            background: #00f0a0;
            border-radius: 2px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .card {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .card:hover {
            border-color: #00f0a0;
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 240, 160, 0.15);
        }
        
        .card-image {
            width: 100%;
            height: 180px;
            background: #1f2d45;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7fa3;
            font-size: 3rem;
        }
        
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #e8ecf5;
        }
        
        .card-meta {
            font-size: 0.85rem;
            color: #6b7fa3;
            margin-bottom: 0.8rem;
            display: flex;
            justify-content: space-between;
        }
        
        .card-description {
            font-size: 0.95rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-footer {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            flex: 1;
            padding: 0.7rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #00f0a0;
            color: #000;
        }
        
        .btn-primary:hover {
            box-shadow: 0 0 16px rgba(0, 240, 160, 0.4);
        }
        
        .btn-secondary {
            background: #1f2d45;
            color: #00f0a0;
            border: 1px solid #00f0a0;
        }
        
        .btn-secondary:hover {
            background: #00f0a0;
            color: #000;
        }
        
        .categories {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        
        .category-btn {
            padding: 0.6rem 1.2rem;
            background: #1f2d45;
            border: 1px solid #374151;
            color: #e8ecf5;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .category-btn:hover {
            border-color: #00f0a0;
            color: #00f0a0;
        }
        
        .category-btn.active {
            background: #00f0a0;
            color: #000;
            border-color: #00f0a0;
        }
        
        footer {
            background: #111827;
            border-top: 1px solid #1f2d45;
            padding: 3rem 2rem;
            margin-top: 4rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            margin-bottom: 1rem;
            color: #00f0a0;
        }
        
        .footer-section a {
            display: block;
            color: #6b7fa3;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: color 0.2s;
        }
        
        .footer-section a:hover {
            color: #00f0a0;
        }
        
        .footer-bottom {
            border-top: 1px solid #1f2d45;
            padding-top: 2rem;
            text-align: center;
            color: #6b7fa3;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-links a {
            display: inline-flex;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            background: #1f2d45;
            border-radius: 50%;
            text-decoration: none;
            color: #00f0a0;
            transition: all 0.2s;
        }
        
        .social-links a:hover {
            background: #00f0a0;
            color: #000;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            nav {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .categories {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="/" class="logo">
                🎮 <?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?>
            </a>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search apps and games...">
                <div class="search-results" id="searchResults"></div>
            </div>
            
            <nav>
                <a href="/app/">Apps</a>
                <a href="/game/">Games</a>
                <a href="/admin/login.php">Admin</a>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <div class="hero">
            <h1><?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?></h1>
            <p><?php echo Security::escape($settings['site_description'] ?? 'A complete self-hosted CMS for managing modded apps and games'); ?></p>
        </div>
        
        <?php if (!empty($categories)): ?>
        <div class="section">
            <h3 class="section-title"><span></span>Categories</h3>
            <div class="categories">
                <button class="category-btn active" onclick="filterByCategory('all')">All</button>
                <?php foreach ($categories as $category): ?>
                <button class="category-btn" onclick="filterByCategory('<?php echo Security::escape($category['slug'] ?? ''); ?>')">
                    <?php echo Security::escape($category['name'] ?? ''); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($latestApps)): ?>
        <div class="section">
            <h3 class="section-title"><span></span>Latest Apps</h3>
            <div class="grid" id="appsGrid">
                <?php foreach ($latestApps as $app): ?>
                <div class="card" data-category="<?php echo Security::escape($app['category'] ?? ''); ?>">
                    <div class="card-image">
                        <?php if (!empty($app['image'])): ?>
                        <img src="<?php echo Security::escape($app['image']); ?>" alt="<?php echo Security::escape($app['title'] ?? ''); ?>">
                        <?php else: ?>
                        📱
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <div class="card-title"><?php echo Security::escape($app['title'] ?? ''); ?></div>
                        <div class="card-meta">
                            <span><?php echo Security::escape($app['version'] ?? '1.0'); ?></span>
                            <span><?php echo count(json_decode($app['features'] ?? '[]', true)); ?> features</span>
                        </div>
                        <div class="card-description"><?php echo Security::escape(substr($app['description'] ?? '', 0, 120)); ?></div>
                        <div class="card-footer">
                            <a href="/app/<?php echo Security::escape($app['slug'] ?? ''); ?>.php" class="btn btn-primary">View</a>
                            <a href="/app/<?php echo Security::escape($app['slug'] ?? ''); ?>.php?download=1" class="btn btn-secondary">Download</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="/app/" class="btn btn-primary" style="display: inline-block; padding: 0.9rem 2rem;">View All Apps</a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($latestGames)): ?>
        <div class="section">
            <h3 class="section-title"><span></span>Latest Games</h3>
            <div class="grid" id="gamesGrid">
                <?php foreach ($latestGames as $game): ?>
                <div class="card" data-category="<?php echo Security::escape($game['category'] ?? ''); ?>">
                    <div class="card-image">
                        <?php if (!empty($game['image'])): ?>
                        <img src="<?php echo Security::escape($game['image']); ?>" alt="<?php echo Security::escape($game['title'] ?? ''); ?>">
                        <?php else: ?>
                        🎯
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <div class="card-title"><?php echo Security::escape($game['title'] ?? ''); ?></div>
                        <div class="card-meta">
                            <span><?php echo Security::escape($game['genre'] ?? 'N/A'); ?></span>
                            <span><?php echo Security::escape($game['version'] ?? '1.0'); ?></span>
                        </div>
                        <div class="card-description"><?php echo Security::escape(substr($game['description'] ?? '', 0, 120)); ?></div>
                        <div class="card-footer">
                            <a href="/game/<?php echo Security::escape($game['slug'] ?? ''); ?>.php" class="btn btn-primary">View</a>
                            <a href="/game/<?php echo Security::escape($game['slug'] ?? ''); ?>.php?download=1" class="btn btn-secondary">Download</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="/game/" class="btn btn-primary" style="display: inline-block; padding: 0.9rem 2rem;">View All Games</a>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <footer>
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>About</h3>
                    <a href="/">Home</a>
                    <a href="/app/">Apps</a>
                    <a href="/game/">Games</a>
                </div>
                <div class="footer-section">
                    <h3>Links</h3>
                    <a href="/admin/login.php">Admin Panel</a>
                    <a href="/">Contact</a>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?php echo Security::escape($settings['social_facebook']); ?>" target="_blank" title="Facebook">f</a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?php echo Security::escape($settings['social_twitter']); ?>" target="_blank" title="Twitter">𝕏</a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?php echo Security::escape($settings['social_instagram']); ?>" target="_blank" title="Instagram">📷</a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_github'])): ?>
                        <a href="<?php echo Security::escape($settings['social_github']); ?>" target="_blank" title="GitHub">⭐</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?php echo Security::escape($settings['footer_text'] ?? '© 2024 OMGPlugins. All rights reserved.'); ?></p>
            </div>
        </div>
    </footer>
    
    <script>
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('active');
                return;
            }
            
            fetch('/search-api.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        searchResults.innerHTML = data.results.map(item => `
                            <div class="search-result-item">
                                <a href="/${item.type}/${item.slug}.php">
                                    <div class="search-result-type">${item.type.toUpperCase()}</div>
                                    <div class="search-result-title">${item.title}</div>
                                </a>
                            </div>
                        `).join('');
                        searchResults.classList.add('active');
                    } else {
                        searchResults.innerHTML = '<div class="search-result-item"><div class="search-result-title">No results found</div></div>';
                        searchResults.classList.add('active');
                    }
                })
                .catch(err => console.error(err));
        });
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-box')) {
                searchResults.classList.remove('active');
            }
        });
        
        function filterByCategory(category) {
            const cards = document.querySelectorAll('.card');
            const buttons = document.querySelectorAll('.category-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
