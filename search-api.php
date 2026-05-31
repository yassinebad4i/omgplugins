<?php
/**
 * Search API - Returns JSON results for frontend search
 */

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/json.php';
require_once __DIR__ . '/includes/frontend.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

global $frontend;

$results = $frontend->search($query, 'all');

// Format results
$formattedResults = [];
foreach ($results as $item) {
    $formattedResults[] = [
        'title' => $item['title'] ?? '',
        'slug' => $item['slug'] ?? '',
        'type' => $item['type'] ?? 'app'
    ];
}

echo json_encode(['results' => array_slice($formattedResults, 0, 10)]);
?>
