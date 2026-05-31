<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$auth->require();
global $json;

$apps = $json->read('apps');
$games = $json->read('games');
$message = '';
$messageType = '';
$searchTerm = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $searchTerm = Security::sanitize($_GET['q']);
    
    if (strlen($searchTerm) > 2) {
        $appResults = $json->search('apps', 'title', $searchTerm);
        $gameResults = $json->search('games', 'title', $searchTerm);
        
        foreach ($appResults as $app) {
            $app['type'] = 'app';
            $results[] = $app;
        }
        
        foreach ($gameResults as $game) {
            $game['type'] = 'game';
            $results[] = $game;
        }
    } else {
        $message = 'Search query must be at least 3 characters.';
        $messageType = 'error';
    }
}

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - OMGPlugins CMS</title>
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
        
        .main {
            margin-left: 280px;
            margin-top: 64px;
            padding: 2rem;
            min-height: calc(100vh - 64px);
        }
        
        .search-box {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
        }
        
        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #1f2d45;
            border-radius: 10px;
            background: #0a0d14;
            color: #e8ecf5;
            font-family: inherit;
            font-size: 0.95rem;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #00f0a0;
            box-shadow: 0 0 12px rgba(0, 240, 160, 0.15);
        }
        
        .btn-search {
            padding: 0.75rem 1.5rem;
            background: #00f0a0;
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-error {
            background: rgba(255, 85, 85, 0.1);
            border: 1px solid rgba(255, 85, 85, 0.3);
            color: #ff8585;
        }
        
        .results-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #1f2d45;
        }
        
        .results-count {
            color: #6b7fa3;
            font-size: 0.95rem;
        }
        
        .result-card {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .result-info {
            flex: 1;
        }
        
        .result-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .result-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.85rem;
            color: #6b7fa3;
        }
        
        .result-type {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(0, 240, 160, 0.1);
            color: #00f0a0;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .result-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-edit {
            padding: 0.5rem 1rem;
            background: rgba(0, 240, 160, 0.1);
            color: #00f0a0;
            border: 1px solid rgba(0, 240, 160, 0.3);
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-edit:hover {
            background: #00f0a0;
            color: #000;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7fa3;
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
                flex-direction: column;
                gap: 1rem;
            }
            
            .main {
                margin-left: 0;
                margin-top: 0;
                padding: 1rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .result-card {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .result-actions {
                width: 100%;
                margin-top: 1rem;
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
            <a href="/admin/dashboard.php">
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
        <h1 class="header-title">Search</h1>
    </header>
    
    <main class="main">
        <div class="search-box">
            <form method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Search apps and games..." value="<?php echo Security::escape($searchTerm); ?>" autofocus>
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php echo Security::escape($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($searchTerm) && count($results) > 0): ?>
            <div class="results-header">
                <h2>Search Results</h2>
                <p class="results-count">Found <?php echo count($results); ?> result<?php echo count($results) !== 1 ? 's' : ''; ?></p>
            </div>
            
            <?php foreach ($results as $result): ?>
                <div class="result-card">
                    <div class="result-info">
                        <div class="result-title"><?php echo Security::escape($result['title']); ?></div>
                        <div class="result-meta">
                            <span class="result-type"><?php echo ucfirst($result['type']); ?></span>
                            <span>Version: <?php echo Security::escape($result['version']); ?></span>
                            <span>Category: <?php echo Security::escape($result['category'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="result-actions">
                        <a href="/admin/edit-<?php echo $result['type']; ?>.php?id=<?php echo $result['id']; ?>" class="btn-edit">Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php elseif (!empty($searchTerm)): ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 1rem;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>No results found for "<?php echo Security::escape($searchTerm); ?>"</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 1rem;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>Enter a search query to find apps and games</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>