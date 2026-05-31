<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$auth->require();
global $json;

$message = '';
$messageType = '';
$categories = $json->read('categories');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $name = Security::sanitize($_POST['name'] ?? '');
            $type = Security::sanitize($_POST['type'] ?? 'app');
            
            if (empty($name)) {
                $message = 'Category name is required.';
                $messageType = 'error';
            } else {
                $categoryExists = false;
                foreach ($categories as $cat) {
                    if (strtolower($cat['name']) === strtolower($name) && $cat['type'] === $type) {
                        $categoryExists = true;
                        break;
                    }
                }
                
                if ($categoryExists) {
                    $message = 'This category already exists for this type.';
                    $messageType = 'error';
                } else {
                    $categoryData = [
                        'name' => $name,
                        'type' => $type,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $catId = $json->addItem('categories', $categoryData);
                    if ($catId) {
                        $message = 'Category added successfully!';
                        $messageType = 'success';
                        $categories = $json->read('categories');
                    } else {
                        $message = 'Failed to add category.';
                        $messageType = 'error';
                    }
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            if ($json->deleteItem('categories', $id)) {
                $message = 'Category deleted successfully.';
                $messageType = 'success';
                $categories = $json->read('categories');
            } else {
                $message = 'Failed to delete category.';
                $messageType = 'error';
            }
        }
    }
}

$appCategories = array_filter($categories, function($cat) {
    return ($cat['type'] ?? 'app') === 'app';
});

$gameCategories = array_filter($categories, function($cat) {
    return ($cat['type'] ?? 'app') === 'game';
});

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - OMGPlugins CMS</title>
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
        
        .form-container {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        input[type="text"],
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #1f2d45;
            border-radius: 10px;
            background: #0a0d14;
            color: #e8ecf5;
            font-family: inherit;
            font-size: 0.95rem;
        }
        
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #00f0a0;
            box-shadow: 0 0 12px rgba(0, 240, 160, 0.15);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 150px;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .btn-add {
            padding: 0.75rem 1.5rem;
            background: #00f0a0;
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
        
        .category-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .category-card {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-info {
            flex: 1;
        }
        
        .category-name {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .category-type {
            font-size: 0.8rem;
            color: #6b7fa3;
            text-transform: uppercase;
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
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .category-list {
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
            <a href="/admin/categories.php" class="active">
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
        <h1 class="header-title">Categories Management</h1>
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
        
        <div class="form-container">
            <h3 style="margin-bottom: 1.5rem;">Add New Category</h3>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" id="name" name="name" placeholder="e.g., Streaming" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="app">App</option>
                            <option value="game">Game</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-add">Add Category</button>
                </div>
            </form>
        </div>
        
        <div class="section">
            <h2 class="section-title">App Categories</h2>
            <?php if (count($appCategories) > 0): ?>
                <div class="category-list">
                    <?php foreach ($appCategories as $cat): ?>
                        <div class="category-card">
                            <div class="category-info">
                                <div class="category-name"><?php echo Security::escape($cat['name']); ?></div>
                                <div class="category-type">App Category</div>
                            </div>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #6b7fa3; text-align: center; padding: 2rem;">No app categories yet.</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2 class="section-title">Game Categories</h2>
            <?php if (count($gameCategories) > 0): ?>
                <div class="category-list">
                    <?php foreach ($gameCategories as $cat): ?>
                        <div class="category-card">
                            <div class="category-info">
                                <div class="category-name"><?php echo Security::escape($cat['name']); ?></div>
                                <div class="category-type">Game Category</div>
                            </div>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #6b7fa3; text-align: center; padding: 2rem;">No game categories yet.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>