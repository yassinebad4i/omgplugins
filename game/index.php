<?php
/**
 * Games Listing Page
 * Displays all published games with filters and pagination
 */

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/json.php';
require_once __DIR__ . '/../includes/frontend.php';

global $json, $frontend;

$settings = $frontend->getSettings();
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category = isset($_GET['category']) ? Security::sanitize($_GET['category']) : '';
$sort = isset($_GET['sort']) ? Security::sanitize($_GET['sort']) : 'newest';

// Get games
if (!empty($category)) {
    $games = $frontend->getGamesByCategory($category);
} else {
    $games = $frontend->getPublishedGames();
}

// Sort
if ($sort === 'oldest') {
    usort($games, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
} else { // newest
    usort($games, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

// Pagination
$perPage = 12;
$totalGames = count($games);
$totalPages = ceil($totalGames / $perPage);
$page = min($page, $totalPages);
$startIdx = ($page - 1) * $perPage;
$paginatedGames = array_slice($games, $startIdx, $perPage);

$categories = $frontend->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games - <?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?></title>
    <meta name="description" content="Browse all available games on <?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?>">
    <meta property="og:title" content="Games - <?php echo Security::escape($settings['site_name'] ?? 'OMGPlugins'); ?>">
    <meta property="og:description" content="Browse all available games">
    <meta name="twitter:card" content="summary">
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
        
        nav a:hover, nav a.active {
            color: #00f0a0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #00f0a0 0%, #00d4ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-header p {
            color: #6b7fa3;
            font-size: 1rem;
        }
        
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .filter-group label {
            color: #6b7fa3;
            font-weight: 500;
        }
        
        select {
            padding: 0.6rem 1rem;
            background: #1f2d45;
            border: 1px solid #374151;
            color: #e8ecf5;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        select:focus {
            outline: none;
            border-color: #00f0a0;
            box-shadow: 0 0 12px rgba(0, 240, 160, 0.2);
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
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
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 3rem 0;
        }
        
        .pagination a, .pagination span {
            padding: 0.6rem 1rem;
            border: 1px solid #374151;
            border-radius: 8px;
            text-decoration: none;
            color: #6b7fa3;
            transition: all 0.2s;
        }
        
        .pagination a:hover {
            border-color: #00f0a0;
            color: #00f0a0;
        }
        
        .pagination .active {
            background: #00f0a0;
            color: #000;
            border-color: #00f0a0;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6b7fa3;
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
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .filters {
                flex-direction: column;
                align-items: flex-start;
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
                <a href="/game/" class="active">Games</a>
                <a href="/admin/login.php">Admin</a>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <div class="page-header">
            <h1>Games</h1>
            <p>Browse <?php echo $totalGames; ?> available games</p>
        </div>
        
        <div class="filters">
            <div class="filter-group">
                <label>Category:</label>
                <select onchange="window.location = '/game/?category=' + this.value + '&sort=' + document.getElementById('sortSelect').value">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo Security::escape($cat['slug'] ?? ''); ?>" <?php echo $category === ($cat['slug'] ?? '') ? 'selected' : ''; ?>>
                        <?php echo Security::escape($cat['name'] ?? ''); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Sort:</label>
                <select id="sortSelect" onchange="window.location = '/game/?category=' + document.querySelector('select[onchange*=category]').value + '&sort=' + this.value">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                </select>
            </div>
        </div>
        
        <?php if (!empty($paginatedGames)): ?>
        <div class="grid">
            <?php foreach ($paginatedGames as $game): ?>
            <div class="card">
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
                        <span>v<?php echo Security::escape($game['version'] ?? '1.0'); ?></span>
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
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="/game/?page=1<?php echo !empty($category) ? '&category=' . Security::escape($category) : ''; ?>&sort=<?php echo Security::escape($sort); ?>">« First</a>
            <a href="/game/?page=<?php echo $page - 1; ?><?php echo !empty($category) ? '&category=' . Security::escape($category) : ''; ?>&sort=<?php echo Security::escape($sort); ?>">‹ Prev</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
            <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="/game/?page=<?php echo $i; ?><?php echo !empty($category) ? '&category=' . Security::escape($category) : ''; ?>&sort=<?php echo Security::escape($sort); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="/game/?page=<?php echo $page + 1; ?><?php echo !empty($category) ? '&category=' . Security::escape($category) : ''; ?>&sort=<?php echo Security::escape($sort); ?>">Next ›</a>
            <a href="/game/?page=<?php echo $totalPages; ?><?php echo !empty($category) ? '&category=' . Security::escape($category) : ''; ?>&sort=<?php echo Security::escape($sort); ?>">Last »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="no-results">
            <p>No games found</p>
        </div>
        <?php endif; ?>
    </main>
    
    <footer>
        <p><?php echo Security::escape($settings['footer_text'] ?? '© 2024 OMGPlugins. All rights reserved.'); ?></p>
    </footer>
</body>
</html>