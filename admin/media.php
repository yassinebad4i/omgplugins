<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

$auth->require();
global $json;

$message = '';
$messageType = '';
$uploads = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $filename = Security::sanitize($_POST['filename'] ?? '');
            $type = Security::sanitize($_POST['type'] ?? 'image');
            if (Security::deleteUploadedFile($filename, $type)) {
                $message = 'File deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to delete file.';
                $messageType = 'error';
            }
        } elseif (isset($_FILES['file'])) {
            $type = Security::sanitize($_POST['type'] ?? 'image');
            $result = Security::saveUploadedFile($_FILES['file'], $type);
            
            if ($result['success']) {
                $message = 'File uploaded successfully!';
                $messageType = 'success';
            } else {
                $message = $result['error'] ?? 'Upload failed.';
                $messageType = 'error';
            }
        }
    }
}

$uploadsDir = __DIR__ . '/../uploads/images/';
if (is_dir($uploadsDir)) {
    $files = scandir($uploadsDir);
    foreach ($files as $file) {
        if (!in_array($file, ['.', '..'])) {
            $uploads[] = [
                'name' => $file,
                'path' => '/uploads/images/' . $file,
                'type' => 'image',
                'size' => filesize($uploadsDir . $file),
                'uploaded_at' => date('Y-m-d H:i:s', filemtime($uploadsDir . $file))
            ];
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
    <title>Media Manager - OMGPlugins CMS</title>
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
        
        .upload-section {
            background: #111827;
            border: 2px dashed #1f2d45;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .upload-input {
            display: none;
        }
        
        .upload-label {
            cursor: pointer;
            padding: 2rem;
            display: block;
        }
        
        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .upload-text {
            color: #6b7fa3;
            margin-bottom: 0.5rem;
        }
        
        .upload-hint {
            font-size: 0.85rem;
            color: #6b7fa3;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .media-card {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .media-preview {
            width: 100%;
            height: 150px;
            background: #0a0d14;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .media-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .media-info {
            padding: 1rem;
        }
        
        .media-name {
            font-size: 0.85rem;
            word-break: break-all;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .media-meta {
            font-size: 0.75rem;
            color: #6b7fa3;
            margin-bottom: 0.75rem;
        }
        
        .media-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-copy {
            flex: 1;
            padding: 0.4rem;
            background: rgba(0, 240, 160, 0.1);
            color: #00f0a0;
            border: 1px solid rgba(0, 240, 160, 0.3);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-copy:hover {
            background: #00f0a0;
            color: #000;
        }
        
        .btn-delete {
            flex: 1;
            padding: 0.4rem;
            background: rgba(255, 85, 85, 0.1);
            color: #ff8585;
            border: 1px solid rgba(255, 85, 85, 0.3);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
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
            
            .media-grid {
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
            <a href="/admin/categories.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                Categories
            </a>
            <a href="/admin/media.php" class="active">
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
        <h1 class="header-title">Media Manager</h1>
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
        
        <div class="upload-section">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                <input type="file" id="fileInput" name="file" class="upload-input" accept="image/*" required>
                <label for="fileInput" class="upload-label">
                    <div class="upload-icon">📤</div>
                    <div class="upload-text"><strong>Click to upload or drag and drop</strong></div>
                    <div class="upload-hint">PNG, JPG, WebP up to 5MB</div>
                </label>
            </form>
        </div>
        
        <?php if (count($uploads) > 0): ?>
            <h2 style="margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 700;">Uploaded Images (<?php echo count($uploads); ?>)</h2>
            <div class="media-grid">
                <?php foreach ($uploads as $upload): ?>
                    <div class="media-card">
                        <div class="media-preview">
                            <img src="<?php echo Security::escape($upload['path']); ?>" alt="<?php echo Security::escape($upload['name']); ?>">
                        </div>
                        <div class="media-info">
                            <div class="media-name" title="<?php echo Security::escape($upload['name']); ?>"><?php echo Security::escape(substr($upload['name'], 0, 20)) . (strlen($upload['name']) > 20 ? '...' : ''); ?></div>
                            <div class="media-meta"><?php echo number_format($upload['size'] / 1024, 2); ?> KB</div>
                            <div class="media-actions">
                                <button type="button" class="btn-copy" onclick="copyToClipboard('<?php echo Security::escape($upload['path']); ?>')">Copy URL</button>
                                <form method="POST" style="flex: 1;" onsubmit="return confirm('Delete this file?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrf_token); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="filename" value="<?php echo Security::escape($upload['name']); ?>">
                                    <input type="hidden" name="type" value="image">
                                    <button type="submit" class="btn-delete" style="width: 100%; margin: 0;">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 1rem;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <p>No images uploaded yet. Upload your first image above!</p>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');
        const uploadSection = document.querySelector('.upload-section');
        
        uploadSection.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadSection.style.borderColor = '#00f0a0';
            uploadSection.style.backgroundColor = 'rgba(0, 240, 160, 0.05)';
        });
        
        uploadSection.addEventListener('dragleave', () => {
            uploadSection.style.borderColor = '#1f2d45';
            uploadSection.style.backgroundColor = '#111827';
        });
        
        uploadSection.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadSection.style.borderColor = '#1f2d45';
            uploadSection.style.backgroundColor = '#111827';
            fileInput.files = e.dataTransfer.files;
            uploadForm.submit();
        });
        
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadForm.submit();
            }
        });
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('URL copied to clipboard!');
            });
        }
    </script>
</body>
</html>