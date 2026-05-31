<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$auth->require();
$user = $auth->getCurrentUser();

global $json;

$apps = $json->read('apps');
$games = $json->read('games');
$categories = $json->read('categories');

$totalApps = count($apps);
$totalGames = count($games);
$totalCategories = count($categories);

$latestApps = array_slice($apps, -5);
$latestGames = array_slice($games, -5);

array_walk($latestApps, function(&$app) {
    $app['title'] = Security::escape($app['title'] ?? '');
    $app['version'] = Security::escape($app['version'] ?? '');
    $app['status'] = Security::escape($app['status'] ?? 'draft');
});

array_walk($latestGames, function(&$game) {
    $game['title'] = Security::escape($game['title'] ?? '');
    $game['version'] = Security::escape($game['version'] ?? '');
    $game['status'] = Security::escape($game['status'] ?? 'draft');
});
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
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: #1a2235;
            border-radius: 10px;
            font-size: 0.9rem;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00f0a0, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
        }
        
        .logout-btn {
            padding: 0.5rem 1rem;
            background: #1f2d45;
            color: #e8ecf5;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #ff8585;
            color: #fff;
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
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            border-color: #00f0a0;
            box-shadow: 0 8px 24px rgba(0, 240, 160, 0.1);
        }
        
        .stat-label {
            color: #6b7fa3;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
        }
        
        .section-link {
            color: #00f0a0;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .section-link:hover {
            gap: 0.5rem;
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
            padding: 1rem;
            border-bottom: 1px solid #1f2d45;
        }
        
        .table tbody tr:hover {
            background: #1a2235;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
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
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7fa3;
        }
        
        .empty-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
            opacity: 0.5;
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
            }
            
            .header {
                left: 0;
                margin-top: auto;
                flex-direction: column;
                gap: 1rem;
            }
            
            .main {
                margin-left: 0;
                margin-top: 0;
                padding: 1rem;
            }
            
            .stats-grid {
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
        <h1 class="header-title">Dashboard</h1>
        <div class="header-actions">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                <span><?php echo Security::escape($user['username']); ?></span>
            </div>
            <a href="/admin/logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    
    <main class="main">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Apps</div>
                <div class="stat-value"><?php echo $totalApps; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Games</div>
                <div class="stat-value"><?php echo $totalGames; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Categories</div>
                <div class="stat-value"><?php echo $totalCategories; ?></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">Latest Apps</h2>
                <a href="/admin/apps.php" class="section-link">View All →</a>
            </div>
            
            <?php if (count($latestApps) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($latestApps) as $app): ?>
                            <tr>
                                <td><?php echo $app['title']; ?></td>
                                <td><?php echo $app['version']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $app['status']; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo Security::escape($app['published_at'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                        <polyline points="13 2 13 9 20 9"></polyline>
                    </svg>
                    <p>No apps yet. <a href="/admin/apps.php" class="section-link">Add your first app</a></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">Latest Games</h2>
            </div>
            
            <?php if (count($latestGames) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($latestGames) as $game): ?>
                            <tr>
                                <td><?php echo $game['title']; ?></td>
                                <td><?php echo $game['version']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $game['status']; ?>">
                                        <?php echo ucfirst($game['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo Security::escape($game['published_at'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                        <polyline points="13 2 13 9 20 9"></polyline>
                    </svg>
                    <p>No games yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>