<?php
/**
 * Download Tracking API
 * Returns download statistics and tracks downloads
 */

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/json.php';
require_once __DIR__ . '/includes/frontend.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? Security::sanitize($_GET['action']) : '';
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$itemType = isset($_GET['type']) ? Security::sanitize($_GET['type']) : 'app';

global $json, $frontend;

switch ($action) {
    case 'get':
        if (!$itemId) {
            echo json_encode(['error' => 'Missing item ID']);
            exit;
        }
        
        $downloads = $json->read('downloads');
        $count = 0;
        
        foreach ($downloads as $download) {
            if ($download['item_id'] == $itemId && $download['type'] === $itemType) {
                $count = $download['count'] ?? 0;
                break;
            }
        }
        
        echo json_encode([
            'id' => $itemId,
            'type' => $itemType,
            'count' => $count
        ]);
        break;
    
    case 'stats':
        $downloads = $json->read('downloads');
        $stats = [
            'total_downloads' => 0,
            'total_apps' => 0,
            'total_games' => 0,
            'top_apps' => [],
            'top_games' => []
        ];
        
        $apps = [];
        $games = [];
        
        foreach ($downloads as $download) {
            $stats['total_downloads'] += $download['count'] ?? 0;
            
            if ($download['type'] === 'app') {
                $stats['total_apps']++;
                $apps[] = $download;
            } elseif ($download['type'] === 'game') {
                $stats['total_games']++;
                $games[] = $download;
            }
        }
        
        // Sort by count descending
        usort($apps, function($a, $b) {
            return ($b['count'] ?? 0) - ($a['count'] ?? 0);
        });
        usort($games, function($a, $b) {
            return ($b['count'] ?? 0) - ($a['count'] ?? 0);
        });
        
        $stats['top_apps'] = array_slice($apps, 0, 5);
        $stats['top_games'] = array_slice($games, 0, 5);
        
        echo json_encode($stats);
        break;
    
    case 'timeline':
        // Get downloads over time (last 30 days)
        $downloads = $json->read('downloads');
        $timeline = [];
        
        for ($i = 0; $i < 30; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $timeline[$date] = 0;
        }
        
        // This is simplified - you'd need timestamp data in downloads
        echo json_encode(array_reverse($timeline));
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>