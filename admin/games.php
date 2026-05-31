<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$auth->require();
global $json;

$games = $json->read('games');
$message = '';
$messageType = '';
$search = '';
$filterCategory = '';
$filterStatus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        if ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            if ($json->deleteItem('games', $id)) {
                $message = 'Game deleted successfully.';
                $messageType = 'success';
                $games = $json->read('games');
            } else {
                $message = 'Failed to delete game.';
                $messageType = 'error';
            }
        }
    }
}

if (isset($_GET['search'])) {
    $search = Security::sanitize($_GET['search']);
    $games = $json->search('games', 'title', $search);
}

if (isset($_GET['category'])) {
    $filterCategory = Security::sanitize($_GET['category']);
    $games = array_filter($games, function($g) use ($filterCategory) {
        return ($g['category'] ?? '') === $filterCategory;
    });
}

if (isset($_GET['status'])) {
    $filterStatus = Security::sanitize($_GET['status']);
    $games = array_filter($games, function($g) use ($filterStatus) {
        return ($g['status'] ?? '') === $filterStatus;
    });
}

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games Management - OMGPlugins CMS</title>
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
        
        .btn-primary {
            padding: 0.6rem 1.5rem;
            background: #00f0a0;
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
        
        .main {
            margin-left: 280px;
            margin-top: 64px;
            padding: 2rem;
            min-height: calc(100vh - 64px);
        }
        
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-input,
        .filter-select {
            padding: 0.6rem 1rem;
            border: 1px solid #1f2d45;
            border-radius: 10px;
            background: #111827;
            color: #e8ecf5;
            font-size: 0.9rem;
        }
        
        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #00f0a0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: rgba(0, 240, 160, 0.1);
            border: 1px solid rgba(0, 240, 160, 0.3);
            color: #00f0a0;
        }
        
        .alert-error {
            background: rgba(255, 85, 85, 0.1);
            border: 1px solid rgba(255, 85, 85, 0.3);
            color: #ff8585;
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
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-edit {
            padding: 0.4rem 0.8rem;
            background: rgba(0, 240, 160, 0.1);
            color: #00f0a0;
            border: 1px solid rgba(0, 240, 160, 0.3);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit:hover {
            background: #00f0a0;
            color: #000;
        }
        
        .btn-delete {
            padding: 0.4rem 0.8rem;
            background: rgba(255, 85, 85, 0.1);
            color: #ff8585;
            border: 1px solid rgba(255, 85, 85, 0.3);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-delete:hover {
            background: #ff8585;
            color: #fff;
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
                flex-direction: column;
                gap: 1rem;
            }
            
            .main {
                margin-left: 0;
                margin-top: 0;
                padding: 1rem;
            }
            
            .filters {
                flex-direction: column;
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
            <a href="/admin/games.php" class="active">
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
        <h1 class="header-title">Games Management</h1>
        <div class="header-actions">
            <a href="/admin/add-game.php" class="btn-primary">+ Add New Game</a>
        </div>
    </header>
    
    <main class="main">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php echo Security::escape($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="filters">
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                <input type="text" name="search" class="filter-input" placeholder="Search games..." value="<?php echo Security::escape($search); ?>" style="flex: 1; min-width: 200px;">
                <button type="submit" class="btn-primary">Search</button>
                <a href="/admin/games.php" class="btn-primary" style="background: #1f2d45; color: #00f0a0;">Clear</a>
            </form>
        </div>
        
        <?php if (count($games) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($games) as $game): ?>
                        <tr>
                            <td><strong><?php echo Security::escape($game['title']); ?></strong></td>
                            <td><?php echo Security::escape($game['category'] ?? 'N/A'); ?></td>
                            <td><?php echo Security::escape($game['version']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $game['status']; ?>">
                                    <?php echo ucfirst($game['status']); ?>
                                </span>
                            </td>
                            <td><?php echo Security::escape($game['published_at'] ?? 'Draft'); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="/admin/edit-game.php?id=<?php echo $game['id']; ?>" class="btn-edit">Edit</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this game?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $game['id']; ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
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
                <p>No games yet. <a href="/admin/add-game.php" style="color: #00f0a0; text-decoration: none; font-weight: 600;">Add your first game</a></p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>