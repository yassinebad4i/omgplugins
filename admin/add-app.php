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
        $title = Security::sanitize($_POST['title'] ?? '');
        $slug = Security::generateSlug($title);
        $version = Security::sanitize($_POST['version'] ?? '');
        $category = Security::sanitize($_POST['category'] ?? '');
        $description = Security::sanitize($_POST['description'] ?? '');
        $download_url = Security::sanitize($_POST['download_url'] ?? '');
        $status = Security::sanitize($_POST['status'] ?? 'draft');
        
        if (empty($title) || empty($version) || empty($download_url)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        } else {
            $appData = [
                'title' => $title,
                'slug' => $slug,
                'version' => $version,
                'category' => $category,
                'description' => $description,
                'download_url' => $download_url,
                'status' => $status,
                'published_at' => ($status === 'published') ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s'),
                'thumbnail' => Security::sanitize($_POST['thumbnail'] ?? '')
            ];
            
            $appId = $json->addItem('apps', $appData);
            if ($appId) {
                $message = 'App added successfully! ID: ' . $appId;
                $messageType = 'success';
                // Reset form
                $_POST = [];
            } else {
                $message = 'Failed to add app. Please try again.';
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
    <title>Add App - OMGPlugins CMS</title>
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
        
        .form-container {
            max-width: 600px;
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 16px;
            padding: 2rem;
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
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: #e8ecf5;
        }
        
        .required {
            color: #ff8585;
        }
        
        input[type="text"],
        input[type="url"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #1f2d45;
            border-radius: 10px;
            background: #0a0d14;
            color: #e8ecf5;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        input[type="text"]:focus,
        input[type="url"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #00f0a0;
            box-shadow: 0 0 12px rgba(0, 240, 160, 0.15);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        input::placeholder {
            color: #6b7fa3;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            border-top: 1px solid #1f2d45;
            padding-top: 2rem;
        }
        
        .btn-submit {
            flex: 1;
            padding: 0.8rem;
            background: #00f0a0;
            color: #000;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 24px rgba(0, 240, 160, 0.3);
        }
        
        .btn-cancel {
            flex: 1;
            padding: 0.8rem;
            background: #1a2235;
            color: #e8ecf5;
            border: 1px solid #1f2d45;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
        }
        
        .btn-cancel:hover {
            border-color: #00f0a0;
            color: #00f0a0;
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
        <h1 class="header-title">Add New App</h1>
    </header>
    
    <main class="main">
        <div class="form-container">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php echo Security::escape($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="title">App Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" placeholder="e.g., Netflix Premium" value="<?php echo Security::escape($_POST['title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="version">Version <span class="required">*</span></label>
                        <input type="text" id="version" name="version" placeholder="e.g., v8.119.0" value="<?php echo Security::escape($_POST['version'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo Security::escape($cat['name']); ?>" <?php echo (($_POST['category'] ?? '') === $cat['name']) ? 'selected' : ''; ?>>
                                    <?php echo Security::escape($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="App description...">"><?php echo Security::escape($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="download_url">Download URL <span class="required">*</span></label>
                    <input type="url" id="download_url" name="download_url" placeholder="https://example.com/app.apk" value="<?php echo Security::escape($_POST['download_url'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="thumbnail">Thumbnail URL</label>
                    <input type="url" id="thumbnail" name="thumbnail" placeholder="https://example.com/thumb.jpg" value="<?php echo Security::escape($_POST['thumbnail'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo (($_POST['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo (($_POST['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add App</button>
                    <a href="/admin/apps.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>