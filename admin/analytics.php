<?php
/**
 * Analytics Dashboard
 * Displays comprehensive download statistics and trends
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/json.php';
require_once __DIR__ . '/includes/frontend.php';

global $auth, $json, $frontend;
$auth->require();

$downloads = $json->read('downloads');
$apps = $json->getPublished('apps');
$games = $json->getPublished('games');

// Calculate stats
$totalDownloads = 0;
$topApps = [];
$topGames = [];

foreach ($downloads as $download) {
    $totalDownloads += $download['count'] ?? 0;
    
    if ($download['type'] === 'app') {
        $topApps[] = $download;
    } else {
        $topGames[] = $download;
    }
}

// Sort
usort($topApps, function($a, $b) {
    return ($b['count'] ?? 0) - ($a['count'] ?? 0);
});
usort($topGames, function($a, $b) {
    return ($b['count'] ?? 0) - ($a['count'] ?? 0);
});

$topApps = array_slice($topApps, 0, 10);
$topGames = array_slice($topGames, 0, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - OMGPlugins Admin</title>
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
        }
        
        header {
            background: #111827;
            border-bottom: 1px solid #1f2d45;
            padding: 1.5rem 2rem;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #00f0a0;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7fa3;
            font-size: 0.9rem;
        }
        
        .section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .section-title span {
            width: 4px;
            height: 1.5rem;
            background: #00f0a0;
            border-radius: 2px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: #111827;
            border: 1px solid #1f2d45;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            background: #1f2d45;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #00f0a0;
            border-bottom: 1px solid #374151;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #1f2d45;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background: #1f2d45;
        }
        
        .rank {
            font-weight: 600;
            color: #00f0a0;
            min-width: 30px;
        }
        
        .empty {
            text-align: center;
            padding: 2rem;
            color: #6b7fa3;
        }
        
        a {
            color: #00f0a0;
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📊 Analytics</h1>
            <p style="color: #6b7fa3;">Download statistics and trends</p>
        </div>
    </header>
    
    <main class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($totalDownloads); ?></div>
                <div class="stat-label">Total Downloads</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($topApps); ?></div>
                <div class="stat-label">Apps with Downloads</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($topGames); ?></div>
                <div class="stat-label">Games with Downloads</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($apps); ?></div>
                <div class="stat-label">Total Published Apps</div>
            </div>
        </div>
        
        <?php if (!empty($topApps)): ?>
        <div class="section">
            <h2 class="section-title"><span></span>Top 10 Apps</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>App ID</th>
                        <th>Downloads</th>
                        <th>Last Downloaded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_values($topApps) as $index => $app): ?>
                    <tr>
                        <td><span class="rank">#<?php echo $index + 1; ?></span></td>
                        <td><?php echo $app['item_id']; ?></td>
                        <td><?php echo number_format($app['count'] ?? 0); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($app['last_downloaded'] ?? 'now')); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($topGames)): ?>
        <div class="section">
            <h2 class="section-title"><span></span>Top 10 Games</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Game ID</th>
                        <th>Downloads</th>
                        <th>Last Downloaded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_values($topGames) as $index => $game): ?>
                    <tr>
                        <td><span class="rank">#<?php echo $index + 1; ?></span></td>
                        <td><?php echo $game['item_id']; ?></td>
                        <td><?php echo number_format($game['count'] ?? 0); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($game['last_downloaded'] ?? 'now')); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="section">
            <h2 class="section-title"><span></span>Top Games</h2>
            <div class="empty">No game downloads tracked yet</div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>