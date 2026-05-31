<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$auth->require();
global $json;

$apps = $json->read('apps');
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        if ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            if ($json->deleteItem('apps', $id)) {
                $message = 'App deleted successfully.';
                $messageType = 'success';
                $apps = $json->read('apps');
            } else {
                $message = 'Failed to delete app.';
                $messageType = 'error';
            }
        }
    }
}

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apps Management - OMGPlugins CMS</title>
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
            <a href="/admin/apps.php" class="active">
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
        <h1 class="header-title">Apps Management</h1>
        <div class="header-actions">
            <a href="/admin/add-app.php" class="btn-primary">+ Add New App</a>
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
        
        <?php if (count($apps) > 0): ?>
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
                    <?php foreach (array_reverse($apps) as $app): ?>
                        <tr>
                            <td><strong><?php echo Security::escape($app['title']); ?></strong></td>
                            <td><?php echo Security::escape($app['category'] ?? 'N/A'); ?></td>
                            <td><?php echo Security::escape($app['version']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </td>
                            <td><?php echo Security::escape($app['published_at'] ?? 'Draft'); ?></td>
                            <td>
                                <div class="actions">
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this app?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
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
                <p>No apps yet. <a href="/admin/add-app.php" style="color: #00f0a0; text-decoration: none; font-weight: 600;">Add your first app</a></p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>