<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$auth->require();
global $json;

$user = $auth->getCurrentUser();
$apps = $json->read('apps');
$games = $json->read('games');
$categories = $json->read('categories');

$appCount = count($apps);
$gameCount = count($games);
$publishedApps = count($json->getPublished('apps'));
$publishedGames = count($json->getPublished('games'));

$recentApps = array_slice(array_reverse($apps), 0, 5);
$recentGames = array_slice(array_reverse($games), 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OMGPlugins CMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: #0a0d14;
            color: #e8ecf5;
            min-height: 100vh;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: #111827;
            border-right: 1px solid #1f2d45;
            padding: 2rem 0;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.35rem;
            font-weight: 700;
            text-decoration: none;
            color: #e8ecf5;
        }
        
        .logo-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #00f0a0;
            box-shadow: 0 0 12px #00f0a0;
        }
        
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .sidebar-nav a {
            padding: 0.75rem 1.5rem;
            color: #6b7fa3;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: #1a2235;
            color: #00f0a0;
        }
        
        .sidebar-nav svg {
            width: 18px;
            height: 18px;
        }
        
        .header {
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            height: 64px;
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid #1f2d45;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 10;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            min-width: 250px;
        }
        
        .search-box input {
            background: none;
            border: none;
            color: #e8ecf5;
            font-size: 0.9rem;
            flex: 1;
        }
        
        .search-box input:focus {
            outline: none;
        }
        
        .search-box input::placeholder {
            color: #6b7fa3;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            text-align: right;
            font-size: 0.85rem;
        }
        
        .user-name {
            font-weight: 600;
            color: #e8ecf5;
        }
        
        .user-role {
            color: #6b7fa3;
            font-size: 0.75rem;
        }
        
        .main {
            margin-left: 280px;
            margin-top: 64px;
            padding: 2rem;
            min-height: calc(100vh - 64px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(0, 240, 160, 0.1) 0%, rgba(17, 24, 39, 0.5) 100%);
            border: 1px solid #1f2d45;
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .stat-label {
            color: #6b7fa3;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #00f0a0;
            margin-bottom: 0.5rem;
        }
        
        .stat-meta {
            font-size: 0.8rem;
            color: #6b7fa3;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #1f2d45;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead {
            background: #1a2235;
        }
        
        .table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            color: #6b7fa3;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #1f2d45;
        }
        
        .table td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #1f2d45;
            font-size: 0.9rem;
        }
        
        .table tbody tr:hover {
            background: #1a2235;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-published {
            background: rgba(0, 240, 160, 0.1);
            color: #00f0a0;
        }
        
        .status-draft {
            background: rgba(107, 127, 163, 0.1);
            color: #6b7fa3;
        }
        
        .quick-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        
        .btn-action {
            padding: 0.7rem 1.5rem;
            background: #00f0a0;
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-block;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
        
        .btn-action-secondary {
            background: #1a2235;
            color: #00f0a0;
            border: 1px solid #1f2d45;
        }
        
        .btn-action-secondary:hover {
            border-color: #00f0a0;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid #1f2d45;
                padding: 1rem;
            }
            
            .sidebar-nav {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .sidebar-nav a {
                flex: 1 1 auto;
                min-width: 150px;
                font-size: 0.8rem;
            }
            
            .header {
                left: 0;
                flex-direction: column;
                gap: 1rem;
                height: auto;
                padding: 1rem;
            }
            
            .search-box {
                min-width: auto;
                width: 100%;
            }
            
            .main {
                margin-left: 0;
                margin-top: 0;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="/admin/dashboard.php" class="sidebar-brand">
            <span class="logo-dot"></span>OMGPlugins
        </a>
        <nav class="sidebar-nav">
            <a href="/admin/dashboard.php" class="active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline>
                    <polyline points="12 12 20 7.5"></polyline>
                    <polyline points="12 12 12 21"></polyline>
                    <polyline points="12 12 4 7.5"></polyline>
                </svg>
                Dashboard
            </a>
            <a href="/admin/apps.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                Apps Management
            </a>
            <a href="/admin/games.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2" ry="2"></rect>
                    <path d="M6 12h12M6 16h12M6 8h12"></path>
                </svg>
                Games Management
            </a>
            <a href="/admin/categories.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                Categories
            </a>
            <a href="/admin/media.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                Media
            </a>
            <a href="/admin/logout.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </a>
        </nav>
    </aside>
    
    <header class="header">
        <div class="header-left">
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <form action="/admin/search.php" method="GET" style="width: 100%;">
                    <input type="text" name="q" placeholder="Search apps and games..." autocomplete="off">
                </form>
            </div>
        </div>
        <div class="header-right">
            <div class="user-info">
                <div class="user-name"><?php echo Security::escape($user['username']); ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
    </header>
    
    <main class="main">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Apps</div>
                <div class="stat-number"><?php echo $appCount; ?></div>
                <div class="stat-meta"><?php echo $publishedApps; ?> published</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Games</div>
                <div class="stat-number"><?php echo $gameCount; ?></div>
                <div class="stat-meta"><?php echo $publishedGames; ?> published</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Categories</div>
                <div class="stat-number"><?php echo count($categories); ?></div>
                <div class="stat-meta">Organized content</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Status</div>
                <div class="stat-number" style="color: #00ff00;">✓</div>
                <div class="stat-meta">System operational</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="/admin/add-app.php" class="btn-action">+ Add App</a>
            <a href="/admin/add-game.php" class="btn-action">+ Add Game</a>
            <a href="/admin/categories.php" class="btn-action btn-action-secondary">Manage Categories</a>
            <a href="/admin/media.php" class="btn-action btn-action-secondary">Upload Media</a>
        </div>
        
        <div class="content-grid">
            <div class="section">
                <h2 class="section-title">Recent Apps</h2>
                <?php if (count($recentApps) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Version</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApps as $app): ?>
                                <tr>
                                    <td><strong><?php echo Security::escape($app['title']); ?></strong></td>
                                    <td><?php echo Security::escape($app['version']); ?></td>
                                    <td><span class="status-badge status-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #6b7fa3; text-align: center; padding: 2rem;">No apps yet. <a href="/admin/add-app.php" style="color: #00f0a0; text-decoration: none;">Create one</a></p>
                <?php endif; ?>
            </div>
            
            <div class="section">
                <h2 class="section-title">Recent Games</h2>
                <?php if (count($recentGames) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Version</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentGames as $game): ?>
                                <tr>
                                    <td><strong><?php echo Security::escape($game['title']); ?></strong></td>
                                    <td><?php echo Security::escape($game['version']); ?></td>
                                    <td><span class="status-badge status-<?php echo $game['status']; ?>"><?php echo ucfirst($game['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #6b7fa3; text-align: center; padding: 2rem;">No games yet. <a href="/admin/add-game.php" style="color: #00f0a0; text-decoration: none;">Create one</a></p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>